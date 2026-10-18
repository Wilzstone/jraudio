<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id_barang';
    public $timestamps = false;
    protected $fillable = ['nama_barang','kategori','harga_sewa','stok','stok_disewa','stok_maintenance','status','gambar'];
}
