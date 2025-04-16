<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Penitip
 * 
 * @property int $id_penitip
 * @property string $nama_penitip
 * @property string $email
 * @property string $password
 * @property string $no_telp
 * @property string $nik
 * @property float $saldo
 * @property int $poin
 * @property int $akumulasi_rating
 * 
 * @property Collection|Pengambilan[] $pengambilans
 * @property Collection|Penitipan[] $penitipans
 *
 * @package App\Models
 */
class Penitip extends Model
{
	protected $table = 'penitips';
	protected $primaryKey = 'id_penitip';
	public $timestamps = false;

	protected $casts = [
		'saldo' => 'float',
		'poin' => 'int',
		'akumulasi_rating' => 'int'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'nama_penitip',
		'email',
		'password',
		'no_telp',
		'nik',
		'saldo',
		'poin',
		'akumulasi_rating'
	];

	public function pengambilans()
	{
		return $this->hasMany(Pengambilan::class, 'id_penitip');
	}

	public function penitipans()
	{
		return $this->hasMany(Penitipan::class, 'id_penitip');
	}
}
