<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    //
    protected $fillable = [
        'status',
        'create_time',
        'title',
        'language',
        'category',
        'uuid',
        'layout',
    ];
}
