<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\NotifikasiPembayaranService;
use App\Services\PembayaranWorkflowService;

class NotifikasiPembayaranController extends Controller
{
    public function show(int $id, NotifikasiPembayaranService $service)
    {
        $row = DB::table('notifikasi_pembayaran as n')
            ->join('transaksi as t','t.id_transaksi','=','n.id_transaksi')
            ->join('pelanggan as p','p.id_pelanggan','=','t.id_pelanggan')
            ->where('n.id_pembayaran',$id)
            ->select('n.*','p.nama_pelanggan','p.no_hp')->first();
        abort_unless($row,404);
        $wa = $service->linkWhatsapp($row->no_hp,$row->pesan);
        return view('pembayaran.notifikasi',compact('row','wa'));
    }
    public function tandai(Request $request, int $id)
    {
        $request->validate(['konfirmasi'=>'required|accepted']);
        $row = DB::table('notifikasi_pembayaran')->where('id_pembayaran',$id)->first();
        abort_unless($row,404);
        DB::table('notifikasi_pembayaran')->where('id_pembayaran',$id)
            ->where('status','Disiapkan')->update(['status'=>'Ditandai Terkirim',
                'ditandai_terkirim_at'=>now(), 'ditandai_oleh'=>(int)$request->user()->id_user,'updated_at'=>now()]);
        return redirect()->route('pembayaran.notifikasi.show',['id'=>$id])->with('info','Ditandai terkirim oleh admin. Ini bukan verifikasi pengiriman dari WhatsApp.');
    }
    public function pengingatIndex()
    {
        $rows=DB::table('transaksi as t')->join('pelanggan as p','p.id_pelanggan','=','t.id_pelanggan')
            ->leftJoin('pembayaran as pb',function($j){$j->on('pb.id_transaksi','=','t.id_transaksi')->where('pb.status_konfirmasi','=','Dikonfirmasi')->whereIn('pb.jenis_pembayaran',['DP','Pelunasan']);})
            ->whereIn('t.status_transaksi',['Booking','Berjalan'])
            ->groupBy('t.id_transaksi','t.tanggal_sewa','t.status_transaksi','t.total_harga','p.nama_pelanggan','p.no_hp')
            ->selectRaw('t.id_transaksi,t.tanggal_sewa,t.status_transaksi,t.total_harga,p.nama_pelanggan,p.no_hp,COALESCE(SUM(pb.jumlah_bayar),0) as total_dibayar')
            ->orderBy('t.tanggal_sewa')->get()->filter(fn($row)=>(int)$row->total_dibayar < (int)$row->total_harga);
        return view('pembayaran.pengingat',compact('rows'));
    }
    public function pengingatShow(int $id, NotifikasiPembayaranService $service, PembayaranWorkflowService $workflow)
    {
        $row=DB::table('transaksi as t')->join('pelanggan as p','p.id_pelanggan','=','t.id_pelanggan')
            ->where('t.id_transaksi',$id)->select('t.*','p.nama_pelanggan','p.no_hp')->first();
        abort_unless($row && in_array($row->status_transaksi,['Booking','Berjalan'],true),404);
        $r=$workflow->ringkasan($id);
        abort_unless($r['sisa_sewa']>0,404);
        $pesan='Halo Kak '.$row->nama_pelanggan.",\n\nKami ingin mengingatkan pembayaran sewa JR Audio untuk transaksi #".$id.
          ".\nTotal sewa: Rp ".number_format($r['total_sewa'],0,',','.').
          ".\nSudah dibayar (terkonfirmasi): Rp ".number_format($r['dibayar_sewa'],0,',','.').
          ".\nSisa pelunasan: Rp ".number_format($r['sisa_sewa'],0,',','.').
          ".\nTanggal acara: ".$row->tanggal_sewa.
          ".\n\nMohon konfirmasi rencana pelunasan kepada tim JR Audio. Terima kasih.";
        $wa=$service->linkWhatsapp($row->no_hp,$pesan);
        return view('pembayaran.pengingat-detail',compact('row','r','pesan','wa'));
    }
}
