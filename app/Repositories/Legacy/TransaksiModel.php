<?php
namespace App\Repositories\Legacy;
use PDO;
use Exception;
use Throwable;
use DateTime;
use Illuminate\Support\Facades\DB;


class TransaksiModel
{
    private PDO $db;
    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT t.*, p.nama_pelanggan, k.nama_karyawan FROM transaksi t 
        JOIN pelanggan p ON t.id_pelanggan=p.id_pelanggan JOIN karyawan k ON t.id_karyawan=k.id_karyawan ORDER BY t.id_transaksi DESC");
        return $stmt->fetchAll();
    }
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT t.*, p.nama_pelanggan, p.no_hp, p.alamat, k.nama_karyawan FROM transaksi t 
        JOIN pelanggan p ON t.id_pelanggan=p.id_pelanggan JOIN karyawan k ON t.id_karyawan=k.id_karyawan WHERE t.id_transaksi=?");
        $stmt->execute([$id]);
        $d = $stmt->fetch();
        return $d ?: null;
    }
    public function getDetail(int $idTransaksi): array
    {
        $stmt = $this->db->prepare("SELECT d.*, COALESCE(b.nama_barang,d.nama_item_external) AS nama_barang, COALESCE(b.kategori,'External') 
        AS kategori FROM detail_transaksi d LEFT JOIN barang b ON d.id_barang=b.id_barang WHERE d.id_transaksi=? ORDER BY d.id_detail ASC");
        $stmt->execute([$idTransaksi]);
        return $stmt->fetchAll();
    }
    public function getDenda(int $idTransaksi): int
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(denda+denda_manual),0) AS total_denda FROM pengembalian WHERE id_transaksi=?");
        $stmt->execute([$idTransaksi]);
        return (int) ($stmt->fetch()['total_denda'] ?? 0);
    }
    private function validasiTanggal(string $tanggalSewa, string $tanggalKembali): void
    {
        $today = new DateTime(date('Y-m-d'));
        $sewa = new DateTime($tanggalSewa);
        $kembali = new DateTime($tanggalKembali);
        if ($sewa < $today)
            throw new Exception('Tanggal sewa tidak boleh sebelum hari ini.');
        if ($kembali < $sewa)
            throw new Exception('Tanggal kembali tidak boleh sebelum tanggal sewa.');
        $maks = (clone $sewa)->modify('+7 days');
        if ($kembali > $maks)
            throw new Exception('Maksimal tanggal pengembalian adalah H+7 dari tanggal sewa.');
    }
    public function create(array $data, array $items): int
    {
        $this->validasiTanggal($data['tanggal_sewa'], $data['tanggal_kembali']);
        $jenis = $data['jenis_transaksi'] ?? 'Satuan';
        if (!in_array($jenis, ['Satuan', 'Paketan'], true))
            $jenis = 'Satuan';
        $this->db->beginTransaction();
        try {
            $totalBarang = 0;
            $totalExternal = 0;
            $detailFinal = [];
            foreach ($items as $item) {
                $tipe = $item['tipe_item'] ?? 'Barang';
                $qty = (int) ($item['qty'] ?? 0);
                if ($qty <= 0)
                    continue;
                if ($tipe === 'External') {
                    $nama = trim($item['nama_item_external'] ?? '');
                    $sumber = trim($item['sumber_external'] ?? '');
                    $harga = max(0, (int) ($item['harga_satuan'] ?? 0));
                    if ($nama === '')
                        throw new Exception('Nama item tambahan wajib diisi.');
                    $sub = $harga * $qty;
                    $totalExternal += $sub;
                    $detailFinal[] = ['tipe_item' => 'External', 'id_barang' => null, 'nama_item_external' => 
                    $nama, 'sumber_external' => $sumber, 'qty' => $qty, 'harga_satuan' => $harga, 'subtotal' => $sub];
                    continue;
                }
                $idBarang = (int) ($item['id_barang'] ?? 0);
                if ($idBarang <= 0)
                    continue;
                $stmt = $this->db->prepare('SELECT * FROM barang WHERE id_barang=? FOR UPDATE');
                $stmt->execute([$idBarang]);
                $barang = $stmt->fetch();
                if (!$barang)
                    throw new Exception('Barang tidak ditemukan.');
                if ((int) $barang['stok'] < $qty)
                    throw new Exception('Stok tersedia untuk ' . $barang['nama_barang'] . ' tidak cukup. Sisa tersedia: ' . (int) $barang['stok']);
                $isGenset = (stripos($barang['nama_barang'], 'Generator set') !== false || stripos($barang['kategori'], 'Genset') !== false);
                $gensetInput = (int) ($data['biaya_genset'] ?? 0);
                if ($jenis === 'Satuan') {
                    if ($isGenset && $gensetInput > 0) {
                        $harga = 0;
                        $sub = 0;
                    } else {
                        $harga = (int) $barang['harga_sewa'];
                        $sub = $harga * $qty;
                        $totalBarang += $sub;
                    }
                } else {
                    $harga = 0;
                    $sub = 0;
                }
                $detailFinal[] = ['tipe_item' => 'Barang', 'id_barang' => $idBarang, 'nama_item_external' => null,
                 'sumber_external' => null, 'qty' => $qty, 'harga_satuan' => $harga, 'subtotal' => $sub];
            }
            if (empty($detailFinal))
                throw new Exception('Minimal pilih 1 barang atau item tambahan untuk transaksi.');
            $hargaPaket = (int) ($data['harga_paket_manual'] ?? 0);
            $genset = (int) ($data['biaya_genset'] ?? 0);
            if ($jenis === 'Paketan') {
                if ($hargaPaket <= 0)
                    throw new Exception('Harga paket manual wajib diisi untuk transaksi paketan.');
                $total = $hargaPaket + $genset + $totalExternal;
            } else {
                $total = $totalBarang + $genset + $totalExternal;
                $hargaPaket = 0;
                $data['id_paket'] = null;
                $data['nama_paket'] = null;
            }
            $stmt = $this->db->prepare("INSERT INTO transaksi (id_pelanggan,id_karyawan,jenis_transaksi,id_paket,
            nama_paket,harga_paket_manual,biaya_genset,tanggal_transaksi,tanggal_sewa,tanggal_kembali,status_transaksi,total_harga,alamat_lokasi_acara,link_google_maps)
             VALUES (:id_pelanggan,:id_karyawan,:jenis_transaksi,:id_paket,:nama_paket,:harga_paket_manual,:biaya_genset,:tanggal_transaksi,:tanggal_sewa,:tanggal_kembali,:status_transaksi,:total_harga,:alamat_lokasi_acara,:link_google_maps)");
            $stmt->execute([':id_pelanggan' => $data['id_pelanggan'], ':id_karyawan' => $data['id_karyawan'], ':jenis_transaksi' 
            => $jenis, ':id_paket' => $data['id_paket'] ?: null, ':nama_paket' => $data['nama_paket'] ?: null, ':harga_paket_manual' => $hargaPaket,
             ':biaya_genset' => $genset, ':tanggal_transaksi' => $data['tanggal_transaksi'], ':tanggal_sewa' => $data['tanggal_sewa'],
              ':tanggal_kembali' => $data['tanggal_kembali'], ':status_transaksi' => $data['status_transaksi'], ':total_harga' => $total,
                ':alamat_lokasi_acara' => $data['alamat_lokasi_acara'] ?? null,
                ':link_google_maps' => $data['link_google_maps'] ?? null]);
            $id = (int) $this->db->lastInsertId();
            $ins = $this->db->prepare("INSERT INTO detail_transaksi (id_transaksi,id_barang,nama_item_external,sumber_external,qty,harga_satuan,subtotal,tipe_item)
             VALUES (:id_transaksi,:id_barang,:nama_item_external,:sumber_external,:qty,:harga_satuan,:subtotal,:tipe_item)");
            $stok = $this->db->prepare("UPDATE barang SET stok=stok-:q1, stok_disewa=stok_disewa+:q2, status=CASE WHEN (stok-:q3)>0 
            THEN 'Tersedia' ELSE 'Disewa' END WHERE id_barang=:id_barang AND stok>=:q4");
            foreach ($detailFinal as $d) {
                $ins->execute([':id_transaksi' => $id, ':id_barang' => $d['id_barang'], ':nama_item_external' => 
                $d['nama_item_external'], ':sumber_external' => $d['sumber_external'], ':qty' => $d['qty'], ':harga_satuan' => 
                $d['harga_satuan'], ':subtotal' => $d['subtotal'], ':tipe_item' => $d['tipe_item']]);
                if ($d['tipe_item'] === 'Barang') {
                    $stok->execute([':q1' => $d['qty'], ':q2' => $d['qty'], ':q3' => $d['qty'], ':q4' => $d['qty'], ':id_barang' => $d['id_barang']]);
                    if ($stok->rowCount() !== 1) {
                        throw new Exception('Stok salah satu barang tidak cukup saat transaksi disimpan. Seluruh perubahan dibatalkan.');
                    }
                }
            }
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    public function updateStatus(int $id, string $status): bool
    {
        $trx = $this->getById($id);
        if ($trx && $trx['status_transaksi'] === 'Selesai')
            throw new Exception('Transaksi sudah selesai dan tidak bisa diubah lagi.');
        $stmt = $this->db->prepare('UPDATE transaksi SET status_transaksi=? WHERE id_transaksi=?');
        return $stmt->execute([$status, $id]);
    }
}
