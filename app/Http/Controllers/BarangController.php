<?php
namespace App\Http\Controllers;
use App\Repositories\Legacy\BarangModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BarangController extends Controller
{
    public function index(BarangModel $model) { $barang = $model->getAll(); return view('barang.index', compact('barang')); }
    public function create() { return view('barang.create'); }
    public function edit(Request $request, BarangModel $model) {
        $data = $model->getById($request->integer('id'));
        return $data ? view('barang.edit', compact('data')) : redirect('/admin/barang')->with('info','Barang tidak ditemukan');
    }
    private function valid(Request $request): array {
        return $request->validate([
            'nama_barang'=>'required|string|max:180','kategori'=>'required|string|max:100',
            'harga_sewa'=>'required|integer|min:0','stok'=>'required|integer|min:0',
            'stok_maintenance'=>'required|integer|min:0',
            'gambar'=>'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096'
        ]);
    }
    private function gambar(Request $request, ?string $lama): ?string {
        if (!$request->hasFile('gambar')) return $lama;
        $file = $request->file('gambar');
        $nama = time().'_'.bin2hex(random_bytes(6)).'.'.$file->extension();
        $file->move(public_path('assets/img/barang'), $nama);
        if ($lama && is_file(public_path('assets/img/barang/'.$lama))) @unlink(public_path('assets/img/barang/'.$lama));
        return $nama;
    }
    public function store(Request $request, BarangModel $model) {
        $data=$this->valid($request);$data['gambar']=$this->gambar($request,null);
        $model->create($data);
        return redirect('/admin/barang')->with('info','Barang berhasil disimpan');
    }
    public function update(Request $request, BarangModel $model) {
        $request->validate(['id_barang'=>'required|integer|exists:barang,id_barang']);
        $data=$this->valid($request);$lama=$model->getById($request->integer('id_barang'));
        $data['gambar']=$this->gambar($request,$lama['gambar']??null);
        $model->update($request->integer('id_barang'),$data);
        return redirect('/admin/barang')->with('info','Barang berhasil diperbarui');
    }
    public function delete(Request $request, BarangModel $model) {
        $id=$request->integer('id');
        if ($model->dipakaiDiTransaksi($id)) return back()->with('info','Barang sudah dipakai dalam transaksi');
        $data=$model->getById($id);
        if ($data) {
            $model->delete($id);
            $gambar=$data['gambar']??null;
            if ($gambar && is_file(public_path('assets/img/barang/'.$gambar))) @unlink(public_path('assets/img/barang/'.$gambar));
        }
        return redirect('/admin/barang')->with('info','Barang berhasil dihapus');
    }
}
