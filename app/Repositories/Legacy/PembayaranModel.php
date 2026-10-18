<?php
namespace App\Repositories\Legacy;
use PDO;
use Exception;
use Throwable;
use DateTime;
use Illuminate\Support\Facades\DB;


class PembayaranModel
{
    private PDO $db;
    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT pb.*, t.total_harga, p.nama_pelanggan
            FROM pembayaran pb
            JOIN transaksi t ON pb.id_transaksi = t.id_transaksi
            JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
            ORDER BY pb.id_pembayaran DESC");
        return $stmt->fetchAll();
    }
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT pb.*, t.total_harga, p.nama_pelanggan
            FROM pembayaran pb
            JOIN transaksi t ON pb.id_transaksi = t.id_transaksi
            JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
            WHERE pb.id_pembayaran = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }
    public function getByTransaksi(int $idTransaksi): array
    {
        $stmt = $this->db->prepare("SELECT * FROM pembayaran WHERE id_transaksi = ? ORDER BY id_pembayaran DESC");
        $stmt->execute([$idTransaksi]);
        return $stmt->fetchAll();
    }
    public function getTotalDikonfirmasi(int $idTransaksi): int
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(jumlah_bayar), 0) AS total_bayar
            FROM pembayaran
            WHERE id_transaksi = ? AND status_konfirmasi = 'Dikonfirmasi'");
        $stmt->execute([$idTransaksi]);
        return (int) ($stmt->fetch()['total_bayar'] ?? 0);
    }
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO pembayaran
            (id_transaksi, tanggal_bayar, jumlah_bayar, jenis_pembayaran, status_konfirmasi)
            VALUES (:id_transaksi, :tanggal_bayar, :jumlah_bayar, :jenis_pembayaran, :status_konfirmasi)");
        return $stmt->execute([
            ':id_transaksi' => $data['id_transaksi'],
            ':tanggal_bayar' => $data['tanggal_bayar'],
            ':jumlah_bayar' => $data['jumlah_bayar'],
            ':jenis_pembayaran' => $data['jenis_pembayaran'],
            ':status_konfirmasi' => $data['status_konfirmasi'],
        ]);
    }
    public function updateStatus(int $idPembayaran, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE pembayaran SET status_konfirmasi = ? WHERE id_pembayaran = ?");
        return $stmt->execute([$status, $idPembayaran]);
    }
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM pembayaran WHERE id_pembayaran = ?");
        return $stmt->execute([$id]);
    }
}
