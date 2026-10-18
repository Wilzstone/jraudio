<?php
namespace App\Http\Controllers;
use App\Repositories\Legacy\KaryawanModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class KaryawanController extends Controller
{
    private function valid(Request $request,bool $new): array {
        return $request->validate([
            'username'=>['required','string','max:100',Rule::unique('users','username')->ignore($new?null:$request->input('id_user'),'id_user')],
            'password'=>[$new?'required':'nullable','string','min:6'],
            'role'=>['required',Rule::in(['Owner','Karyawan'])],
            'nama_karyawan'=>'required|string|max:150','no_hp'=>'nullable|string|max:25',
            'alamat'=>'nullable|string|max:255','jabatan'=>'nullable|string|max:100'
        ]);
    }
    public function index(KaryawanModel $model) { $karyawan=$model->getAll();return view('karyawan.index',compact('karyawan')); }
    public function create() {return view('karyawan.create');}
    public function edit(Request $request,KaryawanModel $model) {
        $data=$model->getById($request->integer('id'));return $data?view('karyawan.edit',compact('data')):redirect('/admin/karyawan')->with('info','Karyawan tidak ditemukan');
    }
    public function store(Request $request,KaryawanModel $model) {
        $data=$this->valid($request,true);$data['password']=Hash::make($data['password']);
        $model->create($data);return redirect('/admin/karyawan')->with('info','Karyawan disimpan');
    }
    public function update(Request $request,KaryawanModel $model) {
        $request->validate(['id_karyawan'=>'required|integer|exists:karyawan,id_karyawan']);
        $old=$model->getById($request->integer('id_karyawan'));
        // id_user diperoleh dari database, bukan dipercaya dari input browser.
        $request->merge(['id_user'=>$old['id_user']]);
        $data=$this->valid($request,false);$data['id_user']=$old['id_user'];
        $data['password']=!empty($data['password'])?Hash::make($data['password']):null;
        $model->update($request->integer('id_karyawan'),$data);
        return redirect('/admin/karyawan')->with('info','Karyawan diperbarui');
    }
    public function delete(Request $request,KaryawanModel $model) {
        $id=$request->integer('id');
        if ($model->dipakaiDiTransaksi($id)) return back()->with('info','Karyawan sudah digunakan transaksi');
        $data=$model->getById($id);
        if ($data && (int)$data['id_user'] === (int)$request->user()->getAuthIdentifier()) return back()->with('info','Tidak dapat menghapus akun yang sedang digunakan');
        if ($data) $model->delete($id);
        return redirect('/admin/karyawan')->with('info','Karyawan dihapus');
    }
}
