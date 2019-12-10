<?php
namespace App\Models\Advertise;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $table = 'a_target_apps';

    protected $fillable = ['name', 'bundle_id', 'platform'];

    public function tokens(){
        return $this->hasMany(ApiToken::class, 'bundle_id', 'bundle_id');
    }
}