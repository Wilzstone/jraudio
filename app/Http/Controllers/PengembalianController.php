<?php
namespace App\Http\Controllers;
use App\Repositories\Legacy\PengembalianModel;
use Illuminate\Http\Request;
use Throwable;
class PengembalianController extends Controller
{
    public function index(PengembalianModel $model) {
        $transaksiBerjalan=$model->getTransaksiBerjalan();$detailTransaksi=[];
        foreach($transaksiBerjalan as $trx) $detailTransaksi[$trx['id_transaksi']]=$model->getDetailTransaksi((int)$trx['id_transaksi']);
        $riwayat=$model->getRiwayat();$detailRiwayat=[];
        foreach($riwayat as $row) $detailRiwayat[$row['id_pengembalian']]=$model->getDetailPengembalian((int)$row['id_pengembalian']);
        return view('pengembalian.index',compact('transaksiBerjalan','detailTransaksi','riwayat','detailRiwayat'));
    }
    public function store(Request $request,PengembalianModel $model) {
        $data=$request->validate(['id_transaksi'=>'required|integer|exists:transaksi,id_transaksi',
          'tanggal_pengembalian'=>'required|date','kondisi_barang'=>'required|string|max:255',
          'catatan'=>'nullable|string|max:3000','denda_manual'=>'nullable|integer|min:0',
          'rusak_qty'=>'nullable|array','rusak_qty.*'=>'integer|min:0']);
        try { $model->proses($data);return redirect('/admin/pengembalian')->with('info','Pengembalian diproses. Stok dan denda diperbarui'); }
        catch(Throwable $e) {return back()->with('info','Pengembalian gagal: '.$e->getMessage());}
    }
}
