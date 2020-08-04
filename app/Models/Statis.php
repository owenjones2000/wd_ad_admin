<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statis extends Model
{
    //
    protected $table= 'a_statis';

    protected $casts = [
        'statis' => 'array',
    ];

    const UPDATED_AT = null;
}
