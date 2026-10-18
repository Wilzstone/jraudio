<?php
namespace App\Http\Controllers;
use App\Models\Barang;
class PublicController extends Controller
{
    public function index() { $barang = $this->daftarBarang(); return view('public.index', compact('barang')); }
    public function barang() { $barang = $this->daftarBarang(); return view('public.barang', compact('barang')); }
    private function daftarBarang(): array {
        return Barang::where('stok', '>', 0)->orderBy('nama_barang')->get()->toArray();
    }
    public function tentang() { return view('public.tentang'); }
    public function lokasi() { return view('public.lokasi'); }
    public function kontak() { return view('public.kontak'); }
    public function paket() { return view('public.paket'); }
    public function galeri() { return view('public.galeri'); }
    public function partner() { return view('public.partner'); }
}
