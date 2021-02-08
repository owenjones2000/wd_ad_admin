<?php

namespace App\Console\Commands;

use App\Admin\Service\OceanengineService;
use App\Helper\Helper;
use App\Models\Advertise\Account;
use App\Models\Advertise\Ad;
use App\Models\Advertise\App;
use App\Models\Advertise\Bill;
use App\Models\Advertise\BillInfo;
use App\Models\Advertise\BillSet;
use App\Models\Advertiser;
use App\Models\Campaign;
use App\Models\Record;
use Barryvdh\Snappy\Facades\SnappyImage;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

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
        $res = $client->get("https://itunes.apple.com/lookup", [
            'query' => [
                'id' => '1524898135'
            ]
        ]);
        $code = $res->getStatusCode();
        $content = $res->getBody()->getContents();
        $data = json_decode($content, true);
        dd($code, $data);
    }

    public function test5()
    {
        Log::info('start' . __METHOD__);
        $apps = App::query()
            ->where('is_remove', 0)
            ->get();
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
                    dump("app  android $app->id name $app->name result {$code}");
                    if ($code == 404) {
                        Log::error("app  Android $app->id name $app->name account {$app->advertiser->realname} removal");
                        $app->is_remove = 1;
                        $app->save();
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
                    dump("app  Ios $app->id name $app->name result {$data['resultCount']}");
                    if (isset($data['resultCount']) && $data['resultCount'] < 1) {

                        Log::error("app  Ios $app->id name $app->name account {$app->advertiser->realname} removal");
                        $app->is_remove = 1;
                        $app->save();
                    }
                    break;
                default:
                    # code...
                    break;
            }
        }
        Log::info('finish' . __METHOD__);
    }

    public function test6()
    {
        $arr1 = array("banana", "orange");
        $arr2 = array("pitaya", "tomato");
        $con_arr = "banana apple orange banana grape";
        $con_rep = str_replace($arr1, $arr2, $con_arr, $count);
        dump($con_rep);

        // $arr = array("blue", "red", "green", "yellow");
        // $str1 = str_replace("red", "pink", $arr, $i);
        // print_r($str1);
    }

    public function test7()
    {
        // DB::table('y_sub_tasks_202008')->where('impressions', '<=', 0)->delete();
        $res = strpos('https://app.appsflyer.com/id1533932791?c={campaign}&af_c_id={campaign_id}&af_adset={adset}&af_adset_id={adset_id}&af_ad={ad}&af_ad_id={ad_id}&af_siteid={publisher_id}&pid=wudiads_int&af_click_lookback=7d&clickid={clickid}&advertising_id={gaid}&android_id={android_id}&idfa={idfa}&af_ip={ip}&af_ua={ua}&af_lang={language}&redirect=false', 'https://app.appsflyer.com/id1533932791');
        // https://app.appsflyer.com/id1533932791?c={campaign}&af_c_id={campaign_id}&af_adset={adset}&af_adset_id={adset_id}&af_ad={ad}&af_ad_id={ad_id}&af_siteid={publisher_id}&pid=wudiads_int&af_click_lookback=7d&clickid={clickid}&advertising_id={gaid}&android_id={android_id}&idfa={idfa}&af_ip={ip}&af_ua={ua}&af_lang={language}&redirect=false
        // https://app.appsflyer.com/id1533771239?c={campaign}&af_c_id={campaign_id}&af_adset={adset}&af_adset_id={adset_id}&af_ad={ad}&af_ad_id={ad_id}&af_siteid={publisher_id}&pid=wudiads_int&af_click_lookback=7d&clickid={clickid}&advertising_id={gaid}&android_id={android_id}&idfa={idfa}&af_ip={ip}&af_ua={ua}&af_lang={language}&redirect=false
        dd($res);
    }

    

    public function test8()
    {
        // $img = getimagesize(Storage::disk('local')->path('20201222172733.jpg'), $imageinfo);
        // $img = getimagesize(Storage::disk('local')->path('2021011216104360025ffd4da2784b6.png'), $imageinfo);
        // $img = getimagesize(Storage::disk('local')->path('2021011216104534595ffd91d36645a.gif'), $imageinfo);
        // dd($img, $imageinfo);
        // $filePath = Storage::disk('local')->path('2021011216104360025ffd4da2784b6.png');
        // $base64 = Helper::imgToBase64($filePath);
        // $base64 = base64_encode(Storage::disk('local')->get('2021011216104360025ffd4da2784b6.png'));
        // dd($base64);
        // Log::info($base64);
        $bill = Bill::query()->where('id', 105)->with('account')->firstOrFail();
        $billInfo = BillInfo::query()->where('bill_id', 105)->get();
        $prePay = Record::whereBetween('date', [$bill->start_date, $bill->end_date])
        ->where('main_user_id', $bill->main_user_id)->get();
        $billAdr = BillSet::where('id',
            $bill->account->id
        )->first();
        $jpg = SnappyImage::loadView('bill.invoice', ['bill' => $bill, 'billInfo' => $billInfo, 'prePay' => $prePay, 'billAdr' => $billAdr]);
        $invoice_name = 'Invoice_' . $bill['start_date'] . '~' . $bill['end_date'] . '.jpg';
        $filePath = Storage::disk('local')->path($invoice_name);
        $jpg->save($filePath, true);
        $base64 = base64_encode(Storage::disk('local')->get($invoice_name));

        $client = new Client();
        $rep = $client->request("POST", "https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=39ab2517-8650-45d9-84e9-8ff2418817d0", [
            "json" => [
                "msgtype" => "image",
                "image" => [
                    "base64" => $base64,
                    "md5" => md5_file($filePath),
                ]
            ]
        ]);

        dd($rep->getBody()->getContents());
    }

    public function test9()
    {
        $client = new Client();
        //
        $repUp = $client->request("POST", "https://qyapi.weixin.qq.com/cgi-bin/webhook/upload_media?key=39ab2517-8650-45d9-84e9-8ff2418817d0&type=file", [
            'multipart' => [
                [
                    'name'     => 'something',
                    // 'contents' => Storage::disk('local')->get('2021010716100076015ff6c431d9675.mp4'),
                    'contents' => Storage::disk('local')->get('2021011216104360025ffd4da2784b6.png'),
                    'filename' => '122.png',
                ],
            ]
        ]);
        $result = $repUp->getBody()->getContents();
        $resArray = json_decode($result, 1);
        dump($resArray);
        $repSend = $client->request("POST", "https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=39ab2517-8650-45d9-84e9-8ff2418817d0", [
            "json" => [
                "msgtype" => "file",
                "file" => [
                    "media_id" => $resArray['media_id'],
                ]
            ]
        ]);
        dump($repSend->getBody()->getContents());
    }

    public function test10()
    {
        $time = Carbon::now('Asia/Shanghai');
        $time = Carbon::now('GMT');
        $time1 = Carbon::now('GMT+8');
        $time2 = Carbon::now('GMT-8');

        dump($time, $time1, $time2);
    }
}
