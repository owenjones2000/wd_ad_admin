<?php

namespace App\Console\Commands;

use App\Admin\Service\OceanengineService;
use App\Models\Advertise\Account;
use App\Models\Advertise\Ad;
use App\Models\Advertise\App;
use App\Models\Advertiser;
use App\Models\Campaign;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test {function} {param1?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test';
    /**
     * 
     *
     * @var OceanengineService
     */
    protected $ogService;

    const Base_Url = 'https://ad.oceanengine.com/open_api/';
    const Test_Url = 'https://test-ad.toutiao.com/open_api/';
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
        $p = $this->argument('function');
        $name = 'test' . $p;
        call_user_func([$this, $name]);
    }

    public function test1()
    {
        Account::query()
            ->where('status', 1)
            ->each(function ($account) {
                // dump($account);
                $account->generateBill();
            });
    }

    public function test11()
    {
        dd(public_path());
        app()->make('files')->link('/e/material', public_path('material'));
    }

    public function  test2()
    {
        // $ad = Ad::find(927);
        // $redis =  Redis::connection('feature');dump($redis);
        // Redis::connection('feature')->select(1);
        // $imp = Redis::connection('feature')->hget('wudiads_ad_total_impression', 927);
        // $ins = Redis::connection('feature')->hget('wudiads_ad_total_installation', 927);
        // dump($imp, $ins);
        dd((new Ad)->getTable());
    }

    public function test3()
    {
        $code = mt_rand(10000, 99999);
        dump($code);
        $phone = "8618581547568";
        $params = [
            "code"  => $code,
            "phone" => $phone
        ];
        $client = new Client();

        $res = $client->get("http://gocn.luckfun.vip/253code", [
            'query' => $params
        ]);
        $content = $res->getBody()->getContents();
        $data = json_decode($content, true);
        dd($data);
        $result = ["result" => false, "error" => $content];
        if ($data && isset($data['result']) && $data['result'] === true) {
            $result = ["result" => true, "message_id" => $data['message_id']];
        }

    }

    public function test4()
    {
        $client = new Client();
        // $res = $client->get("https://itunes.apple.com/lookup?id=1524898135");
        $res = $client->get("https://itunes.apple.com/lookup",[
            'query' => [
                'id'=> '1524898135'
            ]
        ]);
        $code = $res->getStatusCode();
        $content = $res->getBody()->getContents();
        $data = json_decode($content, true);
        dd($code, $data);
    }

    public function test5()
    {
        $apps = App::query()
            ->where('is_remove', 0)
            ->get()->shuffle();
            $client = new Client();
            foreach ($apps as $app) {
                $key = 'app_removal' . $app->id;
                switch ($app->os) {
                    case 'android':
                        $res = $client->get("https://play.google.com/store/apps/details", [
                            'http_errors' => false,
                            'query' => [
                                'id' => $app->bundle_id
                            ]
                        ]);
                        $code = $res->getStatusCode();
                        if ($code == 404) {
                            Log::error("app  Android $app->id name $app->name account {$app->advertiser->realname} removal");
                            $app->is_remove = 1;
                            $app->save();
                            Redis::del($key);
                        }
                        break;
                    case 'ios':
                        $res = $client->get("https://itunes.apple.com/lookup", [
                            'http_errors' => false,
                            'query' => [
                                'id' => substr($app->app_id, 2)
                            ]
                        ]);
                        $content = $res->getBody()->getContents();
                        $data = json_decode($content, true);
                        if (isset($data['resultCount']) && $data['resultCount'] < 1) {
                            
                            Log::error("app  Ios $app->id name $app->name account {$app->advertiser->realname} removal");
                            $app->is_remove = 1;
                            $app->save();
                            Redis::del($key);
                        }
                        break;
                    default:
                        # code...
                        break;
                }
            }
    }
}
