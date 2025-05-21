<?php


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Barang
 * 
 * @property int $id_barang
 * @property int $id_kategori
 * @property int $id_penitipan
 * @property int $id_donasi
 * @property string $nama_barang
 * @property string $deskripsi_barang
 * @property string $garansi
 * @property Carbon $tanggal_garansi
 * @property float $harga
 * @property string $status_barang
 * @property float $berat
 * @property Carbon $tanggal_keluar
 * @property string $gambar1
 * @property string $gambar2
 * 
 * @property Donasi $donasi
 * @property KategoriBarang $kategori_barang
 * @property Penitipan $penitipan
 * @property Detailtransaksi|null $detailtransaksi
 * @property Diskusi|null $diskusi
 * @property Collection|Rating[] $ratings
 *
 * @package App\Models
 */
class Barang extends Model
{
    protected $table = 'barangs';
    protected $primaryKey = 'id_barang';
    public $timestamps = false;

    protected $casts = [
        'id_kategori' => 'int',
        'id_penitipan' => 'int',
        'id_donasi' => 'int',
        'tanggal_garansi' => 'datetime',
        'harga' => 'float',
        'berat' => 'float',
        'tanggal_keluar' => 'datetime'
    ];

    protected $fillable = [
        'id_kategori',
        'id_penitipan',
        'id_donasi',
        'nama_barang',
        'deskripsi_barang',
        'garansi',
        'tanggal_garansi',
        'harga',
        'status_barang',
        'berat',
        'tanggal_keluar',
        'gambar1', // Menambahkan kolom gambar1
        'gambar2'  // Menambahkan kolom gambar2
    ];
    protected $appends = ['gambar1_url', 'gambar2_url'];

    public function getGambar1UrlAttribute()
    {
        return asset('storage/' . $this->gambar1);
    }

    public function getGambar2UrlAttribute()
    {
        return asset('storage/' . $this->gambar2);
    }

    public function updateStatus($newStatus)
    {
        $this->status_barang = $newStatus;
        $this->save();
    }

    public function donasi()
    {
        return $this->belongsTo(Donasi::class, 'id_donasi');
    }

    public function kategori_barang()
    {
        return $this->belongsTo(KategoriBarang::class, 'id_kategori');
    }

    public function penitipan()
    {
        return $this->belongsTo(Penitipan::class, 'id_penitipan');
    }

    public function detailtransaksi()
    {
        return $this->hasOne(Detailtransaksi::class, 'id_barang');
    }

    public function diskusi()
    {
        return $this->hasOne(Diskusi::class, 'id_barang');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'id_barang');
    }
}