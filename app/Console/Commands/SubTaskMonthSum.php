<?php

namespace App\Console\Commands;

use App\Models\Advertise\Account;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SubTaskMonthSum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subtask';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate subtask';

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
        $lastmonthday = Carbon::now()->subMonth()->lastOfMonth()->format('Ymd');
        $firstmonthday = Carbon::now()->subMonth()->firstOfMonth()->format('Ymd');
        for ($i= $firstmonthday; $i <= $lastmonthday; $i++) { 
            $tableName = 'z_sub_tasks_'.$i;
            $storeName = 'y_sub_tasks_'.Carbon::now()->subMonth()->format('Ym');
            $templateName = 'zz_sub_tasks';
            $res = DB::connection()->select("SHOW COLUMNS FROM $templateName");
            $columns = array_column($res, 'Field');
            unset($columns[0]);
            $columns = implode(',', $columns);
            // dd($columns = implode(',',$columns));
            if (Schema::connection('mysql')->hasTable($storeName) == false) {
                DB::connection()->statement("create table $storeName like $templateName");
            }
            DB::connection()->statement("INSERT INTO $storeName($columns) SELECT $columns FROM $tableName");
        }
    }
}
