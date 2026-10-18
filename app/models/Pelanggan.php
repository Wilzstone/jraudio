<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $timestamps = false;
    protected $fillable = ['nama_pelanggan','no_hp','alamat'];
}
