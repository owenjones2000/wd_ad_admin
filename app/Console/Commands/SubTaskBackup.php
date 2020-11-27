<?php

namespace App\Console\Commands;

use App\Models\Advertise\Account;
use App\Models\Advertise\AdvertiseKpi;
use Carbon\Carbon;
use Dcat\EasyExcel\Excel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SubTaskBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:subtask-backup {start_date?} {end_date?} {action?}';

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
        $start = Carbon::parse($start_date ?? '-8 day')->format('Ymd');
        $end = Carbon::parse($end_date ?? '-8 day')->format('Ymd');
        $this->bar = $this->output->createProgressBar($end - $start);
        $this->bar->start();
        $action = $this->argument('action')??'back';
        switch ($action) {
            case 'back':
                $this->backUpSubtask($start, $end);
                break;
            case 'bak':
                $this->backUpBak($start, $end);
                break;
            default:
                # code...
                break;
        }

        $this->bar->finish();
    }

    public function backUpSubtask($start, $end)
    {
        // $storeName = 'y_sub_tasks_' . Carbon::now()->subMonth()->format('Ym');
        // $templateName = 'zz_sub_tasks';
        for ($i = $start; $i <= $end; $i++) {
            dump($i);
            $tableName = 'z_sub_tasks_' . $i;
            // $storeName = 'z_sub_tasks_' . $i . '_bak';
            if (Schema::connection('mysql')->hasTable($tableName) == true) {
                // DB::connection()->statement("create table $storeName like $templateName");
                // DB::connection()->statement("INSERT INTO $storeName SELECT * FROM $tableName");
                $res = $this->backToAws($tableName, $tableName);
                if ($res) {
                    DB::table($tableName)->where('requests', '=', 0)->delete();
                    DB::statement("optimize table $tableName");
                }
            }

            $this->bar->advance();
        }
    }

    public function backUpBak($start, $end)
    {
        for ($i = $start; $i <= $end; $i++) {
            dump($i);
            $tableName = 'z_sub_tasks_' . $i;
            $storeName = 'z_sub_tasks_' . $i . '_bak';
            if (Schema::connection('mysql')->hasTable($storeName) == true) {
                
                $res = $this->backToAws($storeName, $tableName);
                if ($res) {
                    DB::statement("DROP table $storeName");
                }
            }

            $this->bar->advance();
        }
    }
    public function backToAws($tableName, $storeName)
    {
        $back = AdvertiseKpi::from($tableName)
            // ->get()
            ->cursor()
            ->getIterator();
        // ->toArray();
        $res = Excel::csv($back)->disk(Storage::disk('backup'))
            ->store("db-autobackup/$storeName.csv");
        unset($back);
        return $res;
    }
}
