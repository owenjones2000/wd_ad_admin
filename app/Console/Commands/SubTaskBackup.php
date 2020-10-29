<?php

namespace App\Console\Commands;

use App\Models\Advertise\Account;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubTaskBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:subtask-backup {start_date} {end_date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'subtask backup';

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
        $start_date = $this->argument('start_date');
        $end_date = $this->argument('end_date');
        $start = Carbon::parse($start_date)->format('Ymd');
        $end = Carbon::parse($end_date)->format('Ymd');
        $bar = $this->output->createProgressBar($end- $start);

        $bar->start();
        $storeName = 'y_sub_tasks_' . Carbon::now()->subMonth()->format('Ym');
        $templateName = 'zz_sub_tasks';
        for ($i= $start; $i <= $end; $i++) { 
            dump($i);
            $tableName = 'z_sub_tasks_'.$i;
            $storeName = 'z_sub_tasks_' . $i.'_bak';
            if (Schema::connection('mysql')->hasTable($storeName) == false) {
                DB::connection()->statement("create table $storeName like $templateName");
            }
            DB::connection()->statement("INSERT INTO $storeName SELECT * FROM $tableName");
            DB::table($tableName)->where('requests', '=', 0)->delete();
            DB::statement("optimize table $tableName");
            $bar->advance();
        }
        $bar->finish();
    }
}
