<?php
namespace App\Models\Advertise;

use App\Traits\MultiTable;
use Illuminate\Database\Eloquent\Model;

class AdvertiseKpi extends Model
{
    use MultiTable;
    const Type_Reward_video = 1;
    const Type_Interstitial_video = 2;

    protected $table = 'z_sub_tasks';

    /**
     * 应用
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function app()
    {
        return $this->belongsTo(App::class, 'app_id', 'id');
    }
    /**
     * 广告活动
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign(){
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    /**
     * 广告
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ad(){
        return $this->belongsTo(Ad::class, 'ad_id', 'id');
    }

    /**
     * 区域
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region(){
        return $this->belongsTo(Region::class, 'country', 'code');
    }

    /**
     * 渠道
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel(){
        return $this->belongsTo(Channel::class, 'target_app_id', 'id');
    }
}