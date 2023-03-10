<?php

namespace App\Jobs;

use App\Models\Advertise\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateBill implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param $account_id
     * @return void
     */
    public function __construct($account_id)
    {
        $this->account_id = $account_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Account $account */
        $account = Account::query()
            ->where('id', $this->account_id)
            ->where('status', 1)
            ->firstOrFail();
        $account->generateBill();
    }

    private $account_id = null;
}
