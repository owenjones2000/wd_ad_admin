<?php

namespace App\Jobs;

use App\Models\Idfa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AudienceRedis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tag_id;
    
    protected $app_ids;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tagId, $appIds)
    {
        $this->tag_id = $tagId;
        $this->app_ids = $appIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        $id = $this->tag_id;
        $newappIds = $this->app_ids;
        Log::info('AudienceRedis   tagid ' . $id);
        Log::info($newappIds);
        $idfas = Idfa::where('tag_id', $id)->chunk(10000, function ($idfas) use ($newappIds) {
            $redisValue = $idfas->pluck('idfa');
            Log::info(count($redisValue));
            foreach ($newappIds as $key => $id) {
                // $redisRes = Redis::connection('default')->sadd('app_audience_blocklist_' . $id, ...$redisValue);
                $redisRes = Redis::connection('feature')->sadd('app_audience_blocklist_' . $id, ...$redisValue);
                Log::info('redisres  ' . $redisRes . '   appid ' . $id);
            }
        });
        Log::info('AudienceRedis end');
    }
}
