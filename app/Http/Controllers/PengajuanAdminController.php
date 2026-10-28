<?php
namespace App\Http\Controllers;

use App\Services\PengajuanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PengajuanAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', '');
        $query = DB::table('pengajuan_sewa')->orderByDesc('id_pengajuan');
        if (in_array($status, ['Menunggu','Revisi','Disetujui','Batal'], true)) $query->where('status', $status);
        $pengajuan = $query->paginate(20)->withQueryString();
        $counts = DB::table('pengajuan_sewa')->select('status', DB::raw('COUNT(*) as jumlah'))->groupBy('status')->pluck('jumlah', 'status');
        return view('admin.pengajuan.index', compact('pengajuan', 'counts', 'status'));
    }

    public function show(string $kode)
    {
        $pengajuan = DB::table('pengajuan_sewa')->where('kode', $kode)->first();
        abort_unless($pengajuan, 404);
        $detail = DB::table('pengajuan_detail')->where('id_pengajuan', $pengajuan->id_pengajuan)->orderBy('id_pengajuan_detail')->get();
        $barang = DB::table('barang')->orderBy('nama_barang')->get();
        $kebutuhan = json_decode($pengajuan->kebutuhan_json, true) ?: [];
        $pelangganTertaut = $pengajuan->id_pelanggan ? DB::table('pelanggan')->where('id_pelanggan',$pengajuan->id_pelanggan)->first() : null;
        return view('admin.pengajuan.show', compact('pengajuan', 'detail', 'barang', 'kebutuhan', 'pelangganTertaut'));
    }

    public function simpan(Request $request, string $kode, PengajuanService $service)
    {
        $data = $request->validate([
            'aksi' => 'required|in:revisi',
            'tanggal_sewa' => 'nullable|date',
            'tanggal_kembali' => 'nullable|date|after_or_equal:tanggal_sewa',
            'harga_final' => 'nullable|integer|min:1|max:1000000000',
            'biaya_genset' => 'nullable|integer|min:0|max:1000000000',
            'catatan_admin' => 'nullable|string|max:2000',
            'items' => 'nullable|array',
            'items_baru' => 'nullable|array|max:30',
            'items_baru.*.tipe_item' => 'required|in:Barang,External',
            'items_baru.*.id_barang' => 'nullable|integer|exists:barang,id_barang',
            'items_baru.*.nama_barang' => 'required|string|max:150',
            'items_baru.*.sumber_external' => 'nullable|string|max:150',
            'items_baru.*.qty' => 'required|integer|min:1|max:10000',
            'items.*.tipe_item' => 'required|in:Barang,External',
            'items.*.id_barang' => 'nullable|integer|exists:barang,id_barang',
            'items.*.nama_barang' => 'required|string|max:150',
            'items.*.sumber_external' => 'nullable|string|max:150',
            'items.*.qty' => 'required|integer|min:1|max:10000',
        ]);
        try {
            $id = $service->simpanDanProses($kode, $data, (int)$request->user()->id_user, false);
            return $id
                ? redirect('/admin/transaksi/detail?id='.$id)->with('info', 'Pengajuan telah disetujui dan menjadi transaksi Booking #'.$id)
                : redirect()->route('pengajuan.admin.show', ['kode'=>$kode])->with('info', 'Perubahan tersimpan. Status pengajuan: Revisi.');
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Pengajuan belum diproses: '.$e->getMessage());
        }
    }

    public function batalkan(Request $request, string $kode, PengajuanService $service)
    {
        $data = $request->validate(['catatan_admin' => 'nullable|string|max:2000']);
        try {
            $service->batalkan($kode, (int)$request->user()->id_user, $data['catatan_admin'] ?? null);
            return redirect()->route('pengajuan.admin.index')->with('info', 'Pengajuan dibatalkan, tanpa mengubah stok.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
