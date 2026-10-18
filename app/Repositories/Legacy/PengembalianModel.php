<?php
namespace App\Repositories\Legacy;
use PDO;
use Exception;
use Throwable;
use DateTime;
use Illuminate\Support\Facades\DB;


class PengembalianModel
{
    private PDO $db;
    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    public function getTransaksiBerjalan(): array
    {
        $stmt = $this->db->query("SELECT t.*, p.nama_pelanggan, k.nama_karyawan
            FROM transaksi t
            JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
            JOIN karyawan k ON t.id_karyawan = k.id_karyawan
            LEFT JOIN pengembalian pg ON t.id_transaksi = pg.id_transaksi
            WHERE t.status_transaksi = 'Berjalan'
              AND pg.id_pengembalian IS NULL
            ORDER BY t.id_transaksi DESC");
        return $stmt->fetchAll();
    }
    public function getDetailTransaksi(int $idTransaksi): array
    {
        $stmt = $this->db->prepare("SELECT dt.*, COALESCE(b.nama_barang, dt.nama_item_external) 
        AS nama_barang, COALESCE(b.kategori, 'External') AS kategori
            FROM detail_transaksi dt
            LEFT JOIN barang b ON dt.id_barang = b.id_barang
            WHERE dt.id_transaksi = ?
            ORDER BY dt.id_detail ASC");
        $stmt->execute([$idTransaksi]);
        return $stmt->fetchAll();
    }
    public function getRiwayat(): array
    {
        $stmt = $this->db->query("SELECT pg.*, t.tanggal_sewa, t.tanggal_kembali, t.total_harga, t.status_transaksi,
                   p.nama_pelanggan, k.nama_karyawan
            FROM pengembalian pg
            JOIN transaksi t ON pg.id_transaksi = t.id_transaksi
            JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
            JOIN karyawan k ON t.id_karyawan = k.id_karyawan
            ORDER BY pg.id_pengembalian DESC");
        return $stmt->fetchAll();
    }
    public function getDetailPengembalian(int $idPengembalian): array
    {
        $stmt = $this->db->prepare("SELECT pd.*, b.nama_barang
            FROM pengembalian_detail pd
            JOIN barang b ON pd.id_barang = b.id_barang
            WHERE pd.id_pengembalian = ?");
        $stmt->execute([$idPengembalian]);
        return $stmt->fetchAll();
    }
    private function hitungDenda(string $tanggalKembali, string $tanggalPengembalian): int
    {
        $kembali = new DateTime($tanggalKembali);
        $pengembalian = new DateTime($tanggalPengembalian);

        if ($pengembalian <= $kembali) {
            return 0;
        }

        $selisihHari = (int) $kembali->diff($pengembalian)->days;
        return $selisihHari * 50000;
    }
    public function proses(array $data): bool
    {
        $this->db->beginTransaction();

        try {
            $idTransaksi = (int) $data['id_transaksi'];
            $tanggalPengembalian = $data['tanggal_pengembalian'];
            $rusakList = $data['rusak_qty'] ?? [];
            $dendaManual = max(0, (int) ($data['denda_manual'] ?? 0));

            $cek = $this->db->prepare("SELECT COUNT(*) AS total FROM pengembalian WHERE id_transaksi = ?");
            $cek->execute([$idTransaksi]);
            $row = $cek->fetch();
            if ((int) $row['total'] > 0) {
                throw new Exception('Transaksi ini sudah pernah diproses pengembaliannya.');
            }

            $stmtTrx = $this->db->prepare("SELECT * FROM transaksi WHERE id_transaksi = ? FOR UPDATE");
            $stmtTrx->execute([$idTransaksi]);
            $trx = $stmtTrx->fetch();

            if (!$trx) {
                throw new Exception('Transaksi tidak ditemukan.');
            }

            if ($trx['status_transaksi'] === 'Selesai') {
                throw new Exception('Transaksi sudah selesai dan tidak bisa diproses ulang.');
            }

            $dendaTelat = $this->hitungDenda($trx['tanggal_kembali'], $tanggalPengembalian);

            $stmt = $this->db->prepare("INSERT INTO pengembalian
                (id_transaksi, tanggal_pengembalian, kondisi_barang, denda, denda_manual, catatan)
                VALUES (:id_transaksi, :tanggal_pengembalian, :kondisi_barang, :denda, :denda_manual, :catatan)");

            $stmt->execute([
                ':id_transaksi' => $idTransaksi,
                ':tanggal_pengembalian' => $tanggalPengembalian,
                ':kondisi_barang' => $data['kondisi_barang'],
                ':denda' => $dendaTelat,
                ':denda_manual' => $dendaManual,
                ':catatan' => $data['catatan'],
            ]);

            $idPengembalian = (int) $this->db->lastInsertId();

            $detail = $this->db->prepare("SELECT id_detail, id_barang, qty, tipe_item FROM detail_transaksi WHERE id_transaksi = ?");
            $detail->execute([$idTransaksi]);
            $items = $detail->fetchAll();

            $insertDetail = $this->db->prepare("INSERT INTO pengembalian_detail
                (id_pengembalian, id_detail, id_barang, qty_kembali_baik, qty_rusak)
                VALUES (:id_pengembalian, :id_detail, :id_barang, :qty_kembali_baik, :qty_rusak)");

            $stok = $this->db->prepare("UPDATE barang SET
                    stok = stok + :qty_baik_update,
                    stok_disewa = GREATEST(stok_disewa - :qty_total_update, 0),
                    stok_maintenance = stok_maintenance + :qty_rusak_update,
                    status = CASE
                        WHEN (stok + :qty_baik_status) > 0 THEN 'Tersedia'
                        WHEN (GREATEST(stok_disewa - :qty_total_status, 0)) > 0 THEN 'Disewa'
                        WHEN (stok_maintenance + :qty_rusak_status) > 0 THEN 'Maintenance'
                        ELSE 'Tersedia'
                    END
                WHERE id_barang = :id_barang");

            foreach ($items as $item) {
                $idDetail = (int) $item['id_detail'];
                if (($item['tipe_item'] ?? 'Barang') === 'External' || empty($item['id_barang'])) {
                    continue;
                }
                $qtyTotal = (int) $item['qty'];
                $qtyRusak = max(0, (int) ($rusakList[$idDetail] ?? 0));

                if ($qtyRusak > $qtyTotal) {
                    throw new Exception('Jumlah barang rusak tidak boleh lebih besar dari jumlah barang yang disewa.');
                }

                $qtyBaik = $qtyTotal - $qtyRusak;

                $insertDetail->execute([
                    ':id_pengembalian' => $idPengembalian,
                    ':id_detail' => $idDetail,
                    ':id_barang' => (int) $item['id_barang'],
                    ':qty_kembali_baik' => $qtyBaik,
                    ':qty_rusak' => $qtyRusak,
                ]);

                $stok->execute([
                    ':qty_baik_update' => $qtyBaik,
                    ':qty_total_update' => $qtyTotal,
                    ':qty_rusak_update' => $qtyRusak,
                    ':qty_baik_status' => $qtyBaik,
                    ':qty_total_status' => $qtyTotal,
                    ':qty_rusak_status' => $qtyRusak,
                    ':id_barang' => (int) $item['id_barang'],
                ]);
            }

            $status = $this->db->prepare("UPDATE transaksi SET status_transaksi = 'Selesai' WHERE id_transaksi = ?");
            $status->execute([$idTransaksi]);

            return $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
