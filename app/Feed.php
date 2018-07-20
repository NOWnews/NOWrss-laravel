<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    //
    protected $fillable = [
        'create_time', 'title', 'language', 'category', 'uuid', 'layout'
    ];
}
