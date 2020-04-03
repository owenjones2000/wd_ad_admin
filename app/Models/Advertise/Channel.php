<?php
namespace App\Models\Advertise;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $table = 'a_target_apps';

    protected $fillable = ['name', 'bundle_id', 'platform', 'put_mode', 'rate'];

    public function publisher(){
        return $this->belongsTo(Account::class, 'main_user_id', 'id');
    }
    public function tokens(){
        return $this->hasMany(ApiToken::class, 'bundle_id', 'bundle_id');
    }
}