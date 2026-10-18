<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Pengembalian extends Model
{
    protected $table = 'pengembalian';
    protected $primaryKey = 'id_pengembalian';
    public $timestamps = false;
    protected $fillable = ['id_transaksi','tanggal_pengembalian','kondisi_barang','denda','denda_manual','catatan'];
}
