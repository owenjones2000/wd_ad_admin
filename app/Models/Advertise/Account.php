<?php
namespace App\Models\Advertise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Account extends Model
{
    use SoftDeletes;

    protected $table = 'a_users';

    protected $fillable = ['username', 'email', 'realname'];

    /**
     * 构造Campaign
     * @param Account $account
     * @param $params
     * @return mixed
     */
    public static function Make($params){
        $account = DB::transaction(function () use($params) {
            if (empty($params['id'])) {
                $account = new Account();
                $account['status'] = true;
            } else {
                $account = Account::query()->where([
                    'id' => $params['id'],
                ])->firstOrFail();
            }
            $account['main_user_id'] = 0; // 主账号

            if (!empty($params['password'])){
                $data['password_hash'] = Hash::make($params['password']);
            }

            $account->fill($params);
            $account->saveOrFail();
            
            return $account;
        }, 3);

        return $account;
    }

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
    
    public static function rules($request_params = [])
    {
        return [
            'email' => 'required|email|unique:a_users,email,'.($request_params['id']??''),
            'username' => 'required|string|alpha_dash|min:2|max:14',
            'password' => empty($request_params['id']) ?
                'required|min:2|max:14' : 'min:2|max:14'
        ];
    }
}