<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penggunaan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'master_apar_id',
        'gedung_id',
        'lokasi',
        'tanggal_penggunaan',
        'alasan',
        'status',
    ];

    /**
     * Get the user that owns the Penggunaan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the master APAR that owns the Penggunaan.
     */
    public function masterApar(): BelongsTo
    {
        return $this->belongsTo(MasterApar::class, 'master_apar_id');
    }

    /**
     * Get the gedung that owns the Penggunaan.
     */
    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class, 'gedung_id');
    }
}
