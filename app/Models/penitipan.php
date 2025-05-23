<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Penitipan
 * 
 * @property int $id_penitipan
 * @property int $id_penitip
 * @property Carbon $tanggal_penitipan
 * @property Carbon $batas_penitipan
 * 
 * @property Penitip $penitip
 * @property Collection|Barang[] $barangs
 *
 * @package App\Models
 */
class Penitipan extends Model
{
	protected $table = 'penitipans';
	protected $primaryKey = 'id_penitipan';
	public $timestamps = false;

	protected $casts = [
		'id_penitip' => 'int',
		'id_pegawai' => 'int',
		'tanggal_penitipan' => 'datetime',
		'batas_penitipan' => 'datetime'
	];

	protected $fillable = [
		'id_penitip',
		'id_pegawai',
		'tanggal_penitipan',
		'batas_penitipan',
	];

	public function penitip()
	{
		return $this->belongsTo(Penitip::class, 'id_penitip');
	}

	public function barangs()
	{
		return $this->hasMany(Barang::class, 'id_penitipan');
	}
	public function pegawai()
	{
		return $this->belongsTo(Pegawai::class, 'id_pegawai');
	}
}
