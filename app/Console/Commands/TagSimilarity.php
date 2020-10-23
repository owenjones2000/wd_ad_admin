<?php

namespace App\Console\Commands;

use App\Models\Advertise\App;
use App\Models\Advertise\Channel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TagSimilarity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:tag-similarity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '应用标签相似度';

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
        $channels = Channel::query()->with(['tags'])->get();
        try {
            foreach ($channels as $key => $app) {
                $keyRedis = 'similarity_' . $app->id;
                $redisData  = [];
                foreach ($apps as $key => $channel) {
                    if (!$app->tags->isEmpty() && !$channel->tags->isEmpty()) {
                        // dd($app->toArray(), $channel->toArray());
                        $intersect = $app->tags->intersect($channel->tags);
                        $fenzi = count($intersect);
                        $fenmu = sqrt(count($app->tags)) * sqrt(count($channel->tags));
                        $simi = round($fenzi / $fenmu, 2);

                        $redisData[$channel->id] = $simi;

                        // dd($simi);
                        // dd(count($app->tags), count($channel->tags), count($intersect));
                    }
                }
                if ($redisData) {
                    $res = Redis::connection('feature')->pipeline(function ($pipe) use ($keyRedis,$redisData){
                        foreach ($redisData as $key => $value) {
                            $pipe->zadd($keyRedis, $value, $key);
                        }
                        
                    });
                    dump($app->id .'---->'.count($redisData));
                }
                // Redis::connection('feature')->del($keyRedis);
            }
            Log::info('finish' . __METHOD__);
        } catch (\Exception $e) {
            Log::info('fail' . __METHOD__);
            Log::error($e->getMessage());
        }
    }
}
