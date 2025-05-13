<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RequestDonasi
 * 
 * @property int $id_request
 * @property int $id_organisasi
 * @property int $id_pegawai
 * @property Carbon $tanggal_request
 * @property string $detail_request
 * 
 * @property Organisasi $organisasi
 * @property Pegawai $pegawai
 *
 * @package App\Models
 */
class RequestDonasi extends Model
{
	protected $table = 'request_donasis';
	protected $primaryKey = 'id_request';
	public $timestamps = false;

	protected $casts = [
		'id_organisasi' => 'int',
		'id_pegawai' => 'int',
		'tanggal_request' => 'datetime'
	];

	protected $fillable = [
		'id_organisasi',
		'id_pegawai',
		'tanggal_request',
		'detail_request'
	];

	public function organisasi()
	{
		return $this->belongsTo(Organisasi::class, 'id_organisasi');
	}

	public function pegawai()
	{
		return $this->belongsTo(Pegawai::class, 'id_pegawai');
	}
}
