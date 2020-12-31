<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        if(App::environment('local')){
            DB::listen(function ($query) {
                // $query->sql
                // $query->bindings
                // $query->time
                if ($query->time >3){
                    $sql = $query->sql;
                    if (!Arr::isAssoc($query->bindings)) {
                        foreach ($query->bindings as $key => $value) {
                            $sql = Str::replaceFirst('?', "'{$value}'", $sql);
                        }
                    }
                    Log::info(sprintf('[%s] %s', $query->time, $sql));
                }
            });
        }
    }
}
