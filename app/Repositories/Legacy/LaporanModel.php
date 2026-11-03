<?php
namespace App\Repositories\Legacy;
use PDO;
use Exception;
use Throwable;
use DateTime;
use Illuminate\Support\Facades\DB;


class LaporanModel
{
    private PDO $db;
    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    public function getRingkasan(string $mulai, string $selesai): array
    {
        $stmt = $this->db->prepare("SELECT
                COUNT(*) AS total_transaksi,
                COALESCE(SUM(CASE WHEN status_transaksi <> 'Batal' THEN total_harga ELSE 0 END), 0) AS total_nilai_transaksi,
                SUM(CASE WHEN status_transaksi = 'Booking' THEN 1 ELSE 0 END) AS total_booking,
                SUM(CASE WHEN status_transaksi = 'Berjalan' THEN 1 ELSE 0 END) AS total_berjalan,
                SUM(CASE WHEN status_transaksi = 'Selesai' THEN 1 ELSE 0 END) AS total_selesai,
                SUM(CASE WHEN status_transaksi = 'Batal' THEN 1 ELSE 0 END) AS total_batal
            FROM transaksi
            WHERE tanggal_transaksi BETWEEN ? AND ?");
        $stmt->execute([$mulai, $selesai]);
        $ringkasan = $stmt->fetch() ?: [];

        $stmtBayar = $this->db->prepare("SELECT COALESCE(SUM(jumlah_bayar), 0) AS total_pendapatan
            FROM pembayaran
            WHERE status_konfirmasi = 'Dikonfirmasi'
            AND tanggal_bayar BETWEEN ? AND ?");
        $stmtBayar->execute([$mulai, $selesai]);
        $bayar = $stmtBayar->fetch() ?: [];

        $ringkasan['total_pendapatan'] = $bayar['total_pendapatan'] ?? 0;
        return $ringkasan;
    }
    public function getLaporanTransaksi(string $mulai, string $selesai): array
    {
        // Agregasi pembayaran dan denda sebelum JOIN agar aman pada ONLY_FULL_GROUP_BY.
        // Satu transaksi tetap menghasilkan satu baris, walaupun memiliki banyak pembayaran.
        $stmt = $this->db->prepare("SELECT t.*, p.nama_pelanggan, k.nama_karyawan,
                COALESCE(pb.total_bayar, 0) AS total_bayar,
                COALESCE(pg.total_denda, 0) AS total_denda
            FROM transaksi t
            JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
            JOIN karyawan k ON t.id_karyawan = k.id_karyawan
            LEFT JOIN (
                SELECT id_transaksi, SUM(CASE WHEN jenis_pembayaran IN ('DP','Pelunasan') THEN jumlah_bayar ELSE 0 END) AS total_bayar
                FROM pembayaran
                WHERE status_konfirmasi = 'Dikonfirmasi'
                GROUP BY id_transaksi
            ) pb ON t.id_transaksi = pb.id_transaksi
            LEFT JOIN (
                SELECT id_transaksi, SUM(denda + denda_manual) AS total_denda
                FROM pengembalian
                GROUP BY id_transaksi
            ) pg ON t.id_transaksi = pg.id_transaksi
            WHERE t.tanggal_transaksi BETWEEN ? AND ?
            ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC");
        $stmt->execute([$mulai, $selesai]);
        return $stmt->fetchAll();
    }

    public function getLaporanPembayaran(string $mulai, string $selesai): array
    {
        $stmt = $this->db->prepare("SELECT pb.*, t.total_harga, p.nama_pelanggan
            FROM pembayaran pb
            JOIN transaksi t ON pb.id_transaksi = t.id_transaksi
            JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
            WHERE pb.tanggal_bayar BETWEEN ? AND ?
            ORDER BY pb.tanggal_bayar DESC, pb.id_pembayaran DESC");
        $stmt->execute([$mulai, $selesai]);
        return $stmt->fetchAll();
    }

    /** Margin hanya dari baris mitra dengan biaya diketahui; bukan laba bersih perusahaan. */
    public function getLaporanMitra(string $mulai, string $selesai): array
    {
        $stmt = $this->db->prepare("SELECT dt.id_detail, t.id_transaksi, t.tanggal_transaksi,
            p.nama_pelanggan, dt.nama_item_external AS nama_barang, dt.sumber_external, dt.id_barang_mitra, t.jenis_transaksi, dt.termasuk_paket, dt.dikembalikan_ke_mitra_at,
            dt.qty, dt.harga_satuan AS harga_pelanggan, dt.harga_mitra,
            CASE WHEN t.jenis_transaksi='Paketan' AND dt.termasuk_paket=1 THEN NULL ELSE dt.subtotal END AS total_jual,
            (dt.qty * dt.harga_mitra) AS biaya_mitra,
            CASE WHEN t.jenis_transaksi='Paketan' AND dt.termasuk_paket=1 THEN NULL
                 ELSE (dt.subtotal - dt.qty * dt.harga_mitra) END AS margin
            FROM detail_transaksi dt
            JOIN transaksi t ON t.id_transaksi=dt.id_transaksi
            JOIN pelanggan p ON p.id_pelanggan=t.id_pelanggan
            WHERE dt.tipe_item='External' AND t.status_transaksi <> 'Batal'
              AND t.tanggal_transaksi BETWEEN ? AND ?
            ORDER BY t.tanggal_transaksi DESC, dt.id_detail DESC");
        $stmt->execute([$mulai,$selesai]);
        return $stmt->fetchAll();
    }

    public function getLaporanBarang(): array
    {
        // Hitung rekap per barang dalam subquery agar SELECT b.* tidak melanggar
        // SQL mode ONLY_FULL_GROUP_BY.
        $stmt = $this->db->query("SELECT b.*,
                COALESCE(dt.total_disewa, 0) AS total_disewa,
                COALESCE(dt.total_pemasukan_barang, 0) AS total_pemasukan_barang
            FROM barang b
            LEFT JOIN (
                SELECT d.id_barang, SUM(d.qty) AS total_disewa,
                       SUM(d.subtotal) AS total_pemasukan_barang
                FROM detail_transaksi d
                JOIN transaksi t ON t.id_transaksi=d.id_transaksi
                WHERE d.id_barang IS NOT NULL AND t.status_transaksi <> 'Batal'
                GROUP BY d.id_barang
            ) dt ON b.id_barang = dt.id_barang
            ORDER BY b.nama_barang ASC");
        return $stmt->fetchAll();
    }

    public function getTransaksiById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT t.*, p.nama_pelanggan, p.no_hp, p.alamat, k.nama_karyawan
            FROM transaksi t
            JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
            JOIN karyawan k ON t.id_karyawan = k.id_karyawan
            WHERE t.id_transaksi = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    public function getDetailTransaksi(int $idTransaksi): array
    {
        $stmt = $this->db->prepare("SELECT dt.*, COALESCE(b.nama_barang, dt.nama_item_external) AS nama_barang,
                   COALESCE(b.kategori, 'External') AS kategori
            FROM detail_transaksi dt
            LEFT JOIN barang b ON dt.id_barang = b.id_barang
            WHERE dt.id_transaksi = ?");
        $stmt->execute([$idTransaksi]);
        return $stmt->fetchAll();
    }

    public function getPembayaranByTransaksi(int $idTransaksi): array
    {
        $stmt = $this->db->prepare("SELECT * FROM pembayaran WHERE id_transaksi = ? ORDER BY tanggal_bayar ASC");
        $stmt->execute([$idTransaksi]);
        return $stmt->fetchAll();
    }

    public function getTotalPembayaranDikonfirmasi(int $idTransaksi): int
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(jumlah_bayar), 0) AS total
            FROM pembayaran
            WHERE id_transaksi = ? AND status_konfirmasi = 'Dikonfirmasi'");
        $stmt->execute([$idTransaksi]);
        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function getTotalDenda(int $idTransaksi): int
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(denda + denda_manual), 0) AS total
            FROM pengembalian
            WHERE id_transaksi = ?");
        $stmt->execute([$idTransaksi]);
        return (int) ($stmt->fetch()['total'] ?? 0);
    }
}
