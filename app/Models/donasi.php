<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Donasi
 * 
 * @property int $id_donasi
 * @property int $id_organisasi
 * @property Carbon $tanggal_donasi
 * @property string $nama_penerima
 * 
 * @property Organisasi $organisasi
 * @property Collection|Barang[] $barangs
 *
 * @package App\Models
 */
class Donasi extends Model
{
	protected $table = 'donasis';
	protected $primaryKey = 'id_donasi';
	public $timestamps = false;

	protected $casts = [
		'id_organisasi' => 'int',
		'tanggal_donasi' => 'datetime'
	];

	protected $fillable = [
		'id_organisasi',
		'tanggal_donasi',
		'nama_penerima'
	];

	public function organisasi()
	{
		return $this->belongsTo(Organisasi::class, 'id_organisasi');
	}

	public function barangs()
	{
		return $this->hasMany(Barang::class, 'id_donasi');
	}
}
