<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AparReparasiPhoto extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'apar_reparasi_photos';

    /**
     * Atribut yang bisa diisi secara massal (mass assignable).
     *
     * @var array
     */
    protected $fillable = [
        'inspeksi_id',
        'item_check_id',
        'foto_path',
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

    /**
     * Mendefinisikan relasi ke model ItemCheck.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function itemCheck()
    {
        return $this->belongsTo(ItemCheck::class, 'item_check_id');
    }
}