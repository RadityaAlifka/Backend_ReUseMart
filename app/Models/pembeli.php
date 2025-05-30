<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
/**
 * Class Pembeli
 * 
 * @property int $id_pembeli
 * @property string $nama_pembeli
 * @property string $email
 * @property string $no_telp
 * @property string $password
 * @property int $poin
 * 
 * @property Collection|Alamat[] $alamats
 * @property Diskusi|null $diskusi
 * @property Collection|Merchandise[] $merchandises
 * @property Collection|Pengambilan[] $pengambilans
 * @property Collection|Rating[] $ratings
 *
 * @package App\Models
 */
class Pembeli extends Model implements AuthenticatableContract 
{
	use HasApiTokens, Authenticatable;

	protected $table = 'pembelis';
	protected $primaryKey = 'id_pembeli';
	public $timestamps = false;

	protected $casts = [
		'poin' => 'int'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'user_id',
		'nama_pembeli',
		'email',
		'no_telp',
		'password',
		'poin'
	];

	public function alamats()
	{
		return $this->hasMany(Alamat::class, 'id_pembeli');
	}

	public function diskusi()
	{
		return $this->hasOne(Diskusi::class, 'id_pembeli');
	}

	public function merchandises()
	{
		return $this->hasMany(Merchandise::class, 'id_pembeli');
	}

	public function pengambilans()
	{
		return $this->hasMany(Pengambilan::class, 'id_pembeli');
	}

	public function ratings()
	{
		return $this->hasMany(Rating::class, 'id_pembeli');
	}
	public function user() {
        return $this->belongsTo(User::class);
    }

	
}
