<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class KategoriBarang
 * 
 * @property int $id_kategori
 * @property string $nama_kategori
 * 
 * @property Collection|Barang[] $barangs
 *
 * @package App\Models
 */
class KategoriBarang extends Model
{
	protected $table = 'kategori_barangs';
	protected $primaryKey = 'id_kategori';
	public $timestamps = false;

	protected $fillable = [
		'nama_kategori'
	];

	public function barangs()
	{
		return $this->hasMany(Barang::class, 'id_kategori');
	}
}
