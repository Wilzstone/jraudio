<?php
namespace App\Http\Controllers;
use App\Repositories\Legacy\PelangganModel;
use Illuminate\Http\Request;
class PelangganController extends Controller
{
    private function valid(Request $request): array { return $request->validate(['nama_pelanggan'=>'required|string|max:150','no_hp'=>'nullable|string|max:25','alamat'=>'nullable|string|max:255']); }
    public function index(PelangganModel $model) { $pelanggan=$model->getAll();return view('pelanggan.index',compact('pelanggan')); }
    public function create() {return view('pelanggan.create');}
    public function edit(Request $request,PelangganModel $model) {
        $data=$model->getById($request->integer('id'));return $data?view('pelanggan.edit',compact('data')):redirect('/admin/pelanggan')->with('info','Pelanggan tidak ditemukan');
    }
    public function store(Request $request,PelangganModel $model) {
        $model->create($this->valid($request));return redirect('/admin/pelanggan')->with('info','Pelanggan disimpan');
    }
    public function update(Request $request,PelangganModel $model) {
        $request->validate(['id_pelanggan'=>'required|integer|exists:pelanggan,id_pelanggan']);
        $model->update($request->integer('id_pelanggan'),$this->valid($request));return redirect('/admin/pelanggan')->with('info','Pelanggan diperbarui');
    }
    public function delete(Request $request,PelangganModel $model) {
        $id=$request->integer('id');
        if ($model->dipakaiDiTransaksi($id)) return back()->with('info','Pelanggan sudah digunakan transaksi');
        if ($model->getById($id)) $model->delete($id);
        return redirect('/admin/pelanggan')->with('info','Pelanggan dihapus');
    }
}
