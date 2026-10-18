<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    public $timestamps = false;
    protected $fillable = ['id_pelanggan','id_karyawan','jenis_transaksi','id_paket','nama_paket','harga_paket_manual','biaya_genset','tanggal_transaksi','tanggal_sewa','tanggal_kembali','status_transaksi','total_harga'];
}
