<?php
namespace App\Http\Controllers;
use App\Repositories\Legacy\LaporanModel;
use Illuminate\Http\Request;
class LaporanController extends Controller
{
    public function index(Request $request,LaporanModel $model) {
        $request->validate(['tanggal_mulai'=>'nullable|date','tanggal_selesai'=>'nullable|date|after_or_equal:tanggal_mulai']);
        $tanggalMulai=$request->input('tanggal_mulai',date('Y-m-01'));
        $tanggalSelesai=$request->input('tanggal_selesai',date('Y-m-d'));
        $ringkasan=$model->getRingkasan($tanggalMulai,$tanggalSelesai);
        $laporanTransaksi=$model->getLaporanTransaksi($tanggalMulai,$tanggalSelesai);
        $laporanPembayaran=$model->getLaporanPembayaran($tanggalMulai,$tanggalSelesai);
        $laporanBarang=$model->getLaporanBarang();
        return view('laporan.index',compact('tanggalMulai','tanggalSelesai','ringkasan','laporanTransaksi','laporanPembayaran','laporanBarang'));
    }
    public function invoice(Request $request,LaporanModel $model) {
        $id=$request->integer('id');$transaksi=$model->getTransaksiById($id);
        if(!$transaksi) return redirect('/admin/laporan')->with('info','Transaksi tidak ditemukan');
        $detail=$model->getDetailTransaksi($id);$pembayaran=$model->getPembayaranByTransaksi($id);
        $totalBayar=$model->getTotalPembayaranDikonfirmasi($id);$totalDenda=$model->getTotalDenda($id);
        $totalTagihan=(int)$transaksi['total_harga']+$totalDenda;
        $sisaBayar=max(0,$totalTagihan-$totalBayar);
        return view('laporan.invoice',compact('transaksi','detail','pembayaran','totalBayar','totalDenda','totalTagihan','sisaBayar'));
    }
}
