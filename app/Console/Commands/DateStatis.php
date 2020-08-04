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
            DB::raw('count(DISTINCT idfa) as total_uq_idfa'),
            DB::raw('count(*) as total'),
        ])
        ->first()->toArray();
        $idfa_request = $request_query->where('idfa', '00000000-0000-0000-0000-000000000000')
        ->select([
            DB::raw('count(DISTINCT ip) as no_idfa_count'),
            DB::raw('count(*) as total_no_idfa'),
        ])->first()->toArray();
        dd($total_request, $idfa_request);
        // dd($impression_list);
       

        Log::info('finish'.__METHOD__);
    }
}
