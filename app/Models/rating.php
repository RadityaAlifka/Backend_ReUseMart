<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Rating
 * 
 * @property int $id_rating
 * @property int $id_pembeli
 * @property int $id_barang
 * @property int $rating
 * 
 * @property Barang $barang
 * @property Pembeli $pembeli
 *
 * @package App\Models
 */
class Rating extends Model
{
	protected $table = 'ratings';
	protected $primaryKey = 'id_rating';
	public $timestamps = false;

	protected $casts = [
		'id_pembeli' => 'int',
		'id_barang' => 'int',
		'rating' => 'int'
	];

	protected $fillable = [
		'id_pembeli',
		'id_barang',
		'rating'
	];

	public function barang()
	{
		return $this->belongsTo(Barang::class, 'id_barang');
	}

	public function pembeli()
	{
		return $this->belongsTo(Pembeli::class, 'id_pembeli');
	}
}
