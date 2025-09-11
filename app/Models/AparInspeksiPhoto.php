<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AparInspeksiPhoto extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'apar_inspeksi_photos';

    /**
     * Atribut yang bisa diisi secara massal (mass assignable).
     *
     * @var array
     */
    protected $fillable = [
        'inspeksi_id',
        'foto_path',
        'item_check_id',
    ];

    /**
     * Mendefinisikan relasi ke model AparInspection.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inspection()
    {
        return $this->belongsTo(AparInspection::class, 'inspeksi_id');
    }
}