<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    protected $fillable = [
        'status',
        'title',
        'language',
        'category',
        'uuid',
        'layout',
        'created_at',
        'updated_at',
    ];

    public $timestamps = false;
}
