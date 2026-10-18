<?php
namespace App\Repositories\Legacy;
use PDO;
use Exception;
use Throwable;
use DateTime;
use Illuminate\Support\Facades\DB;


class PelangganModel
{
    private PDO $db;
    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
        return $stmt->fetchAll();
    }
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO pelanggan
            (nama_pelanggan, no_hp, alamat)
            VALUES (:nama_pelanggan, :no_hp, :alamat)");

        return $stmt->execute([
            ':nama_pelanggan' => $data['nama_pelanggan'],
            ':no_hp'          => $data['no_hp'],
            ':alamat'         => $data['alamat'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE pelanggan SET
            nama_pelanggan = :nama_pelanggan,
            no_hp          = :no_hp,
            alamat         = :alamat
            WHERE id_pelanggan = :id_pelanggan");

        return $stmt->execute([
            ':nama_pelanggan' => $data['nama_pelanggan'],
            ':no_hp'          => $data['no_hp'],
            ':alamat'         => $data['alamat'],
            ':id_pelanggan'   => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?");
        return $stmt->execute([$id]);
    }

    public function dipakaiDiTransaksi(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM transaksi WHERE id_pelanggan = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return ((int) $row['total']) > 0;
    }
}
