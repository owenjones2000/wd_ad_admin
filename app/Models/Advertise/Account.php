<?php

namespace App\Models\Advertise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Account extends Model
{
    use SoftDeletes;

    protected $table = 'a_users';

    protected $hidden = [
        'name', 'password_hash', 'remember_token', 'username', 'pivot',
        'main_user_id', 'deleted_at'
    ];
    protected $fillable = [
        'username',
        'email', 
        'realname',
        'agency_name',
        'is_agency',
    ];

    /**
     * 构造Campaign
     * @param Account $account
     * @param $params
     * @return mixed
     */
    public static function Make($params)
    {
        $account = DB::transaction(function () use ($params) {
            if (empty($params['id'])) {
                $account = new Account();
                $account['status'] = true;
                $account['main_user_id'] = 0; // 主账号
            } else {
                $account = Account::query()->where([
                    'id' => $params['id'],
                ])->firstOrFail();
            }

            if (!empty($params['password'])) {
                $account['password_hash'] = Hash::make($params['password']);
            }

            $account->fill($params);
            $account->saveOrFail();

            if (empty($params['id'])) {
                $permission_keys = UaPermission::query()->pluck('id')->toArray();
                $permissions = array_fill_keys(
                    $permission_keys,
                    [
                        'main_user_id' => $account['id'],
                        'model_type'   => 'App\User',
                    ]
                );
                $account->permissions()->sync($permissions);
            }

            return $account;
        }, 3);

        return $account;
    }

    /**
     * 启用
     * @throws \Throwable
     */
    public function enable()
    {
        if (!$this->status) {
            $this->status = true;
            $this->saveOrFail();
        }
    }

    /**
     * 停用
     * @throws \Throwable
     */
    public function disable()
    {
        if ($this->status) {
            $this->status = false;
            $this->saveOrFail();
        }
    }

    /**
     * 启用广告投放
     * @throws \Throwable
     */
    public function enableAdvertising()
    {
        if (!$this->isAdvertiseEnabled) {
            $this->isAdvertiseEnabled = true;
            $this->saveOrFail();
        }
    }

    /**
     * 停用广告投放
     * @throws \Throwable
     */
    public function disableAdvertising()
    {
        if ($this->isAdvertiseEnabled) {
            $this->isAdvertiseEnabled = false;
            $this->saveOrFail();
        }
    }

    /**
     * 启用广告变现
     * @throws \Throwable
     */
    public function enablePublishing()
    {
        if (!$this->isPublishEnabled) {
            $this->isPublishEnabled = true;
            $this->saveOrFail();
        }
    }

    /**
     * 停用广告变现
     * @throws \Throwable
     */
    public function disablePublishing()
    {
        if ($this->isPublishEnabled) {
            $this->isPublishEnabled = false;
            $this->saveOrFail();
        }
    }

    /**
     * 设置账单配置
     *
     * @param $params
     */
    public function setBill($params)
    {
        $this->bill()->updateOrCreate(
            ['id' => $params['id']],
            [
                'address' => Arr::get($params, 'address', ''),
                'phone'   => Arr::get($params, 'phone', ''),
                'company'   => Arr::get($params, 'company', ''),
            ]
        );
    }

    /**
     * 生成账单
     */
    public function generateBill()
    {
        $last_month_timestamp = strtotime('-1 month');
        $start_date = date('Y-m-01', $last_month_timestamp);
        $end_date = date('Y-m-t', $last_month_timestamp);
        $due_date = date('Y-m-t');
        $date_begin = date('Ym01', $last_month_timestamp);
        $date_end = date('Ymt', $last_month_timestamp);
        //        $fee_amount_query = Install::multiTableQuery(function($query){
        //            return $query->select(['spend'])->whereIn('app_id', $this->apps()->select('id')->getQuery());
        //        }, $start_date, $end_date);
        $table = 'y_sub_tasks_' . date('Ym', $last_month_timestamp);
        if (!Schema::hasTable($table)) {
            Log::error("monthly table is not exist!table:" . $table);
            return false;
        }
        $fee_amount_query = AdvertiseKpi::query()->from($table)
            ->whereBetween('date', [$date_begin, $date_end])
            ->whereIn('app_id', $this->apps()->select('id')->getQuery());
        $fee_amount = $fee_amount_query->sum('spend');
        $billInfo = $fee_amount_query->with(['campaign:id,name', 'app:id,name'])
            ->select([
                'campaign_id',
                'app_id',
                DB::raw('sum(spend) as spend'),
                DB::raw('sum(installations) as installations'),
            ])
            ->groupBy('campaign_id')
            ->get()->toArray();
        if ($fee_amount > 0) {
            DB::transaction(function () use ($start_date, $end_date, $fee_amount, $due_date, $billInfo, $last_month_timestamp) {
                $bill = $this->bills()->firstOrNew(
                    [
                        'start_date' => $start_date,
                        'end_date'   => $end_date,
                    ],
                    [
                        'fee_amount' => $fee_amount,
                        'due_date'   => $due_date,
                        'paid_at'    => $fee_amount > 0 ? null : Carbon::now()
                    ]
                );
                if (empty($bill['id']) || empty($bill['invoice_id'])) {
                    $bill['invoice_id'] = 'wudiads-'
                        . date('Ym', $last_month_timestamp)
                        . '-' . date('md') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                }
                $bill->saveOrFail();
                foreach ($billInfo as $key => $value) {
                    BillInfo::firstOrCreate([
                        'bill_id' => $bill->id,
                        'campaign_id' => $value['campaign_id'],
                    ], [
                        'campagin_name' => $value['campaign']['name'] ?? '',
                        'app_name' => $value['app']['name'] ?? '',
                        'spend' => $value['spend'] ?? 0,
                        'installations' => $value['installations'] ?? 0,
                    ]);
                }
            }, 3);
        }
    }

    public function apps()
    {
        return $this->hasMany(App::class, 'main_user_id', 'id');
    }

    public function bill()
    {
        return $this->hasOne(BillSet::class, 'id', 'id');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'main_user_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'main_user_id', 'id');
    }

    /**
     * 广告人员
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function advertisers()
    {
        return $this->belongsToMany(
            Account::class,
            'a_users_advertiser',
            'main_user_id',
            'advertiser_user_id',
            'id',
            'id'
        );
    }

    public function permissions($main_user_id = null)
    {
        $query = $this->belongsToMany(
            UaPermission::class,
            'ua_model_has_permissions',
            'model_id',
            'permission_id'
        );
        if ($main_user_id) {
            $query->wherePivot('main_user_id', $main_user_id);
        }
        return $query;
    }

    public static function rules($request_params = [])
    {
        return [
            'email'    => 'required|email|unique:a_users,email,' . ($request_params['id'] ?? ''),
            'password' => empty($request_params['id']) ?
                'required|min:6|max:32' : 'min:6|max:32'
        ];
    }
}
