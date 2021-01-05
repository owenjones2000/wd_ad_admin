<?php
namespace App\Models\Advertise;

use App\Models\AppTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Channel extends Model
{
    protected $table = 'a_target_apps';

    protected $fillable = ['name', 'bundle_id', 'platform', 'put_mode', 'rate', 'main_user_id'];

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

    public function restart()
    {
        $this->is_cold = 1;
        $this->save();
        $res1 = Redis::connection("restart")->hdel("wudiads_target_app_total_impression", $this->id);
        Log::info('channel restart'. $this->id);
        // Log::info($res);
        Log::info($res1);
    }

    public function tags()
    {
        return $this->belongsToMany(AppTag::class, 'a_target_app_tags', 'app_id', 'tag_id', 'id', 'id');
    }
}