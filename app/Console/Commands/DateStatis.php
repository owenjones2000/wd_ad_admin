<?php

namespace App\Console\Commands;

use App\Models\Advertise\Asset;
use App\Models\Advertise\Impression;
use App\Models\ChannelCpm;
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
    protected $signature = 'admin:statis {start_day?} {end_day?}';

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
        $start_day = $this->argument('start_day');
        $end_day = $this->argument('end_day');
        if (empty($start_day)) {
            $start_date = date('Ymd');
        } else {
            $start_date = date('Ymd', strtotime("-{$start_day} day"));
        }
        if (empty($end_day)) {
            $end_date = date('Ymd');
        } else {
            $end_date = date('Ymd', strtotime("-{$end_day} day"));
        }
        Log::info('start_date' . $start_date);
        Log::info('end_date' . $end_date);
        dump('start_date' . $start_date);
        dump('end_date' . $end_date);
        // Request
        $request_query = \App\Models\Advertise\Request::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date);

        $total_request = $request_query->select([
            DB::raw('count(DISTINCT idfa) as total_device_count'),
            DB::raw('count(*) as total'),
            'date',
        ])
        ->groupBy('date')->get()->toArray();
        $idfa_request = $request_query->where('idfa', '00000000-0000-0000-0000-000000000000')
        ->select([
            DB::raw('count(DISTINCT ip) as no_idfa_count'),
            DB::raw('count(*) as total_no_idfa'),
            'date'
        ])->groupBy('date')->get()->toArray();
        dd($total_request, $idfa_request);
        // dd($impression_list);
       

        Log::info('finish'.__METHOD__);
    }
}
