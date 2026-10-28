<?php
namespace App\Http\Controllers;

use App\Repositories\Legacy\{PembayaranModel,TransaksiModel};
use App\Services\PembayaranWorkflowService;
use Illuminate\Http\Request;
use Throwable;

class PembayaranController extends Controller
{
    public function index(PembayaranModel $model)
    {
        $pembayaran = $model->getAll();
        return view('pembayaran.index', compact('pembayaran'));
    }

    public function create(Request $request, PembayaranWorkflowService $workflow)
    {
        $idTransaksi = $request->integer('id_transaksi');
        $tm = new TransaksiModel;
        $daftarTransaksi = $tm->getAll();
        $transaksi = $idTransaksi > 0 ? $tm->getById($idTransaksi) : null;
        if ($idTransaksi > 0 && !$transaksi) return redirect('/admin/pembayaran')->with('info','Transaksi tidak ditemukan');
        $ringkasan = $transaksi ? $workflow->ringkasan($idTransaksi) : null;
        $jenisOtomatis = $transaksi && $transaksi['status_transaksi'] === 'Selesai' ? 'Denda' : 'DP';
        $sisaBayar = $ringkasan ? ($jenisOtomatis === 'Denda' ? $ringkasan['sisa_denda'] : $ringkasan['sisa_sewa']) : 0;
        $sudahLunas = $transaksi && $sisaBayar <= 0;
        $pending = $transaksi && \Illuminate\Support\Facades\DB::table('pembayaran')->where('id_transaksi', $idTransaksi)->where('status_konfirmasi','Menunggu')->exists();
        return view('pembayaran.create', compact('idTransaksi','transaksi','daftarTransaksi','ringkasan','jenisOtomatis','sisaBayar','sudahLunas','pending'));
    }

    public function store(Request $request, PembayaranWorkflowService $workflow)
    {
        $data = $request->validate([
            'id_transaksi'=>'required|integer|exists:transaksi,id_transaksi',
            'tanggal_bayar'=>['required','date',\Illuminate\Validation\Rule::in([\Carbon\Carbon::today('Asia/Jakarta')->toDateString()])],
            'jenis_pembayaran'=>'required|in:DP,Pelunasan,Denda',
            'jumlah_bayar'=>'nullable|integer|min:1',
            'status_konfirmasi'=>'required|in:Menunggu,Dikonfirmasi',
        ]);
        if ($request->user()?->role !== 'Owner' && $data['status_konfirmasi'] !== 'Menunggu') {
            return back()->withInput()->with('info','Konfirmasi pembayaran hanya dapat dilakukan Owner.');
        }
        try {
            $workflow->tambah($data);
            return redirect('/admin/transaksi/detail?id='.$data['id_transaksi'])->with('info','Pembayaran berhasil dicatat.');
        } catch (Throwable $e) {
            return back()->withInput()->with('info','Pembayaran gagal: '.$e->getMessage());
        }
    }

    public function status(Request $request, PembayaranWorkflowService $workflow)
    {
        $data = $request->validate(['id_pembayaran'=>'required|integer|exists:pembayaran,id_pembayaran', 'status_konfirmasi'=>'required|in:Menunggu,Dikonfirmasi,Ditolak']);
        try {
            $id = $workflow->ubahStatus((int)$data['id_pembayaran'], $data['status_konfirmasi']);
            return redirect('/admin/transaksi/detail?id='.$id)->with('info','Status pembayaran diperbarui.');
        } catch (Throwable $e) {
            return back()->with('info',$e->getMessage());
        }
    }

    public function delete(Request $request, PembayaranWorkflowService $workflow)
    {
        $data = $request->validate(['id'=>'required|integer|exists:pembayaran,id_pembayaran']);
        try {
            $id = $workflow->hapus((int)$data['id']);
            return redirect('/admin/transaksi/detail?id='.$id)->with('info','Pembayaran belum terkonfirmasi dihapus.');
        } catch (Throwable $e) {
            return back()->with('info',$e->getMessage());
        }
    }
}
