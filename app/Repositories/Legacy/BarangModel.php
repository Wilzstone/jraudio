<?php
namespace App\Repositories\Legacy;
use PDO;
use Exception;
use Throwable;
use DateTime;
use Illuminate\Support\Facades\DB;



class BarangModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT *,
            (stok + stok_disewa + stok_maintenance) AS stok_total
            FROM barang
            ORDER BY id_barang DESC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT *,
            (stok + stok_disewa + stok_maintenance) AS stok_total
            FROM barang WHERE id_barang = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    private function hitungStatus(int $stok, int $stokDisewa, int $stokMaintenance): string
    {
        if ($stok > 0) {
            return 'Tersedia';
        }
        if ($stokDisewa > 0) {
            return 'Disewa';
        }
        if ($stokMaintenance > 0) {
            return 'Maintenance';
        }
        return 'Tersedia';
    }

    public function create(array $data): bool
    {
        $stok = (int) ($data['stok'] ?? 0);
        $stokDisewa = (int) ($data['stok_disewa'] ?? 0);
        $stokMaintenance = (int) ($data['stok_maintenance'] ?? 0);
        $status = $this->hitungStatus($stok, $stokDisewa, $stokMaintenance);

        $stmt = $this->db->prepare("INSERT INTO barang
            (nama_barang, kategori, harga_sewa, stok, stok_disewa, stok_maintenance, status, gambar)
            VALUES (:nama_barang, :kategori, :harga_sewa, :stok, :stok_disewa, :stok_maintenance, :status, :gambar)");

        return $stmt->execute([
            ':nama_barang'      => $data['nama_barang'],
            ':kategori'         => $data['kategori'],
            ':harga_sewa'       => $data['harga_sewa'],
            ':stok'             => $stok,
            ':stok_disewa'      => $stokDisewa,
            ':stok_maintenance' => $stokMaintenance,
            ':status'           => $status,
            ':gambar'           => $data['gambar'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $barangLama = $this->getById($id);
        $stok = (int) ($data['stok'] ?? 0);
        $stokDisewa = (int) ($barangLama['stok_disewa'] ?? 0);
        $stokMaintenance = (int) ($data['stok_maintenance'] ?? 0);
        $status = $this->hitungStatus($stok, $stokDisewa, $stokMaintenance);

        $stmt = $this->db->prepare("UPDATE barang SET
            nama_barang      = :nama_barang,
            kategori         = :kategori,
            harga_sewa       = :harga_sewa,
            stok             = :stok,
            stok_maintenance = :stok_maintenance,
            status           = :status,
            gambar           = :gambar
            WHERE id_barang  = :id_barang");

        return $stmt->execute([
            ':nama_barang'      => $data['nama_barang'],
            ':kategori'         => $data['kategori'],
            ':harga_sewa'       => $data['harga_sewa'],
            ':stok'             => $stok,
            ':stok_maintenance' => $stokMaintenance,
            ':status'           => $status,
            ':gambar'           => $data['gambar'],
            ':id_barang'        => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM barang WHERE id_barang = ?");
        return $stmt->execute([$id]);
    }

    public function dipakaiDiTransaksi(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM detail_transaksi WHERE id_barang = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return ((int) $row['total']) > 0;
    }

    public function getTersedia(): array
    {
        $stmt = $this->db->query("SELECT *,
            (stok + stok_disewa + stok_maintenance) AS stok_total
            FROM barang
            WHERE stok > 0
            ORDER BY nama_barang ASC");
        return $stmt->fetchAll();
    }
}
