<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PengembalianDetail extends Model
{
    protected $table = 'pengembalian_detail';
    protected $primaryKey = 'id_pengembalian_detail';
    public $timestamps = false;
    protected $fillable = ['id_pengembalian','id_detail','id_barang','qty_kembali_baik','qty_rusak'];
}
