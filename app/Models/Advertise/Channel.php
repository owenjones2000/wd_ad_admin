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

    /**
     * app黑名单
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function disableApps()
    {
        return $this->belongsToMany(
            App::class,
            'a_target_app_app_disabled',
            'target_app_id',
            'app_id',
            'id',
            'id'
        );
    }
}