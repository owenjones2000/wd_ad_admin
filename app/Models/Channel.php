<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $table = 'a_target_apps';

    protected $fillable = ['name'];
}
