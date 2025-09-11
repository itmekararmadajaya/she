<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterApar;

class AparInspection extends Model
{
    use HasFactory;

    // Nama tabel secara eksplisit, meskipun Laravel dapat menebaknya.
    protected $table = 'apar_inspections';

    protected $fillable = [
        'master_apar_id',
        'user_id',
        'date', // Pastikan kolom ini ada di fillable
        'status',
        'keterangan_inspeksi',
        'final_foto_path',
    ];

    /**
     * Atribut yang harus diubah ke tipe data tertentu.
     *
     * Ini adalah bagian penting yang hilang.
     * Memberitahu Laravel bahwa kolom 'date' adalah tipe data tanggal.
     * Ini akan memastikan kueri whereBetween berjalan dengan benar.
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Accessor untuk memformat tanggal ke tampilan d-m-Y.
     */
    public function getDateFormattedAttribute()
    {
        return Carbon::parse($this->date)->format('d-m-Y');
    }

    /**
     * Return GOOD or NOT GOOD.
     */
    public function getStatusAttribute()
    {
        return $this->details->every(fn ($d) => $d->value === 'B') ? 'GOOD' : 'NOT GOOD';
    }

    // Relasi ke APAR
    public function masterApar()
    {
        return $this->belongsTo(MasterApar::class, 'master_apar_id');
    }

    // Relasi ke User (yang melakukan inspeksi)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke detail item yang diperiksa
    public function details()
    {
        return $this->hasMany(AparInspectionDetail::class, 'apar_inspection_id');
    }
}
