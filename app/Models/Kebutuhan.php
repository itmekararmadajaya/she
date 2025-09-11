<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kebutuhan extends Model
{
    use HasFactory;

    protected $fillable = ['kebutuhan'];

    public function hargaKebutuhans()
    {
        return $this->hasMany(HargaKebutuhan::class, 'kebutuhan_id');
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'harga_kebutuhans', 'kebutuhan_id', 'vendor_id')
                    ->withPivot('biaya', 'tanggal_perubahan');
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }
}
