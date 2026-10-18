<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Paket extends Model
{
    protected $table = 'paket';
    protected $primaryKey = 'id_paket';
    public $timestamps = false;
    protected $fillable = ['nama_paket','deskripsi','range_harga','gambar','status'];
}
