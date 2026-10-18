<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaketDetail extends Model
{
    protected $table = 'paket_detail';
    protected $primaryKey = 'id_paket_detail';
    public $timestamps = false;
    protected $fillable = ['id_paket','id_barang','qty','keterangan'];
}
