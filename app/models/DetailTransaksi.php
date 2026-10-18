<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail';
    public $timestamps = false;
    protected $fillable = ['id_transaksi','id_barang','nama_item_external','sumber_external','qty','harga_satuan','subtotal','tipe_item'];
}
