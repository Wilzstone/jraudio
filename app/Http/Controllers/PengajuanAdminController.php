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
        $pilihanCrew = $pengajuan->pilihan_crew ?: ($pengajuan->rekomendasi_paket === 'custom' ? 'satuan' : $pengajuan->rekomendasi_paket);
        $presetPaket = [];
        foreach (DB::table('paket')->whereIn('nama_paket', ['Paket 2500', 'Paket 5000'])->where('status', 'Aktif')->get() as $paket) {
            $kodePaket = $paket->nama_paket === 'Paket 2500' ? 'paket2500' : 'paket5000';
            $presetPaket[$kodePaket] = DB::table('paket_detail as pd')
                ->join('barang as b', 'b.id_barang', '=', 'pd.id_barang')
                ->where('pd.id_paket', $paket->id_paket)
                ->orderBy('pd.id_paket_detail')
                ->get(['pd.id_barang','b.nama_barang','pd.qty'])
                ->map(fn ($r) => [
                    'tipe_item' => 'Barang', 'id_barang' => (int)$r->id_barang,
                    'nama_barang' => $r->nama_barang, 'qty' => (int)$r->qty,
                    'sumber_external' => '',
                ])->all();
        }
        $initialItems = $detail->map(fn ($r) => [
            'tipe_item' => $r->tipe_item, 'id_barang' => $r->id_barang,
            'nama_barang' => $r->nama_barang, 'qty' => (int)$r->qty,
            'sumber_external' => $r->sumber_external ?? '',
        ])->all();
        $inventoryOptions = $barang->map(fn ($b) => ['id_barang' => $b->id_barang, 'nama_barang' => $b->nama_barang, 'stok' => $b->stok])->values()->all();
        $formItemsClient = old('form_items', $initialItems);
        return view('admin.pengajuan.show', compact('pengajuan', 'detail', 'barang', 'kebutuhan', 'pelangganTertaut', 'pilihanCrew', 'presetPaket', 'initialItems', 'inventoryOptions', 'formItemsClient'));
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
            'pilihan_crew' => 'required|in:paket2500,paket5000,satuan',
            'form_items' => 'required|array|min:1|max:60',
            'form_items.*.tipe_item' => 'required|in:Barang,External',
            'form_items.*.id_barang' => 'nullable|integer|exists:barang,id_barang',
            'form_items.*.nama_barang' => 'required|string|max:150',
            'form_items.*.sumber_external' => 'nullable|string|max:150',
            'form_items.*.qty' => 'required|integer|min:1|max:10000',
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
