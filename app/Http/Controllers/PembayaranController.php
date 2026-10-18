<?php
namespace App\Http\Controllers;
use App\Repositories\Legacy\{PembayaranModel,TransaksiModel};
use Illuminate\Http\Request;
class PembayaranController extends Controller
{
    public function index(PembayaranModel $model) {$pembayaran=$model->getAll();return view('pembayaran.index',compact('pembayaran'));}
    public function create(Request $request,PembayaranModel $model) {
        $idTransaksi=$request->integer('id_transaksi');$tm=new TransaksiModel;
        $transaksi=$idTransaksi>0?$tm->getById($idTransaksi):null;$daftarTransaksi=$tm->getAll();
        if($idTransaksi>0&&!$transaksi) return redirect('/admin/pembayaran')->with('info','Transaksi tidak ditemukan');
        $totalBayar=$idTransaksi>0?$model->getTotalDikonfirmasi($idTransaksi):0;
        $totalDenda=$idTransaksi>0?$tm->getDenda($idTransaksi):0;
        $totalTagihan=$transaksi?(int)$transaksi['total_harga']+$totalDenda:0;
        $sisaBayar=max(0,$totalTagihan-$totalBayar);
        if ($transaksi&&$transaksi['status_transaksi']==='Selesai'&&$sisaBayar<=0)
            return redirect('/admin/transaksi/detail?id='.$idTransaksi)->with('info','Transaksi telah lunas');
        return view('pembayaran.create',compact('idTransaksi','transaksi','daftarTransaksi','totalBayar','totalDenda','totalTagihan','sisaBayar'));
    }
    public function store(Request $request,PembayaranModel $model) {
        $data=$request->validate([
            'id_transaksi'=>'required|integer|exists:transaksi,id_transaksi',
            'tanggal_bayar'=>'required|date','jenis_pembayaran'=>'required|in:DP,Pelunasan',
            'jumlah_bayar'=>'required|integer|min:1','status_konfirmasi'=>'required|in:Menunggu,Dikonfirmasi,Ditolak'
        ]);
        $id=(int)$data['id_transaksi'];$tm=new TransaksiModel;$trx=$tm->getById($id);
        $sisa=max(0,(int)$trx['total_harga']+$tm->getDenda($id)-$model->getTotalDikonfirmasi($id));
        if($sisa<=0) return back()->with('info','Transaksi sudah lunas');
        if($data['jenis_pembayaran']==='Pelunasan')$data['jumlah_bayar']=$sisa;
        if($data['status_konfirmasi']==='Dikonfirmasi'&&$data['jumlah_bayar']>$sisa)
            return back()->with('info','Jumlah bayar melebihi sisa tagihan');
        $model->create($data);return redirect('/admin/transaksi/detail?id='.$id)->with('info','Pembayaran disimpan');
    }
    public function status(Request $request,PembayaranModel $model) {
        $data=$request->validate(['id_pembayaran'=>'required|integer|exists:pembayaran,id_pembayaran','status_konfirmasi'=>'required|in:Menunggu,Dikonfirmasi,Ditolak']);
        $row=$model->getById((int)$data['id_pembayaran']);
        if($data['status_konfirmasi']==='Dikonfirmasi' && $row['status_konfirmasi']!=='Dikonfirmasi') {
            $tm=new TransaksiModel;
            $trx=$tm->getById((int)$row['id_transaksi']);
            $sisa=max(0,(int)$trx['total_harga']+$tm->getDenda((int)$row['id_transaksi'])-$model->getTotalDikonfirmasi((int)$row['id_transaksi']));
            if((int)$row['jumlah_bayar']>$sisa) return back()->with('info','Pembayaran melebihi sisa tagihan, tidak dapat dikonfirmasi.');
        }
        $model->updateStatus((int)$data['id_pembayaran'],$data['status_konfirmasi']);
        return redirect('/admin/transaksi/detail?id='.$row['id_transaksi'])->with('info','Status pembayaran diubah');
    }
    public function delete(Request $request,PembayaranModel $model) {
        $id=$request->integer('id');$row=$model->getById($id);
        if($row) $model->delete($id);
        return redirect('/admin/transaksi/detail?id='.($row['id_transaksi']??0))->with('info','Pembayaran dihapus');
    }
}
