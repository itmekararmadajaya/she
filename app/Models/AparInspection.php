<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterApar;
use App\Models\AparInspectionDetail;
use App\Models\User;
use App\Models\Gedung; // Import model Gedung yang baru

class AparInspection extends Model
{
    use HasFactory;

    // Nama tabel yang akan digunakan oleh model
    protected $table = 'apar_inspections';

    // Kolom-kolom yang dapat diisi secara massal (mass assignable)
    protected $fillable = [
        'master_apar_id',
        'gedung_id', // Menambahkan kolom 'gedung_id'
        'lokasi', 
        'user_id',
        'date',
        'keterangan_inspeksi',
        'final_foto_path',
    ];

    // Atribut yang akan diubah ke tipe data tertentu secara otomatis
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Accessor untuk mendapatkan status inspeksi (GOOD atau NOT GOOD)
     * berdasarkan semua detail inspeksi.
     */
    public function getStatusAttribute()
    {
        // Pastikan relasi 'details' tidak kosong sebelum memeriksa
        if ($this->details->isEmpty()) {
            return 'NOT GOOD';
        }

        // Periksa apakah setiap detail memiliki status 'B' (baik)
        return $this->details->every(fn ($d) => $d->value === 'B') ? 'GOOD' : 'NOT GOOD';
    }

    /**
     * Accessor untuk memformat tanggal ke format d-m-Y.
     */
    public function getDateFormattedAttribute()
    {
        return Carbon::parse($this->date)->format('d-m-Y');
    }

    // --- Relasi ---

    // Relasi ke model MasterApar (satu inspeksi milik satu APAR)
    public function masterApar()
    {
        return $this->belongsTo(MasterApar::class, 'master_apar_id');
    }
    
    // Relasi ke model Gedung (satu inspeksi milik satu Gedung)
    public function gedung()
    {
        return $this->belongsTo(Gedung::class);
    }

    // Relasi ke model User (satu inspeksi dilakukan oleh satu user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke model AparInspectionDetail (satu inspeksi memiliki banyak detail)
    public function details()
    {
        return $this->hasMany(AparInspectionDetail::class, 'apar_inspection_id');
    }
}
