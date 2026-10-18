<?php
namespace App\Repositories\Legacy;
use PDO;
use Exception;
use Throwable;
use DateTime;
use Illuminate\Support\Facades\DB;


class PaketModel
{
    private PDO $db;
    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    public function getAktif(): array
    {
        $stmt = $this->db->query("SELECT * FROM paket WHERE status = 'Aktif' ORDER BY id_paket ASC");
        return $stmt->fetchAll();
    }
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM paket WHERE id_paket = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }
    public function getDetail(int $idPaket): array
    {
        $stmt = $this->db->prepare("SELECT pd.*, b.nama_barang, b.kategori, b.stok, b.stok_disewa, b.stok_maintenance
            FROM paket_detail pd
            JOIN barang b ON pd.id_barang = b.id_barang
            WHERE pd.id_paket = ?
            ORDER BY pd.id_paket_detail ASC");
        $stmt->execute([$idPaket]);
        return $stmt->fetchAll();
    }
    public function getDetailJson(int $idPaket): string
    {
        return json_encode($this->getDetail($idPaket));
    }
}
