<?php

namespace App\Console\Commands;

use App\Models\Advertise\Account;
use Illuminate\Console\Command;

class GenerateBill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bill:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate bill';

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
        Account::query()
            ->where('status', 1)
            ->each(function($account){
                dispatch(new \App\Jobs\GenerateBill($account['id']));
            });
    }
}
