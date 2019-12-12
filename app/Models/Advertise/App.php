<?php
namespace App\Models\Advertise;

use App\Exceptions\BizException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class App extends Model
{
    use SoftDeletes;

    protected $table = 'a_app';
    protected $appends = ['track'];

    protected $fillable = ['name', 'bundle_id', 'os', 'track_platform_id', 'track_code', 'status'];

    /**
     * 构造Campaign
     * @param User $user
     * @param $params
     * @return mixed
     */
//    public static function Make($user, $params){
//        $apps = DB::transaction(function () use($user, $params) {
//            $main_user_id = $user->getMainId();
//            if (empty($params['id'])) {
//                $apps = new self();
//                $apps->main_user_id = $main_user_id;
//                $apps['status'] = true;
//            } else {
//                $apps = self::query()->where([
//                    'id' => $params['id'],
//                    'main_user_id' => $main_user_id
//                ])->firstOrFail();
//            }
//            if($params['track_platform_id'] == TrackPlatform::Adjust && empty($params['track_code'])){
//                throw new BizException('Track code required.');
//            }
//            $apps->fill($params);
//            $apps->saveOrFail();
//
//            return $apps;
//        }, 3);
//        return $apps;
//    }

    /**
     * 管理员解除状态控制
     * @throws \Throwable
     */
    public function enable(){
        if($this->is_admin_disable){
            $this->is_admin_disable = false;
            $this->saveOrFail();
        }
    }

    /**
     * 管理员停用
     * @throws \Throwable
     */
    public function disable(){
        $this->is_admin_disable = true;
        $this->status = false;
        $this->saveOrFail();
    }
    
    public function getTrackAttribute(){
        return TrackPlatform::get($this['track_platform_id']);
    }
}