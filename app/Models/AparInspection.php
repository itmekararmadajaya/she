<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AparInspection extends Model
{
    protected $fillable = [
        'master_apar_id',
        'user_id',
        'date'
    ];

    public function getDateFormattedAttribute()
    {
        return Carbon::parse($this->date)->format('d-m-Y');
    }

    /**
     * Return OK or NOK
     */
    public function getStatusAttribute(){
        return $this->details->every(fn($d) => $d->value === 'B') ? 'OK' : 'NOK';
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
