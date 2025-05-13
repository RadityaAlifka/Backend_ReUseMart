<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Detailtransaksi
 * 
 * @property int $id_transaksi
 * @property int $id_barang
 * 
 * @property Barang $barang
 * @property Transaksi $transaksi
 *
 * @package App\Models
 */
class Detailtransaksi extends Model
{
	protected $table = 'detailtransaksis';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id_transaksi' => 'int',
		'id_barang' => 'int'
	];

	protected $fillable = [
		'id_transaksi',
		'id_barang'
	];

	public function barang()
	{
		return $this->belongsTo(Barang::class, 'id_barang');
	}

	public function transaksi()
	{
		return $this->belongsTo(Transaksi::class, 'id_transaksi');
	}
}
