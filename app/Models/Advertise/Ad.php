<?php
namespace App\Models\Advertise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use SoftDeletes;

    protected $table = 'a_ad';

    protected $fillable = ['name', 'type_id', 'campaign_id'];

    protected $appends = ['type', 'is_upload_completed'];
    
    /**
     * 管理员释放控制
     * @throws \Throwable
     */
    public function enable(){
        if($this->is_admin_disable){
            $this->is_admin_disable = false;
            $this->saveOrFail();
//            if($this->is_upload_completed){
//                $this->status = true;
//                $this->saveOrFail();
//            } else {
//                throw new \Exception('Lack of assets.');
//            }
        }
    }

    /**
     * 管理员封禁
     * @throws \Throwable
     */
    public function disable(){
        if(!$this->is_admin_disable){
            $this->status = false;
            $this->is_admin_disable = true;
            $this->saveOrFail();
        }
    }

    /**
     * 通过审核
     * @throws \Throwable
     */
    public function passReview()
    {
        if ($this->need_review) {
            $this->need_review = false;
            $this->saveOrFail();
//            if($this->is_upload_completed){
//                $this->status = true;
//                $this->saveOrFail();
//            } else {
//                throw new \Exception('Lack of assets.');
//            }
        }
    }

    /**
     * 广告活动
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign(){
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    /**
     * 投放国家
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function regions(){
        return $this->belongsToMany(Region::class, 'a_ad_country',
            'ad_id','country', 'id', 'code');
    }

    /**
     * 广告类型
     * @return AdType
     */
    public function getTypeAttribute(){
        return AdType::get($this->type_id);
    }

    /**
     * 素材是否满足
     * @return bool
     */
    public function getIsUploadCompletedAttribute(){
        foreach ($this['type']['need_asset_type'] as $need_asset_type_id){
            if(!$this['assets']->contains('type_id', $need_asset_type_id)){
                return false;
            }
        }
        return true;
    }

    /**
     * 素材
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets(){
        return $this->hasMany(Asset::class, 'ad_id', 'id');
    }
}
