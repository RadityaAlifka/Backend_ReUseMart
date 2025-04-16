<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Pegawai
 * 
 * @property int $id_pegawai
 * @property int $id_jabatan
 * @property string $nama_pegawai
 * @property string $email
 * @property string $no_telp
 * @property string $password
 * @property float $komisi
 * 
 * @property Jabatan $jabatan
 * @property Diskusi|null $diskusi
 * @property Collection|Merchandise[] $merchandises
 * @property Collection|Pengiriman[] $pengirimen
 * @property Collection|RequestDonasi[] $request_donasis
 *
 * @package App\Models
 */
class Pegawai extends Model
{
	protected $table = 'pegawais';
	protected $primaryKey = 'id_pegawai';
	public $timestamps = false;

	protected $casts = [
		'id_jabatan' => 'int',
		'komisi' => 'float'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'id_jabatan',
		'nama_pegawai',
		'email',
		'no_telp',
		'password',
		'komisi'
	];

	public function jabatan()
	{
		return $this->belongsTo(Jabatan::class, 'id_jabatan');
	}

	public function diskusi()
	{
		return $this->hasOne(Diskusi::class, 'id_pegawai');
	}

	public function merchandises()
	{
		return $this->hasMany(Merchandise::class, 'id_pegawai');
	}

	public function pengirimen()
	{
		return $this->hasMany(Pengiriman::class, 'id_pegawai');
	}

	public function request_donasis()
	{
		return $this->hasMany(RequestDonasi::class, 'id_pegawai');
	}
}
