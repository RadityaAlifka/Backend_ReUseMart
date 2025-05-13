<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Pengiriman
 * 
 * @property int $id_pengiriman
 * @property int $id_transaksi
 * @property int $id_pegawai
 * @property Carbon $tanggal_pengiriman
 * @property string $status_pengiriman
 * @property float $ongkir
 * 
 * @property Pegawai $pegawai
 * @property Transaksi $transaksi
 *
 * @package App\Models
 */
class Pengiriman extends Model
{
	protected $table = 'pengirimans';
	protected $primaryKey = 'id_pengiriman';
	public $timestamps = false;

	protected $casts = [
		'id_transaksi' => 'int',
		'id_pegawai' => 'int',
		'tanggal_pengiriman' => 'datetime',
		'ongkir' => 'float'
	];

	protected $fillable = [
		'id_transaksi',
		'id_pegawai',
		'tanggal_pengiriman',
		'status_pengiriman',
		'ongkir'
	];

	public function pegawai()
	{
		return $this->belongsTo(Pegawai::class, 'id_pegawai');
	}

	public function transaksi()
	{
		return $this->belongsTo(Transaksi::class, 'id_transaksi');
	}
}
