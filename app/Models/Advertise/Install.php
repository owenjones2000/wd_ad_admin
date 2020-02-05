<?php
namespace App\Models\Advertise;

use App\Traits\MultiTable;
use Illuminate\Database\Eloquent\Model;

class Install extends Model
{
    use MultiTable;

    protected $table = 'z_installations';

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
