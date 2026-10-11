<?php
include __DIR__ . '/BaseController.php';
include __DIR__ . '/../models/BarangModel.php';

class BarangController extends BaseController
{
    private BarangModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->requireLogin();
        $this->model = new BarangModel();
    }

    public function index(): void
    {
        $barang = $this->model->getAll();
        include __DIR__ . '/../views/barang/index.php';
    }

    public function create(): void
    {
        $this->requireOwner();
        include __DIR__ . '/../views/barang/create.php';
    }

    public function store(): void
    {
        $this->requireOwner();
        $gambar = $this->uploadGambar();

        $this->model->create([
            'nama_barang'      => trim($_POST['nama_barang'] ?? ''),
            'kategori'         => trim($_POST['kategori'] ?? ''),
            'harga_sewa'       => (int) str_replace(',', '', $_POST['harga_sewa'] ?? 0),
            'stok'             => (int) ($_POST['stok'] ?? 0),
            'stok_disewa'      => 0,
            'stok_maintenance' => (int) ($_POST['stok_maintenance'] ?? 0),
            'gambar'           => $gambar,
        ]);

        $_SESSION['info'] = 'Barang berhasil disimpan';
        header('Location: /admin/barang');
    }

    public function edit(): void
    {
        $this->requireOwner();
        $id = (int) ($_GET['id'] ?? 0);
        $data = $this->model->getById($id);

        if (!$data) {
            $_SESSION['info'] = 'Barang tidak ditemukan';
            header('Location: /admin/barang');
            return;
        }

        include __DIR__ . '/../views/barang/edit.php';
    }

    public function update(): void
    {
        $this->requireOwner();
        $id = (int) ($_POST['id_barang'] ?? 0);
        $barangLama = $this->model->getById($id);

        if (!$barangLama) {
            $_SESSION['info'] = 'Barang tidak ditemukan';
            header('Location: /admin/barang');
            return;
        }

        $gambar = $this->uploadGambar($barangLama['gambar'] ?? null);

        $this->model->update($id, [
            'nama_barang'      => trim($_POST['nama_barang'] ?? ''),
            'kategori'         => trim($_POST['kategori'] ?? ''),
            'harga_sewa'       => (int) str_replace(',', '', $_POST['harga_sewa'] ?? 0),
            'stok'             => (int) ($_POST['stok'] ?? 0),
            'stok_maintenance' => (int) ($_POST['stok_maintenance'] ?? 0),
            'gambar'           => $gambar,
        ]);

        $_SESSION['info'] = 'Barang berhasil diupdate';
        header('Location: /admin/barang');
    }

    public function delete(): void
    {
        $this->requireOwner();
        $id = (int) ($_GET['id'] ?? 0);

        if ($this->model->dipakaiDiTransaksi($id)) {
            $_SESSION['info'] = 'Barang gagal dihapus karena sudah dipakai dalam transaksi';
            header('Location: /admin/barang');
            return;
        }

        $barang = $this->model->getById($id);
        if ($barang && !empty($barang['gambar'])) {
            $path = __DIR__ . '/../../public/assets/img/barang/' . $barang['gambar'];
            if (is_file($path)) {
                unlink($path);
            }
        }

        $this->model->delete($id);
        $_SESSION['info'] = 'Barang berhasil dihapus';
        header('Location: /admin/barang');
    }

    
private function uploadGambar(?string $gambarLama = null): ?string
    {
        if (empty($_FILES['gambar']['name'])) {
            return $gambarLama;
        }

        if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
            return $gambarLama;
        }

        $folderUpload = __DIR__ . '/../../public/assets/img/barang/';

        if (!is_dir($folderUpload)) {
            mkdir($folderUpload, 0777, true);
        }

        $namaAsli = $_FILES['gambar']['name'];
        $ekstensi = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));
        $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ekstensi, $ekstensiValid, true)) {
            $_SESSION['info'] = 'Format gambar tidak didukung. Gunakan jpg, jpeg, png, webp, atau gif.';
            return $gambarLama;
        }

        $namaFile = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $namaAsli);
        $tujuan = $folderUpload . $namaFile;

        if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $tujuan)) {
            $_SESSION['info'] = 'Upload gambar gagal. Cek permission folder public/assets/img/barang.';
            return $gambarLama;
        }

        if ($gambarLama) {
            $pathLama = $folderUpload . $gambarLama;
            if (is_file($pathLama)) {
                unlink($pathLama);
            }
        }

        return $namaFile;
    }
}
