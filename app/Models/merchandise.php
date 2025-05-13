<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Merchandise
 * 
 * @property int $id_merchandise
 * @property int $id_pembeli
 * @property int $id_pegawai
 * @property string $nama_merchandise
 * @property int $stock_merchandise
 * 
 * @property Pegawai $pegawai
 * @property Pembeli $pembeli
 *
 * @package App\Models
 */
class Merchandise extends Model
{
	protected $table = 'merchandises';
	protected $primaryKey = 'id_merchandise';
	public $timestamps = false;

	protected $casts = [
		'id_pembeli' => 'int',
		'id_pegawai' => 'int',
		'stock_merchandise' => 'int'
	];

	protected $fillable = [
		'id_pembeli',
		'id_pegawai',
		'nama_merchandise',
		'stock_merchandise'
	];

	public function pegawai()
	{
		return $this->belongsTo(Pegawai::class, 'id_pegawai');
	}

	public function pembeli()
	{
		return $this->belongsTo(Pembeli::class, 'id_pembeli');
	}
}
