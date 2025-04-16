<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Pengambilan
 * 
 * @property int $id_pengambilan
 * @property int $id_transaksi
 * @property int $id_penitip
 * @property int $id_pembeli
 * @property Carbon $tanggal_pengambilan
 * @property Carbon $batas_pengambilan
 * @property string $status_pengambilan
 * 
 * @property Pembeli $pembeli
 * @property Penitip $penitip
 * @property Transaksi $transaksi
 *
 * @package App\Models
 */
class Pengambilan extends Model
{
	protected $table = 'pengambilans';
	protected $primaryKey = 'id_pengambilan';
	public $timestamps = false;

	protected $casts = [
		'id_transaksi' => 'int',
		'id_penitip' => 'int',
		'id_pembeli' => 'int',
		'tanggal_pengambilan' => 'datetime',
		'batas_pengambilan' => 'datetime'
	];

	protected $fillable = [
		'id_transaksi',
		'id_penitip',
		'id_pembeli',
		'tanggal_pengambilan',
		'batas_pengambilan',
		'status_pengambilan'
	];

	public function pembeli()
	{
		return $this->belongsTo(Pembeli::class, 'id_pembeli');
	}

	public function penitip()
	{
		return $this->belongsTo(Penitip::class, 'id_penitip');
	}

	public function transaksi()
	{
		return $this->belongsTo(Transaksi::class, 'id_transaksi');
	}
}
