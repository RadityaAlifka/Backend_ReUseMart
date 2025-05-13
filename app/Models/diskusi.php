<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Diskusi
 * 
 * @property int $id_pembeli
 * @property int $id_pegawai
 * @property string $detail_diskusi
 * @property int $id_barang
 * @property string $reply
 * 
 * @property Barang $barang
 * @property Pegawai $pegawai
 * @property Pembeli $pembeli
 *
 * @package App\Models
 */
class Diskusi extends Model
{
	protected $table = 'diskusis';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id_pembeli' => 'int',
		'id_pegawai' => 'int',
		'id_barang' => 'int'
	];

	protected $fillable = [
		'id_pembeli',
		'id_pegawai',
		'detail_diskusi',
		'id_barang',
		'reply'
	];

	public function barang()
	{
		return $this->belongsTo(Barang::class, 'id_barang');
	}

	public function pegawai()
	{
		return $this->belongsTo(Pegawai::class, 'id_pegawai');
	}

	public function pembeli()
	{
		return $this->belongsTo(Pembeli::class, 'id_pembeli');
	}
}
