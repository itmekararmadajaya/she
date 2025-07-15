<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AparInspectionDetail extends Model
{
    protected $fillable = [
        'apar_inspection_id',
        'item_check_id',
        'value',
        'remark'
    ];

    public function inspeksi()
    {
        return $this->belongsTo(AparInspection::class, 'apar_inspection_id');
    }

    public function itemCheck()
    {
        return $this->belongsTo(ItemCheck::class, 'item_check_id');
    }
}
