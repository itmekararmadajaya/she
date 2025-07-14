<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterApar extends Model
{
    protected $fillable = [
        'kode',
        'gedung',
        'lokasi',
        'jenis_isi',
        'ukuran',
        'satuan',
        'tgl_kadaluarsa',
        'jenis_pemadam',
        'tanda',
        'catatan',
        'tgl_refill',
        'keterangan',
        'is_active',
    ];

    public function gedung() {
        return $this->belongsTo(Gedung::class, 'gedung_id');
    }
}
