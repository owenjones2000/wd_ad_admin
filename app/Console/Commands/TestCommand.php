<?php

namespace App\Console\Commands;

use App\Admin\Service\OceanengineService;
use App\Models\Advertise\Account;
use App\Models\Advertise\Ad;
use App\Models\Advertiser;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
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
        $ad = Ad::find(927);
        Redis::connection('feature')->select(1);
        $imp = Redis::connection('feature')->hget('wudiads_ad_total_impression', $ad->id);
        $ins = Redis::connection('feature')->hget('wudiads_ad_total_installation', $ad->id);
        dump($imp, $ins);
    }
}
