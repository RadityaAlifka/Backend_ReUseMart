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

    // Tambahkan ini untuk beri tahu primary key-nya
    protected $primaryKey = 'id_diskusi';

    // Jika primary key auto increment atau tidak
    public $incrementing = true;

    public $timestamps = false;

    protected $casts = [
        'id_pembeli' => 'int',
        'id_pegawai' => 'int',
        'id_barang' => 'int',
        'id_diskusi' => 'int',  // tambahkan ini supaya casting ke int juga
    ];

    protected $fillable = [
        'id_pembeli',
        'id_pegawai',
        'detail_diskusi',
        'id_barang',
        'reply'
    ];

    // relasi seperti sebelumnya
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

