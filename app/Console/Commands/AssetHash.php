<?php

namespace App\Console\Commands;

use App\Models\Advertise\Asset;
use Illuminate\Console\Command;

class AssetHash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asset:hash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make asset hash';

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
        Asset::query()
            ->where('hash', '')
            ->each(function($asset){
                $file_hash = md5_file($asset['url']);
                $asset['hash'] = $file_hash;
                $asset->save();
            });
    }
}
