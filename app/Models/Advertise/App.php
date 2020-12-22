<?php

namespace App\Models\Advertise;

use App\Exceptions\BizException;
use App\Models\AppTag;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class App extends Model
{
    use SoftDeletes;

    protected $table = 'a_app';
    protected $appends = ['track'];
    const  App_Type_Shop = 0;
    const  App_Type_Apk = 1;
    const  App_Type_Samsung = 2;

    protected $fillable = [
        'name', 'description',
        'icon_url', 
        'bundle_id',
        'os',
        'track_platform_id', 
        'track_code', 
        'track_url',
        'impression_url',
        'status',
        'app_id',
        'type',
        'extra_data'
    ];

    protected $casts =  [
        'extra_data' => 'array',
    ];
    /**
     * 构造Campaign
     * @param User $user
     * @param $params
     * @return mixed
     */
    public static function Make($params)
    {
        $apps = DB::transaction(function () use ($params) {
            if (empty($params['id'])) {
                $apps = new self();
                $apps->is_admin_disable = true;
                $apps['status'] = false;
                if ($params['track_platform_id'] == TrackPlatform::Adjust && empty($params['track_code'])) {
                    throw new Exception('Track code required.');
                }
            } else {
                $apps = self::query()->where([
                    'id' => $params['id'],
                ])->firstOrFail();
                
                if (isset($params['type']) && $params['type']  == 1) {
                    $params['extra_data'] = array_merge($apps->extra_data, $params['extra_data']);
                } else {
                    unset($params['extra_data']);
                }
            }

            $apps->fill($params);
            $apps->saveOrFail();

            return $apps;
        }, 3);
        return $apps;
    }

    /**
     * 管理员解除状态控制
     * @throws \Throwable
     */
    public function enable()
    {
        if ($this->is_admin_disable) {
            $this->is_admin_disable = false;
            $this->saveOrFail();
        }
    }

    /**
     * 管理员停用
     * @throws \Throwable
     */
    public function disable()
    {
        $this->is_admin_disable = true;
        $this->status = false;
        $this->saveOrFail();
    }

    public function getTrackAttribute()
    {
        return TrackPlatform::get($this['track_platform_id']);
    }

    /**
     * 广告主
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function advertiser()
    {
        return $this->belongsTo(Account::class, 'main_user_id', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(AppTag::class, 'a_app_tags', 'app_id', 'tag_id', 'id', 'id');
    }
}
