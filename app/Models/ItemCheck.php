<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCheck extends Model
{
    protected $fillable = [
        'name',
        'is_delete'
    ];
}
