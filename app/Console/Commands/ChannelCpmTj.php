<?php

namespace App\Console\Commands;

use App\Models\Advertise\Impression;
use App\Models\ChannelCpm;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChannelCpmTj extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:channel_tj {start_day?} {end_day?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'channel cpm tj';

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
        dump('start_date' .$start_date);
        dump('end_date' . $end_date);
        $impression_query = Impression::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->select([
                    'ecpm',
                    'target_app_id',
                    'date',
                    'country',
                    'type',
                ]);
            return $query;
        }, $start_date, $end_date);
        $impression_list = $impression_query->select([
            DB::raw('round(sum(ecpm)/1000, 2) as cpm'),
            'target_app_id',
            'date',
            'country',
            'type',
        ])->groupBy('target_app_id', 'date', 'country', 'type')
        ->get();
            // ->keyBy('target_app_id')
            // ->toArray();
        // dd($impression_list);
        foreach ($impression_list as $key => $value) {
            ChannelCpm::updateOrCreate([
                'date' => $value->date,
                'target_app_id' => $value->target_app_id,
                'country' => $value->country,
                'type' => $value->type,
            ],[
                'cpm_revenue' => $value->cpm ?? 0,
            ]);
        }
        dump('finish');
        Log::info('finish'.__METHOD__);
    }
}
