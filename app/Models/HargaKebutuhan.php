<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaKebutuhan extends Model
{
    use HasFactory;

    // Mengubah urutan kolom dalam properti $fillable
    protected $fillable = [
        'vendor_id', 
        'kebutuhan_id', 
        'jenis_pemadam_id', 
        'jenis_isi_id', 
        'item_check_id', 
        'biaya', 
        'tanggal_perubahan',
    ];

    /**
     * Dapatkan vendor yang terkait dengan harga kebutuhan ini.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Dapatkan kebutuhan yang terkait dengan harga kebutuhan ini.
     */
    public function kebutuhan()
    {
        return $this->belongsTo(Kebutuhan::class, 'kebutuhan_id');
    }

    /**
     * Dapatkan transaksi yang menggunakan harga kebutuhan ini.
     */
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'biaya_id');
    }

    /**
     * Dapatkan jenis isi yang terkait dengan harga kebutuhan ini.
     */
    public function jenisIsi()
    {
        return $this->belongsTo(JenisIsi::class, 'jenis_isi_id');
    }

    /**
     * Dapatkan jenis pemadam yang terkait dengan harga kebutuhan ini.
     */
    public function jenisPemadam()
    {
        return $this->belongsTo(JenisPemadam::class, 'jenis_pemadam_id');
    }

    /**
     * Dapatkan item check yang terkait dengan harga kebutuhan ini.
     */
    public function itemCheck()
    {
        return $this->belongsTo(ItemCheck::class, 'item_check_id');
    }
}
