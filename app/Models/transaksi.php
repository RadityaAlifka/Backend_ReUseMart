<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Transaksi
 * 
 * @property int $id_transaksi
 * @property string $id_pembeli
 * @property string $id_penjual
 * @property Carbon $tgl_pesan
 * @property Carbon $tgl_lunas
 * @property float $diskon_poin
 * @property string $bukti_pembayaran
 * @property string $status_pembayaran
 * 
 * @property Detailtransaksi|null $detailtransaksi
 * @property Collection|Pengambilan[] $pengambilans
 * @property Collection|Pengiriman[] $pengirimen
 *
 * @package App\Models
 */
class Transaksi extends Model
{
	protected $table = 'transaksis';
	protected $primaryKey = 'id_transaksi';
	public $timestamps = false;

	protected $casts = [
		'tgl_pesan' => 'datetime',
		'tgl_lunas' => 'datetime',
		'diskon_poin' => 'float'
	];

	protected $fillable = [
		'id_pembeli',
		
		'tgl_pesan',
		'tgl_lunas',
		'diskon_poin',
		'bukti_pembayaran',
		'status_pembayaran',
		'status_transaksi',
		'total_harga',
	];
	public function pembeli()
	{
		return $this->belongsTo(Pembeli::class, 'id_pembeli');
	}
	public function detailtransaksi()
	{
		return $this->hasMany(Detailtransaksi::class, 'id_transaksi');
	}

	public function pengambilans()
	{
		return $this->hasMany(Pengambilan::class, 'id_transaksi');
	}

	public function pengirimen()
	{
		return $this->hasMany(Pengiriman::class, 'id_transaksi');
	}
}
