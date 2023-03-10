<?php

namespace App\Console\Commands;

use App\Models\Advertise\App;
use App\Models\Advertise\Channel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AppTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:apptag';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '应用标签同步';

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
        $apps = App::query()
            ->with(['tags'])
            ->get();
        try {
            foreach ($apps as $key => $app) {
                if ($app->tags) {
                    $channel = Channel::where('bundle_id', $app->bundle_id)->first();
                    if ($channel) {
                        $channel->tags()->sync($app->tags);
                    }
                }
            }
            Log::info('finish' . __METHOD__);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
