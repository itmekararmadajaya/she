<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    
    protected $fillable = ['master_apar_id', 'vendor_id', 'kebutuhan_id', 'biaya_id', 'tanggal_pembelian', 'tanggal_pelunasan'];
    
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    
    public function kebutuhan()
    {
        return $this->belongsTo(Kebutuhan::class);
    }
    
    public function hargaKebutuhan()
    {
        return $this->belongsTo(HargaKebutuhan::class, 'biaya_id');
    }
    
    public function masterApar()
    {
        return $this->belongsTo(MasterApar::class, 'master_apar_id');
    }
}
