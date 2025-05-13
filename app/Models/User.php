<?php
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['email', 'password', 'level'];

    public function penjual()
    {
        return $this->hasOne(Penjual::class);
    }

    public function pembeli()
    {
        return $this->hasOne(Pembeli::class);
    }

    public function pegawai()
    {
        return $this->hasOne(Pegawai::class);
    }
    public function organisasi()
    {
        return $this->hasOne(Organisasi::class);
    }
}