<?php

namespace App\Console\Commands;

use App\Laravue\Models\User;
use App\Models\Advertise\Account;
use App\Models\Advertise\App;
use App\Models\Advertise\Click;
use App\Models\CallbackInstallation;
use App\Models\Credit;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AccountCredit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:credit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '广告主额度消耗';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('start' . __METHOD__);
        $callback_installs = CallbackInstallation::query()
            ->where('id', '>=', 5227757)
            ->where('is_credit', 0)
            ->limit(1000)
            ->get();
        try {
            $client = new Client();
            foreach ($callback_installs as $install) {
                // 点击日期
                $date = substr($install['click_id'], 0, 8);
                $click = Click::multiTableQuery(function ($query) use ($date, $install) {
                    $query->where('date', $date);
                    return $query;
                }, $date, $date)->where('click_id', $install['click_id'])->first();
                // dump($click);
                // dump($click->id);
                if (!$click) {
                    $install['is_credit'] = 1;
                    $install->save();
                } else {
                    DB::transaction(function () use ($click, $install) {
                        $install['is_credit'] = 1;
                        $install->save();
                        $mainUserId = App::find($click->app_id)->main_user_id ?? 0;
                        $credit = new Credit();
                        $credit->callback_id = $install->id;
                        $credit->click_id = $install->click_id;
                        $credit->app_id = $click->app_id;
                        $credit->main_user_id = $mainUserId;
                        $credit->change = -$click->spend;
                        $credit->save();
                        $account = Account::find($mainUserId);
                        $account->expend_credit += $click->spend;
                        $account->ava_credit -= $click->spend;
                        $account->save();
                    });
                }
            }
            //暂时取消自动关闭
            $accounts = Account::where('ava_credit', '<', 0)->where('status', 1)
            ->where('isAdvertiseEnabled', 1)->get();
            if ($accounts) {
                foreach ($accounts as $key => $account) {
                    $key = 'credit_removal' . $account->id;
                    Log::error('account  credit disable' . $account->id . ' realname ' . $account->realname . ' end');
                    $apps = App::where('main_user_id', $account->id)
                        ->where('status', 1)->update([
                            'status' => 0,
                            'is_credit_disable' => 1,
                            'is_admin_disable' => 1,
                        ]);
                    $count = Redis::incr($key);
                    if ($count <= 3){
                        $client->request("POST", "https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=e797c254-5ca2-44b7-a254-71670c45112d", [
                            "json" => [
                                "msgtype" => "text",
                                "text" => [
                                    "content" => "account  credit disable' . $account->id .' realname '. $account->realname.' end",
                                    "mentioned_list" => ["@all"],
                                ]
                            ]
                        ]);
                    }
                }
            }
            Log::info('finish' . __METHOD__);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
