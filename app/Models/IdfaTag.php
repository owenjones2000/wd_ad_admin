<?php

namespace App\Models;

use App\Models\Advertise\App;
use Illuminate\Database\Eloquent\Model;

class IdfaTag extends Model
{
    //
    protected $table= 'a_idfa_tag';
    protected $fillable = [
        'name',
    ];

    public function apps()
    {
        return $this->belongsToMany(App::class, 'a_idfa_tag_app', 'tag_id', 'app_id', 'id', 'id');
    }

    public function idfas()
    {
        return $this->hasMany(Idfa::class, 'tag_id', 'id');
    }
       
}
