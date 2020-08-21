<?php

namespace App\Console\Commands;

use App\Laravue\Models\User;
use App\Models\Advertise\Account;
use App\Models\Advertise\App;
use App\Models\Advertise\Click;
use App\Models\CallbackInstallation;
use App\Models\Credit;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppDetect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:appdetect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '应用检测';

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
            ->where('status', 1)
            ->get()->shuffle();
        try {
            $client = new Client();
            foreach ($apps as $app) {
                switch ($app->os) {
                    case 'android':
                        
                        // $res = $client->get("https://itunes.apple.com/lookup?id=1524898135");
                        $res = $client->get("https://play.google.com/store/apps/details", [
                            'http_errors' => false,
                            'query' => [
                                'id' => $app->bundle_id
                            ]
                        ]);
                        $code = $res->getStatusCode();
                        if ($code == 404){
                            Log::error('app android'. $app->id. 'removal');
                            $app->status =0;
                            $app->is_admin_disable =1;
                            $app->save();
                        }
                        break;
                    case 'ios':
                        $res = $client->get("https://itunes.apple.com/lookup", [
                            'http_errors' => false,
                            'query' => [
                                'id' => substr($app->app_id, 2)
                            ]
                        ]);
                        $content = $res->getBody()->getContents();
                        $data = json_decode($content, true);
                        if (!is_array($data) && $data['resultCount']<1){
                            Log::error('app  ios' . $app->id . 'removal');
                            $app->status = 0;
                            $app->is_admin_disable = 1;
                            $app->save();
                        }
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }
            Log::info('finish' . __METHOD__);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
