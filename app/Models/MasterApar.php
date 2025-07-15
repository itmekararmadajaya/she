<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MasterApar extends Model
{
    protected $fillable = [
        'kode',
        'gedung_id',
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
        return $this->belongsTo(Gedung::class);
    }

    public function getTglKadaluarsaFormattedAttribute()
    {
        return Carbon::parse($this->tgl_kadaluarsa)->format('d-m-Y');
    }
    public function getTglrefillFormattedAttribute()
    {
        return Carbon::parse($this->tgl_refill)->format('d-m-Y');
    }

    public function inspeksis()
    {
        return $this->hasMany(AparInspection::class, 'master_apar_id');
    }
}
