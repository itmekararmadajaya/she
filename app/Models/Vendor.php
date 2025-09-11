<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = ['nama_vendor', 'kontak'];

    // relasi ke HargaKebutuhan
    public function hargaKebutuhans()
    {
        return $this->hasMany(HargaKebutuhan::class, 'vendor_id');
    }

    // relasi langsung ke kebutuhan lewat pivot harga_kebutuhans
    public function kebutuhans()
    {
        return $this->belongsToMany(Kebutuhan::class, 'harga_kebutuhans', 'vendor_id', 'kebutuhan_id')
                    ->withPivot('biaya', 'tanggal_perubahan');
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }
}
