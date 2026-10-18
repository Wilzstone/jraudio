<?php
namespace App\Repositories\Legacy;
use PDO;
use Exception;
use Throwable;
use DateTime;
use Illuminate\Support\Facades\DB;



class KaryawanModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT k.*, u.username, u.role
            FROM karyawan k
            JOIN users u ON k.id_user = u.id_user
            ORDER BY k.id_karyawan DESC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT k.*, u.username, u.role
            FROM karyawan k
            JOIN users u ON k.id_user = u.id_user
            WHERE k.id_karyawan = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    public function create(array $data): bool
    {
        $this->db->beginTransaction();
        try {
            $stmtUser = $this->db->prepare("INSERT INTO users (username, password, role)
                VALUES (:username, :password, :role)");
            $stmtUser->execute([
                ':username' => $data['username'],
                ':password' => $data['password'],
                ':role'     => $data['role'],
            ]);

            $idUser = (int) $this->db->lastInsertId();

            $stmtKaryawan = $this->db->prepare("INSERT INTO karyawan
                (id_user, nama_karyawan, no_hp, alamat, jabatan)
                VALUES (:id_user, :nama_karyawan, :no_hp, :alamat, :jabatan)");
            $stmtKaryawan->execute([
                ':id_user'        => $idUser,
                ':nama_karyawan'  => $data['nama_karyawan'],
                ':no_hp'          => $data['no_hp'],
                ':alamat'         => $data['alamat'],
                ':jabatan'        => $data['jabatan'],
            ]);

            return $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $this->db->beginTransaction();
        try {
            if ($data['password'] !== null) {
                $stmtUser = $this->db->prepare("UPDATE users SET username = :username,
                 password = :password, role = :role WHERE id_user = :id_user");
                $stmtUser->execute([
                    ':username' => $data['username'],
                    ':password' => $data['password'],
                    ':role'     => $data['role'],
                    ':id_user'  => $data['id_user'],
                ]);
            } else {
                $stmtUser = $this->db->prepare("UPDATE users SET username = :username, role = :role WHERE id_user = :id_user");
                $stmtUser->execute([
                    ':username' => $data['username'],
                    ':role'     => $data['role'],
                    ':id_user'  => $data['id_user'],
                ]);
            }

            $stmtKaryawan = $this->db->prepare("UPDATE karyawan SET
                nama_karyawan = :nama_karyawan,
                no_hp = :no_hp,
                alamat = :alamat,
                jabatan = :jabatan
                WHERE id_karyawan = :id_karyawan");
            $stmtKaryawan->execute([
                ':nama_karyawan' => $data['nama_karyawan'],
                ':no_hp'         => $data['no_hp'],
                ':alamat'        => $data['alamat'],
                ':jabatan'       => $data['jabatan'],
                ':id_karyawan'   => $id,
            ]);

            return $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    public function delete(int $id): bool
    {
        $data = $this->getById($id);
        if (!$data) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $stmtKaryawan = $this->db->prepare("DELETE FROM karyawan WHERE id_karyawan = ?");
            $stmtKaryawan->execute([$id]);

            $stmtUser = $this->db->prepare("DELETE FROM users WHERE id_user = ?");
            $stmtUser->execute([(int) $data['id_user']]);

            return $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function dipakaiDiTransaksi(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM transaksi WHERE id_karyawan = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return ((int) $row['total']) > 0;
    }

    public function getByUserId(int $idUser): ?array
    {
        $stmt = $this->db->prepare("SELECT k.*, u.username, u.role
            FROM karyawan k
            JOIN users u ON k.id_user = u.id_user
            WHERE k.id_user = ?");
        $stmt->execute([$idUser]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

}
