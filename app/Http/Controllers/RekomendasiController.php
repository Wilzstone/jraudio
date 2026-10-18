<?php
namespace App\Http\Controllers;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class RekomendasiController extends Controller
{
    public function index() {return view('rekomendasi.form');}
    public function proses(Request $request, RecommendationService $engine) {
        $input=$request->validate([
            'jenis_acara'=>['required',Rule::in(['seminar','pernikahan','konser','sekolah','lainnya'])],
            'jumlah_peserta'=>'required|integer|min:1|max:20000',
            'lokasi'=>'required|in:indoor,outdoor',
            'kegiatan'=>'required|in:pidato,musik_live,musik_rekaman,umum',
            'anggaran'=>'required|integer|min:0|max:1000000000',
            'tanggal_acara'=>'required|date|after_or_equal:today',
            'butuh_genset'=>'nullable|boolean'
        ]);
        $input['butuh_genset']=$request->boolean('butuh_genset');
        $hasil=$engine->rekomendasikan($input);
        return view('rekomendasi.hasil',compact('input','hasil'));
    }
}
