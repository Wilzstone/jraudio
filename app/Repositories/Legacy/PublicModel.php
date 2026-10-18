<?php
namespace App\Repositories\Legacy;
use PDO;
use Exception;
use Throwable;
use DateTime;
use Illuminate\Support\Facades\DB;


class PublicModel
{
    private PDO $db;
    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    public function getBarangTersedia(): array
    {
        $stmt = $this->db->query("SELECT *,
            (stok + stok_disewa + stok_maintenance) AS stok_total
            FROM barang
            WHERE stok > 0
            ORDER BY nama_barang ASC");
        return $stmt->fetchAll();
    }
}
