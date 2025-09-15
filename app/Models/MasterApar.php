<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class MasterApar extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini.
     *
     * @var string
     */
    protected $table = 'master_apars';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'kode',
        'jenis_pemadam_id',
        'gedung_id',
        'lokasi',
        'tgl_kadaluarsa',
        'ukuran',
        'jenis_isi_id',
        'satuan',
        'catatan',
        'is_active',
        'vendor_id', // Ditambahkan dari konteks sebelumnya (opsional jika digunakan)
        'tanggal_pembelian', // Ditambahkan dari konteks sebelumnya (opsional jika digunakan)
    ];

    /**
     * Atribut yang tidak dapat diisi secara massal.
     * Secara default, semua atribut dapat diisi kecuali yang ditentukan di sini.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Relasi dengan model Gedung.
     *
     * @return BelongsTo
     */
    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class, 'gedung_id');
    }
    
    public function penggunaan()
    {
        return $this->hasOne(Penggunaan::class, 'master_apar_id');
    }

    /**
     * Relasi dengan model JenisIsi.
     *
     * @return BelongsTo
     */
    public function jenisPemadam()
    {
        return $this->belongsTo(JenisPemadam::class, 'jenis_pemadam_id');
    }

    public function jenisIsi()
    {
        return $this->belongsTo(JenisIsi::class, 'jenis_isi_id');
    }

    /**
     * Mendapatkan semua inspeksi APAR untuk MasterApar ini.
     *
     * @return HasMany
     */
    public function aparInspections(): HasMany
    {
        return $this->hasMany(AparInspection::class, 'master_apar_id');
    }

    /**
     * Mendapatkan semua inspeksi untuk MasterApar.
     * Alias untuk aparInspections.
     */
    public function inspections()
    {
        return $this->hasMany(AparInspection::class, 'master_apar_id');
    }

    /**
     * Scope query untuk hanya menyertakan APAR yang aktif.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Mendapatkan status APAR berdasarkan detail inspeksi dan tanggal kedaluwarsa.
     * Ini adalah accessor dinamis yang menghitung status secara dinamis.
     */
    public function getStatusAttribute()
    {
        // Ambil inspeksi terbaru
        $latestInspection = $this->aparInspections()->latest('date')->first();

        // Kondisi 1: Cek jika ada detail inspeksi yang 'NOT GOOD'.
        // Ini memiliki prioritas lebih tinggi dari tanggal kedaluwarsa.
        if ($latestInspection && $latestInspection->details()->where('value', '!=', 'B')->exists()) {
            return 'NOT GOOD';
        }

        // Kondisi 2: Cek jika APAR sudah kedaluwarsa.
        // Ini hanya diperiksa jika kondisi inspeksi di atas tidak terpenuhi.
        if ($this->tgl_kadaluarsa && Carbon::parse($this->tgl_kadaluarsa)->isPast()) {
            return 'NOT GOOD';
        }

        return 'GOOD';
    }

    /**
     * Mendapatkan warna status untuk tujuan tampilan.
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'GOOD':
                return 'bg-success';
            case 'NOT GOOD':
                return 'bg-danger';
            case 'IN PROGRESS':
                return 'bg-warning';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Mendapatkan warna teks untuk status.
     */
    public function getStatusTextColorAttribute()
    {
        return $this->status === 'IN PROGRESS' ? 'text-dark' : 'text-white';
    }
}