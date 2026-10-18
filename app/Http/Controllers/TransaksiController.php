<?php
namespace App\Http\Controllers;
use App\Repositories\Legacy\{TransaksiModel,PelangganModel,BarangModel,KaryawanModel,PembayaranModel,PaketModel};
use Illuminate\Http\Request;
use Throwable;
class TransaksiController extends Controller
{
    public function index(TransaksiModel $model) {$transaksi=$model->getAll();return view('transaksi.index',compact('transaksi'));}
    public function create(Request $request) {
        $pelanggan=(new PelangganModel)->getAll();$barang=(new BarangModel)->getTersedia();
        $paketModel=new PaketModel;$paket=$paketModel->getAktif();$paketDetail=[];
        foreach($paket as $p) $paketDetail[$p['id_paket']]=$paketModel->getDetail((int)$p['id_paket']);
        $old=$request->session()->get('old_input_transaksi',[]);
        return view('transaksi.create',compact('pelanggan','barang','paket','paketDetail','old'));
    }
    public function store(Request $request,TransaksiModel $model) {
        $request->validate([
            'id_pelanggan'=>'required|integer|exists:pelanggan,id_pelanggan',
            'jenis_transaksi'=>'required|in:Satuan,Paketan',
            'tanggal_sewa'=>'required|date','tanggal_kembali'=>'required|date|after_or_equal:tanggal_sewa',
            'status_transaksi'=>'required|in:Booking,Berjalan',
            'id_paket'=>'nullable|integer','harga_paket_manual'=>'nullable|integer|min:0',
            'biaya_genset'=>'nullable|integer|min:0',
            'qty'=>'required|array|min:1','qty.*'=>'required|integer|min:1',
            'harga_manual_item.*'=>'nullable|integer|min:0'
        ]);
        try {
            $karyawan=(new KaryawanModel)->getByUserId((int)$request->user()->id_user);
            if (!$karyawan) throw new \RuntimeException('Data karyawan untuk user login belum ditemukan.');
            $jenis=$request->input('jenis_transaksi','Satuan');$idPaket=$request->integer('id_paket');$namaPaket=null;
            if ($jenis==='Paketan') {
                $p=(new PaketModel)->getById($idPaket);
                if (!$p) throw new \RuntimeException('Paket belum dipilih');
                $namaPaket=$p['nama_paket'];
            }
            $items=[];$types=$request->input('tipe_item',[]);
            $ids=$request->input('id_barang',[]);$names=$request->input('nama_item_external',[]);
            $sources=$request->input('sumber_external',[]);$qty=$request->input('qty',[]);$harga=$request->input('harga_manual_item',[]);
            foreach($qty as $i=>$n) $items[]=[
                'tipe_item'=>$types[$i]??'Barang','id_barang'=>(int)($ids[$i]??0),
                'nama_item_external'=>$names[$i]??'','sumber_external'=>$sources[$i]??'',
                'qty'=>(int)$n,'harga_satuan'=>(int)($harga[$i]??0)
            ];
            $id=$model->create([
                'id_pelanggan'=>$request->integer('id_pelanggan'),'id_karyawan'=>(int)$karyawan['id_karyawan'],
                'jenis_transaksi'=>$jenis,'id_paket'=>$idPaket,'nama_paket'=>$namaPaket,
                'harga_paket_manual'=>$request->integer('harga_paket_manual'),
                'biaya_genset'=>$request->integer('biaya_genset'),
                'tanggal_transaksi'=>date('Y-m-d'),'tanggal_sewa'=>$request->input('tanggal_sewa'),
                'tanggal_kembali'=>$request->input('tanggal_kembali'),
                'status_transaksi'=>$request->input('status_transaksi')
            ],$items);
            return redirect('/admin/transaksi/detail?id='.$id)->with('info','Transaksi berhasil dibuat');
        } catch(Throwable $e) {
            return redirect('/admin/transaksi/tambah')->withInput()->with('old_input_transaksi',$request->except('_token'))
                ->with('info','Transaksi gagal: '.$e->getMessage());
        }
    }
    public function show(Request $request,TransaksiModel $model) {
        $id=$request->integer('id');$data=$model->getById($id);
        if (!$data) return redirect('/admin/transaksi')->with('info','Transaksi tidak ditemukan');
        $detail=$model->getDetail($id);$pm=new PembayaranModel;
        $pembayaran=$pm->getByTransaksi($id);$totalBayar=$pm->getTotalDikonfirmasi($id);
        $totalDenda=$model->getDenda($id);$totalTagihan=(int)$data['total_harga']+$totalDenda;
        $sisaBayar=max(0,$totalTagihan-$totalBayar);$isSelesai=$data['status_transaksi']==='Selesai';
        return view('transaksi.show',compact('data','detail','pembayaran','totalBayar','totalDenda','totalTagihan','sisaBayar','isSelesai'));
    }
    public function status(Request $request,TransaksiModel $model) {
        $data=$request->validate(['id_transaksi'=>'required|integer|exists:transaksi,id_transaksi','status_transaksi'=>'required|in:Booking,Berjalan,Selesai,Batal']);
        try {$model->updateStatus((int)$data['id_transaksi'],$data['status_transaksi']);
        return redirect('/admin/transaksi/detail?id='.$data['id_transaksi'])->with('info','Status diubah');
        } catch(Throwable $e) {return back()->with('info',$e->getMessage());}
    }
}
