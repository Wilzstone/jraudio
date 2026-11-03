<?php
namespace App\Http\Controllers;
use App\Repositories\Legacy\{TransaksiModel,PelangganModel,BarangModel,KaryawanModel,PembayaranModel,PaketModel};
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Support\Facades\DB;
use App\Services\PengajuanService;
use App\Services\PelangganNomorService;
use App\Services\PembayaranWorkflowService;
use App\Services\MitraCatalogService;
class TransaksiController extends Controller
{
    public function index(TransaksiModel $model) {$transaksi=$model->getAll();return view('transaksi.index',compact('transaksi'));}
    public function create(Request $request) {
        $pelanggan=(new PelangganModel)->getAll();$barang=(new BarangModel)->getTersedia();
        $paketModel=new PaketModel;$paket=$paketModel->getAktif();$paketDetail=[];
        foreach($paket as $p) $paketDetail[$p['id_paket']]=$paketModel->getDetail((int)$p['id_paket']);
        $old=$request->session()->get('old_input_transaksi',[]);
        $pengajuan = null; $prefillItems = [];
        if ($request->filled('pengajuan')) {
            // Jangan menimpa paket/jenis dari pengajuan lain dengan old input yang tersisa.
            if (($old['kode_pengajuan'] ?? null) !== $request->query('pengajuan')) $old = [];
            $barang=(new BarangModel)->getAll();
            $pengajuan = DB::table('pengajuan_sewa')->where('kode', $request->query('pengajuan'))->first();
            abort_unless($pengajuan,404);
            abort_unless(in_array($pengajuan->status,['Menunggu','Revisi'],true),409);
            if (!$pengajuan->id_pelanggan) {
                $idPelanggan = DB::transaction(function () use ($pengajuan) {
                    $id = PelangganNomorService::cariAtauBuat($pengajuan->nama_pelanggan, $pengajuan->no_hp);
                    DB::table('pengajuan_sewa')->where('id_pengajuan', $pengajuan->id_pengajuan)->update(['id_pelanggan'=>$id]);
                    return $id;
                });
                $pengajuan->id_pelanggan = $idPelanggan;
                $pelanggan = (new PelangganModel)->getAll();
            }
            $prefillItems = DB::table('pengajuan_detail')->where('id_pengajuan',$pengajuan->id_pengajuan)
                ->orderBy('id_pengajuan_detail')->get()->map(fn($r) => [
                    'tipe_item'=>$r->tipe_item, 'id_barang'=>$r->id_barang,
                    'nama_item_external'=>$r->nama_barang, 'sumber_external'=>$r->sumber_external,
                    'qty'=>$r->qty, 'id_barang_mitra'=>$r->id_barang_mitra ?? null, 'harga_satuan'=>0, 'harga_mitra'=>null,'termasuk_paket'=>(bool)($r->termasuk_paket ?? false),
                ])->all();
            $pilihanCrew = $pengajuan->pilihan_crew ?: ($pengajuan->rekomendasi_paket === 'custom' ? 'satuan' : $pengajuan->rekomendasi_paket);
            $namaPaket = match($pilihanCrew) {
                'paket2500'=>'Paket 2500','paket5000'=>'Paket 5000',default=>null
            };
            $p = $namaPaket ? collect($paket)->first(fn($row) => $row['nama_paket'] === $namaPaket) : null;
            $old = array_merge([
                'id_pelanggan' => $pengajuan->id_pelanggan,
                'jenis_transaksi'=>$pilihanCrew === 'satuan' ? 'Satuan' : 'Paketan', 'id_paket'=>$p['id_paket'] ?? '',
                'harga_paket_manual'=>$pilihanCrew === 'satuan' ? 0 : max(0,(int)($pengajuan->harga_final ?? $pengajuan->estimasi_min ?? 0) - (int)$pengajuan->biaya_genset),
                'biaya_genset'=>$pengajuan->biaya_genset,
                'tanggal_sewa'=>max($pengajuan->tanggal_acara,\Carbon\Carbon::today('Asia/Jakarta')->toDateString()),
                'tanggal_kembali'=>max($pengajuan->tanggal_acara,\Carbon\Carbon::today('Asia/Jakarta')->toDateString()),
            ], $old);
        }
        $barangMitra=MitraCatalogService::pilihan();
        return view('transaksi.create',compact('pelanggan','barang','paket','paketDetail','old','pengajuan','prefillItems','barangMitra'));
    }
    public function store(Request $request,TransaksiModel $model) {
        $request->validate([
            'id_pelanggan'=>'required|integer|exists:pelanggan,id_pelanggan',
            'jenis_transaksi'=>'required|in:Satuan,Paketan',
            'tanggal_sewa'=>'required|date','tanggal_kembali'=>'required|date|after_or_equal:tanggal_sewa',
            'status_transaksi'=>'required|in:Booking',
            'id_paket'=>'nullable|integer','harga_paket_manual'=>'nullable|integer|min:0',
            'biaya_genset'=>'nullable|integer|min:0',
            'qty'=>'required|array|min:1','qty.*'=>'required|integer|min:1',
            'harga_manual_item.*'=>'nullable|integer|min:0',
            'harga_mitra_item'=>'nullable|array',
            'termasuk_paket_item'=>'nullable|array', 'termasuk_paket_item.*'=>'nullable|in:0,1',
            'id_barang_mitra'=>'nullable|array', 'id_barang_mitra.*'=>'nullable|integer|exists:barang_mitra,id_barang_mitra',
            'harga_mitra_item.*'=>'nullable|integer|min:0',
            'alamat_lokasi_acara'=>'nullable|string|max:2000',
            'link_google_maps'=>'nullable|url|starts_with:https://|max:2048',
            'kode_pengajuan'=>'nullable|string|max:32'
        ]);
        try {
            $link = trim((string)$request->input('link_google_maps',''));
            if ($link !== '') {
                $host = strtolower((string)parse_url($link, PHP_URL_HOST));
                $allowed = ['maps.app.goo.gl','maps.google.com','www.google.com','google.com','goo.gl'];
                if (!in_array($host,$allowed,true)) throw new \RuntimeException('Shareloc harus berupa link Google Maps yang valid.');
            }
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
            $sources=$request->input('sumber_external',[]);$qty=$request->input('qty',[]);$harga=$request->input('harga_manual_item',[]);$hargaMitra=$request->input('harga_mitra_item',[]);$mitraIds=$request->input('id_barang_mitra',[]);$included=$request->input('termasuk_paket_item',[]);
            foreach($qty as $i=>$n) $items[]=[
                'tipe_item'=>$types[$i]??'Barang','id_barang'=>(int)($ids[$i]??0),
                'nama_item_external'=>$names[$i]??'','sumber_external'=>$sources[$i]??'',
                'qty'=>(int)$n,'harga_satuan'=>(int)($harga[$i]??0),
                'id_barang_mitra'=>($types[$i]??'Barang')==='External' && !empty($mitraIds[$i]) ? (int)$mitraIds[$i] : null,
                'termasuk_paket'=>($types[$i]??'Barang')==='External' && !empty($included[$i]),
                'harga_mitra'=>array_key_exists($i,$hargaMitra) && $hargaMitra[$i]!=='' ? (int)$hargaMitra[$i] : null
            ];
            if ($request->filled('kode_pengajuan')) {
                $kode = (string)$request->input('kode_pengajuan');
                $p = DB::table('pengajuan_sewa')->where('kode',$kode)->first();
                if (!$p) throw new \RuntimeException('Pengajuan tidak ditemukan.');
                // Jika pengajuan, ID pelanggan harus berasal dari pengajuan, bukan input browser.
                if ($p->id_pelanggan && (int)$p->id_pelanggan !== $request->integer('id_pelanggan'))
                    throw new \RuntimeException('Pelanggan tidak sesuai pengajuan.');
                $id = app(PengajuanService::class)->konfirmasiFormTransaksi($kode, [
                    'jenis_transaksi'=>$jenis,
                    'id_pelanggan'=>$request->integer('id_pelanggan'),
                    'tanggal_sewa'=>$request->input('tanggal_sewa'),
                    'tanggal_kembali'=>$request->input('tanggal_kembali'),
                    'harga_paket_manual'=>$request->integer('harga_paket_manual'),
                    'biaya_genset'=>$request->integer('biaya_genset'),
                    'alamat_lokasi_acara'=>$request->input('alamat_lokasi_acara'),
                    'link_google_maps'=>$request->input('link_google_maps'),
                    'form_items'=>array_map(fn($item)=>[
                        'tipe_item'=>$item['tipe_item'], 'id_barang'=>$item['id_barang'],
                        'nama_barang'=>$item['nama_item_external'],
                        'sumber_external'=>$item['sumber_external'], 'qty'=>$item['qty'],
                        'harga_satuan'=>$item['harga_satuan'], 'harga_mitra'=>$item['harga_mitra'],
                        'id_barang_mitra'=>$item['id_barang_mitra'] ?? null, 'termasuk_paket'=>$item['termasuk_paket'] ?? false,
                    ], $items),
                ], (int)$request->user()->id_user);
                return redirect('/admin/transaksi/detail?id='.$id)->with('info','Pengajuan disetujui. Transaksi Booking #'.$id.' berhasil dibuat.');
            }
            $id=$model->create([
                'id_pelanggan'=>$request->integer('id_pelanggan'),'id_karyawan'=>(int)$karyawan['id_karyawan'],
                'jenis_transaksi'=>$jenis,'id_paket'=>$idPaket,'nama_paket'=>$namaPaket,
                'harga_paket_manual'=>$request->integer('harga_paket_manual'),
                'biaya_genset'=>$request->integer('biaya_genset'),
                'tanggal_transaksi'=>\Carbon\Carbon::today('Asia/Jakarta')->toDateString(),'tanggal_sewa'=>$request->input('tanggal_sewa'),
                'tanggal_kembali'=>$request->input('tanggal_kembali'),
                'status_transaksi'=>'Booking',
                'alamat_lokasi_acara'=>$request->input('alamat_lokasi_acara'),
                'link_google_maps'=>$request->input('link_google_maps')
            ],$items);
            return redirect('/admin/transaksi/detail?id='.$id)->with('info','Transaksi berhasil dibuat');
        } catch(Throwable $e) {
            return redirect('/admin/transaksi/tambah'.($request->filled('kode_pengajuan')?'?pengajuan='.urlencode((string)$request->input('kode_pengajuan')):''))->withInput()->with('old_input_transaksi',$request->except('_token'))
                ->with('info','Transaksi gagal: '.$e->getMessage());
        }
    }
    public function show(Request $request,TransaksiModel $model) {
        $id=$request->integer('id');$data=$model->getById($id);
        if (!$data) return redirect('/admin/transaksi')->with('info','Transaksi tidak ditemukan');
        $detail=$model->getDetail($id);
        $pm=new PembayaranModel;
        $pembayaran=$pm->getByTransaksi($id);
        $ringkasan=app(PembayaranWorkflowService::class)->ringkasan($id);
        $totalDenda=$ringkasan['total_denda'];
        $totalBayar=$ringkasan['dibayar_sewa']+$ringkasan['dibayar_denda'];
        $totalTagihan=(int)$data['total_harga']+$totalDenda;
        $sisaBayar=$ringkasan['sisa_total'];
        $isSelesai=$data['status_transaksi']==='Selesai';
        return view('transaksi.show',compact('data','detail','pembayaran','totalBayar','totalDenda','totalTagihan','sisaBayar','isSelesai','ringkasan'));
    }
    /** Pengembalian ke mitra tidak menyentuh inventaris JR Audio. */
    public function mitraKembali(Request $request) {
        $data=$request->validate(['id_detail'=>'required|integer|exists:detail_transaksi,id_detail']);
        try {
            DB::transaction(function () use ($data) {
                $d=DB::table('detail_transaksi as d')->join('transaksi as t','t.id_transaksi','=','d.id_transaksi')
                    ->where('d.id_detail',$data['id_detail'])->select('d.*','t.status_transaksi')->lockForUpdate()->first();
                if(!$d || $d->tipe_item!=='External' || !$d->id_barang_mitra) throw new \RuntimeException('Barang ini bukan barang katalog mitra.');
                if($d->status_transaksi!=='Selesai') throw new \RuntimeException('Selesaikan proses pengembalian pelanggan terlebih dahulu.');
                if(!$d->dikembalikan_ke_mitra_at) DB::table('detail_transaksi')->where('id_detail',$d->id_detail)->whereNull('dikembalikan_ke_mitra_at')
                    ->update(['dikembalikan_ke_mitra_at'=>now()]);
            });
            return back()->with('info','Pengembalian ke mitra berhasil dicatat.');
        } catch(Throwable $e) { return back()->with('error',$e->getMessage()); }
    }
    public function status(Request $request,TransaksiModel $model) {
        $data=$request->validate(['id_transaksi'=>'required|integer|exists:transaksi,id_transaksi','status_transaksi'=>'required|in:Berjalan,Batal']);
        try {$model->updateStatus((int)$data['id_transaksi'],$data['status_transaksi']);
        return redirect('/admin/transaksi/detail?id='.$data['id_transaksi'])->with('info','Status diubah');
        } catch(Throwable $e) {return back()->with('info',$e->getMessage());}
    }
}
