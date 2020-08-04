<?php

namespace App\Console\Commands;

use App\Models\Advertise\Ad;
use App\Models\Advertise\App;
use App\Models\Advertise\Asset;
use App\Models\Advertise\Campaign;
use App\Models\Advertise\Channel;
use App\Models\Advertise\Impression;
use App\Models\ChannelCpm;
use App\Models\Statis;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DateStatis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:statis {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'statis';

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
        $dateStr = $this->argument('date');
        if (empty($dateStr)) {
            $date = date('Ymd');
        } else {
            $date = date('Ymd', strtotime($dateStr));
        }

        Log::info('date' . $date);
        dump('date' . $date);
        // Request
        $request_query = \App\Models\Advertise\Request::multiTableQuery(function ($query) use ($date) {
            $query->where('date', $date);
            return $query;
        }, $date, $date);

        $total_request = $request_query->select([
            DB::raw('count(DISTINCT idfa) as uq_idfa'),
            DB::raw('count(*) as total'),
        ])
            ->first()->toArray();
        $idfa_request = $request_query->where('idfa', '00000000-0000-0000-0000-000000000000')
            ->select([
                DB::raw('count(DISTINCT ip) as uq_no_idfa'),
                // DB::raw('count(*) as total_no_idfa'),
            ])->first()->toArray();

        // dd($total_request, $idfa_request);
        $devices =  $total_request['uq_idfa'] + $idfa_request['uq_no_idfa'];
        $request_avg  = $devices ? $total_request['total'] / $devices : 0;
        // Impression
        $impression_query = \App\Models\Advertise\Impression::multiTableQuery(function ($query) use ($date) {
            $query->where('date', $date);
            return $query;
        }, $date, $date);

        $total_impression = $impression_query->select([
            DB::raw('count(*) / count(DISTINCT idfa) as impression_avg'),
        ])->first()->toArray();
        // Clicks
        $click_query = \App\Models\Advertise\Click::multiTableQuery(function ($query) use ($date) {
            $query->where('date', $date);
            return $query;
        }, $date, $date);

        $total_click = $click_query->select([
            DB::raw('count(*) / count(DISTINCT idfa) as click_avg'),
        ])->first()->toArray();
        // Installs
        $install_query = \App\Models\Advertise\Install::multiTableQuery(function ($query) use ($date) {
            $query->where('date', $date);
            return $query;
        }, $date, $date);

        $total_install = $install_query->select([
            DB::raw('count(*) / count(DISTINCT idfa) as install_avg'),
        ])->first()->toArray();

        //add
        $begin = Carbon::parse($date ?? 'now')->startOfDay();
        $end = Carbon::parse($date ?? 'now')->endOfDay();
        $newapp = App::query()->whereBetween('created_at', [$begin, $end])->count();
        $newchannel = Channel::query()->whereBetween('created_at', [$begin, $end])->count();
        $newcampaign = Campaign::query()->whereBetween('created_at', [$begin, $end])->count();
        $newad = Ad::query()->whereBetween('created_at', [$begin, $end])->count();
        Statis::updateOrCreate([
            'date' => $date,
        ], [
            'statis' => [
                'uq_idfa' => $total_request['uq_idfa'],
                'uq_no_idfa' => $idfa_request['uq_no_idfa'],
                'request_avg' => $request_avg,
                'impression_avg' => $total_impression['impression_avg']??0,
                'click_avg' => $total_click['click_avg'] ?? 0,
                'install_avg' => $total_install['install_avg'] ?? 0,
                'newapp' => $newapp,
                'newchannel' => $newchannel,
                'newcampaign' => $newcampaign,
                'newad' => $newad,
            ]
        ]);


        Log::info('finish' . __METHOD__);
    }
}
