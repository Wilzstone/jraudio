<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, PublicController, BarangController, KaryawanController, PelangganController, DashboardController, TransaksiController, PembayaranController, PengembalianController, LaporanController, RekomendasiController, PengajuanPublikController, PengajuanAdminController};

Route::get('/', [PublicController::class, 'index']);
Route::get('/home', [PublicController::class, 'index']);
foreach (['tentang','barang','lokasi','kontak','paket','galeri','partner'] as $halaman) {
    Route::get('/'.$halaman, [PublicController::class, $halaman]);
}
Route::get('/rekomendasi', [RekomendasiController::class, 'index'])->name('rekomendasi.index');
Route::get('/rekomendasi/hasil/{draftToken}', [RekomendasiController::class, 'hasil'])->where('draftToken', '[A-Za-z0-9]{40}')->name('rekomendasi.hasil');
Route::post('/rekomendasi', [RekomendasiController::class, 'proses'])->middleware('throttle:12,1')->name('rekomendasi.proses');
Route::post('/pengajuan/simpan', [PengajuanPublikController::class, 'simpan'])->middleware('throttle:8,1')->name('pengajuan.public.simpan');
Route::get('/pengajuan/{kode}', [PengajuanPublikController::class, 'show'])->where('kode', 'JR-[0-9]{6}-[A-Z0-9]{12}')->name('pengajuan.public.show');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/proses-login', [AuthController::class, 'prosesLogin'])->middleware('throttle:5,1');

Route::middleware('auth')->group(function () {
    Route::get('/admin/pengajuan', [PengajuanAdminController::class, 'index'])->name('pengajuan.admin.index');
    Route::get('/admin/pengajuan/{kode}', [PengajuanAdminController::class, 'show'])->name('pengajuan.admin.show');
    Route::post('/admin/pengajuan/{kode}/simpan', [PengajuanAdminController::class, 'simpan'])->name('pengajuan.admin.simpan');
    Route::post('/admin/pengajuan/{kode}/batal', [PengajuanAdminController::class, 'batalkan'])->name('pengajuan.admin.batal');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/cek-ketersediaan', [BarangController::class, 'index']);
    Route::get('/transaksi', [TransaksiController::class, 'index']);
    Route::get('/transaksi/tambah', [TransaksiController::class, 'create']);
    Route::get('/pengembalian', [PengembalianController::class, 'index']);
    Route::get('/admin/barang', [BarangController::class, 'index']);
    Route::get('/admin/transaksi', [TransaksiController::class, 'index']);
    Route::get('/admin/transaksi/tambah', [TransaksiController::class, 'create']);
    Route::post('/admin/transaksi/simpan', [TransaksiController::class, 'store']);
    Route::get('/admin/transaksi/detail', [TransaksiController::class, 'show']);
    Route::post('/admin/transaksi/status', [TransaksiController::class, 'status']);
    Route::get('/admin/pembayaran/tambah', [PembayaranController::class, 'create']);
    Route::post('/admin/pembayaran/simpan', [PembayaranController::class, 'store']);
    Route::get('/admin/pengembalian', [PengembalianController::class, 'index']);
    Route::post('/admin/pengembalian/simpan', [PengembalianController::class, 'store']);

    Route::middleware('role:Owner')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/admin/barang/tambah', [BarangController::class, 'create']);
        Route::post('/admin/barang/simpan', [BarangController::class, 'store']);
        Route::get('/admin/barang/edit', [BarangController::class, 'edit']);
        Route::post('/admin/barang/update', [BarangController::class, 'update']);
        Route::post('/admin/barang/hapus', [BarangController::class, 'delete']);
        Route::get('/admin/karyawan', [KaryawanController::class, 'index']);
        Route::get('/admin/karyawan/tambah', [KaryawanController::class, 'create']);
        Route::post('/admin/karyawan/simpan', [KaryawanController::class, 'store']);
        Route::get('/admin/karyawan/edit', [KaryawanController::class, 'edit']);
        Route::post('/admin/karyawan/update', [KaryawanController::class, 'update']);
        Route::post('/admin/karyawan/hapus', [KaryawanController::class, 'delete']);
        Route::get('/admin/pelanggan', [PelangganController::class, 'index']);
        Route::get('/admin/pelanggan/tambah', [PelangganController::class, 'create']);
        Route::post('/admin/pelanggan/simpan', [PelangganController::class, 'store']);
        Route::get('/admin/pelanggan/edit', [PelangganController::class, 'edit']);
        Route::post('/admin/pelanggan/update', [PelangganController::class, 'update']);
        Route::post('/admin/pelanggan/hapus', [PelangganController::class, 'delete']);
        Route::get('/admin/pembayaran', [PembayaranController::class, 'index']);
        Route::post('/admin/pembayaran/status', [PembayaranController::class, 'status']);
        Route::post('/admin/pembayaran/hapus', [PembayaranController::class, 'delete']);
        Route::get('/admin/laporan', [LaporanController::class, 'index']);
        Route::get('/admin/invoice', [LaporanController::class, 'invoice']);
    });
});
