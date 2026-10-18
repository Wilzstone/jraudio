<?php
namespace App\Repositories\Legacy;
use PDO;
use Exception;
use Throwable;
use DateTime;
use Illuminate\Support\Facades\DB;



class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT u.*, k.id_karyawan, k.nama_karyawan, k.jabatan
            FROM users u
            LEFT JOIN karyawan k ON u.id_user = k.id_user
            WHERE u.username = ?
            LIMIT 1");
        $stmt->execute([$username]);
        $data = $stmt->fetch();
        return $data ?: null;
    }
}
