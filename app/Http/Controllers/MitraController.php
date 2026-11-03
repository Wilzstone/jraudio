<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MitraController extends Controller {
    public function index() {
        $mitra=DB::table('mitra')->orderBy('nama_mitra')->get();
        $barang=DB::table('barang_mitra as b')->join('mitra as m','m.id_mitra','=','b.id_mitra')
            ->select('b.*','m.nama_mitra')->orderBy('m.nama_mitra')->orderBy('b.nama_barang')->get();
        return view('mitra.index',compact('mitra','barang'));
    }
    private function dataMitra(Request $r): array {
        return $r->validate([
            'nama_mitra'=>'required|string|max:150','nama_kontak'=>'nullable|string|max:120',
            'no_hp'=>'nullable|string|max:30','alamat'=>'nullable|string|max:2000',
            'catatan'=>'nullable|string|max:2000','status'=>'required|in:Aktif,Nonaktif'
        ]);
    }
    public function simpanMitra(Request $r) {
        $data=$this->dataMitra($r);DB::table('mitra')->insert($data+['created_at'=>now(),'updated_at'=>now()]);
        return back()->with('info','Mitra berhasil ditambahkan.');
    }
    public function updateMitra(Request $r,int $id) {
        $data=$this->dataMitra($r);$updated=DB::table('mitra')->where('id_mitra',$id)->update($data+['updated_at'=>now()]);
        return back()->with('info','Informasi mitra diperbarui.');
    }
    private function dataBarang(Request $r): array {
        return $r->validate([
            'id_mitra'=>'required|integer|exists:mitra,id_mitra',
            'nama_barang'=>'required|string|max:160','satuan'=>'required|string|max:30',
            'harga_mitra'=>'required|integer|min:0|max:10000000000',
            'harga_pelanggan'=>'required|integer|min:0|max:10000000000',
            'status'=>'required|in:Aktif,Nonaktif','catatan'=>'nullable|string|max:2000'
        ]);
    }
    public function simpanBarang(Request $r) {
        $data=$this->dataBarang($r);
        DB::table('barang_mitra')->insert($data+['created_at'=>now(),'updated_at'=>now()]);
        return back()->with('info','Barang mitra ditambahkan. Tarif bisa disesuaikan pada transaksi.');
    }
    public function updateBarang(Request $r,int $id) {
        $data=$this->dataBarang($r);
        DB::table('barang_mitra')->where('id_barang_mitra',$id)->update($data+['updated_at'=>now()]);
        return back()->with('info','Barang mitra diperbarui. Transaksi lama tidak berubah.');
    }
}
