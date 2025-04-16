<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Alamat
 * 
 * @property int $id_alamat
 * @property int $id_pembeli
 * @property string $kabupaten
 * @property string $kecamatan
 * @property string $kelurahan
 * @property string $detail_alamat
 * @property int $kode_pos
 * @property string $label_alamat
 * 
 * @property Pembeli $pembeli
 *
 * @package App\Models
 */
class Alamat extends Model
{
	protected $table = 'alamats';
	protected $primaryKey = 'id_alamat';
	public $timestamps = false;

	protected $casts = [
		'id_pembeli' => 'int',
		'kode_pos' => 'int'
	];

	protected $fillable = [
		'id_pembeli',
		'kabupaten',
		'kecamatan',
		'kelurahan',
		'detail_alamat',
		'kode_pos',
		'label_alamat'
	];

	public function pembeli()
	{
		return $this->belongsTo(Pembeli::class, 'id_pembeli');
	}
}
