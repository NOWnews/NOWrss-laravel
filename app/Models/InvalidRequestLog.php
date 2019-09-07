<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvalidRequestLog extends Model
{
    protected $fillable = [
        'ip',
        'created_at',
    ];

    public $timestamps = false;
}
