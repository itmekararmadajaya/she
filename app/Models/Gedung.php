<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gedung extends Model
{
    protected $fillable = [
        'nama'
    ];

    public function masterApars(){
        return $this->hasMany(MasterApar::class);
    }
}
