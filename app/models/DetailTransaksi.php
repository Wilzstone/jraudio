<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail';
    public $timestamps = false;
    protected $fillable = ['id_transaksi','id_barang','nama_item_external','sumber_external','id_barang_mitra','harga_mitra','termasuk_paket','dikembalikan_ke_mitra_at','qty','harga_satuan','subtotal','tipe_item'];
}
