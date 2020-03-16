<?php
namespace App\Models\Advertise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Account extends Model
{
    use SoftDeletes;

    protected $table = 'a_users';

    protected $hidden = ['password_hash', 'remember_token', 'username'];
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
                $account['main_user_id'] = 0; // 主账号
            } else {
                $account = Account::query()->where([
                    'id' => $params['id'],
                ])->firstOrFail();
            }

            if (!empty($params['password'])){
                $account['password_hash'] = Hash::make($params['password']);
            }

            $account->fill($params);
            $account->saveOrFail();
            
            return $account;
        }, 3);

        return $account;
    }

    /**
     * 启用
     * @throws \Throwable
     */
    public function enable(){
        if(!$this->status){
            $this->status = true;
            $this->saveOrFail();
        }
    }

    /**
     * 停用
     * @throws \Throwable
     */
    public function disable(){
        if($this->status){
            $this->status = false;
            $this->saveOrFail();
        }
    }

    /**
     * 生成账单
     */
    public function generateBill(){
        $last_month_timestamp = strtotime('-1 month');
        $start_date = date('Y-m-01', $last_month_timestamp);
        $end_date = date('Y-m-t', $last_month_timestamp);
        $due_date = date('Y-m-t');
        $fee_amount_query = Install::multiTableQuery(function($query){
            return $query->select(['spend'])->whereIn('app_id', $this->apps()->select('id')->getQuery());
        }, $start_date, $end_date);
        $fee_amount = $fee_amount_query->sum('spend');
        $this->bills()
            ->updateOrCreate(
                [
                    'start_date' => $start_date,
                    'end_date' =>$end_date,
                ],
                [
                    'fee_amount' => $fee_amount,
                    'due_date' => $due_date,
                    'paid_at' => $fee_amount > 0 ? null : Carbon::now()
                ]
        );
    }

    public function apps(){
        return $this->hasMany(App::class, 'main_user_id', 'id');
    }

    public function bill(){
        return $this->hasOne(BillSet::class, 'id', 'id');
    }
    public function bills(){
        return $this->hasMany(Bill::class, 'main_user_id', 'id');
    }

    public function children(){
        return $this->hasMany(Account::class, 'main_user_id', 'id');
    }
    
    public static function rules($request_params = [])
    {
        return [
            'email' => 'required|email|unique:a_users,email,'.($request_params['id']??''),
            'password' => empty($request_params['id']) ?
                'required|min:2|max:14' : 'min:2|max:14'
        ];
    }
}
