<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Organisasi
 * 
 * @property int $id_organisasi
 * @property string $nama_organisasi
 * @property string $alamat
 * @property string $email
 * @property string $no_telp
 * @property string $password
 * 
 * @property Collection|Donasi[] $donasis
 * @property Collection|RequestDonasi[] $request_donasis
 *
 * @package App\Models
 */
class Organisasi extends Model
{
	protected $table = 'organisasis';
	protected $primaryKey = 'id_organisasi';
	public $timestamps = false;

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'nama_organisasi',
		'alamat',
		'email',
		'no_telp',
		'password'
	];

	public function donasis()
	{
		return $this->hasMany(Donasi::class, 'id_organisasi');
	}

	public function request_donasis()
	{
		return $this->hasMany(RequestDonasi::class, 'id_organisasi');
	}
}
