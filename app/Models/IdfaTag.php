<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdfaTag extends Model
{
    //
    protected $table= 'a_idfa_tag';
    protected $fillable = [
        'name',
    ];
}
