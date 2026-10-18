<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $timestamps = false;
    protected $fillable = ['username','password','role'];
    protected $hidden = ['password'];
    public function karyawan() { return $this->hasOne(Karyawan::class, 'id_user', 'id_user'); }
}
