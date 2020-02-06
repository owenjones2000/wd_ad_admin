<?php

namespace App\Http\Controllers\Api;

use App\Models\Advertise\AdvertiseKpi;
use App\Models\Advertise\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class StatisController extends Controller
{
    public function total(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0]??'now'));
        $end_date = date('Ymd', strtotime($range_date[1]??'now'));

        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
                ->select(['requests', 'impressions', 'clicks', 'installations', 'spend',
                    'app_id', 'campaign_id', 'ad_id', 'target_app_id', 'country'
                ])
            ;
            return $query;
        }, $start_date, $end_date);

        $advertise_kpi_query->select([
            DB::raw('count(DISTINCT app_id) as apps'),
            DB::raw('count(DISTINCT campaign_id) as campaigns'),
            DB::raw('count(DISTINCT ad_id) as ads'),
            DB::raw('count(DISTINCT target_app_id) as channels'),
            DB::raw('count(DISTINCT country) as countries'),

            DB::raw('sum(requests) as requests'),
            DB::raw('sum(impressions) as impressions'),
            DB::raw('sum(clicks) as clicks'),
            DB::raw('sum(installations) as installs'),
            DB::raw('round(sum(clicks) * 100 / sum(impressions), 2) as ctr'),
            DB::raw('round(sum(installations) * 100 / sum(clicks), 2) as cvr'),
            DB::raw('round(sum(installations) * 100 / sum(impressions), 2) as ir'),
            DB::raw('round(sum(spend), 2) as spend'),
            DB::raw('round(sum(spend) / sum(installations), 2) as ecpi'),
            DB::raw('round(sum(spend) * 1000 / sum(impressions), 2) as ecpm'),
        ]);

        $advertise_kpi_list = $advertise_kpi_query->paginate();

        return JsonResource::collection($advertise_kpi_list);
    }

    public function device(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0]??'now'));
        $end_date = date('Ymd', strtotime($range_date[1]??'now'));

//        $start_time = date('Y-m-d 00:00:00', strtotime($range_date[0]??'now'));
//        $end_time = date('Y-m-d 23:59:59', strtotime($range_date[1]??'now'));

        // Device
//        $device_query = Device::query()
//            // ->whereBetween('created_at', [$start_time, $end_time])
//        ;
//
//        $total_device = $device_query->select([
//            DB::raw('count(1) as total_device_count'),
//        ])->first()->toArray();

        // Request
        $request_query = \App\Models\Advertise\Request::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date);

        $total_request = $request_query->select([
            DB::raw('count(DISTINCT idfa) as total_device_count'),
            DB::raw('count(1) / count(DISTINCT idfa) as request_avg'),
        ])->first()->toArray();

        // Impression
        $impression_query = \App\Models\Advertise\Impression::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date);

        $total_impression = $impression_query->select([
            DB::raw('count(1) / count(DISTINCT idfa) as impression_avg'),
        ])->first()->toArray();
        // Clicks
        $click_query = \App\Models\Advertise\Click::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date);

        $total_click = $click_query->select([
            DB::raw('count(1) / count(DISTINCT idfa) as click_avg'),
        ])->first()->toArray();
        // Installs
        $install_query = \App\Models\Advertise\Install::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date);

        $total_install = $install_query->select([
            DB::raw('count(1) / count(DISTINCT idfa) as install_avg'),
        ])->first()->toArray();

        return new JsonResource([array_merge(/*$total_device,*/ $total_request, $total_impression, $total_click, $total_install)]);
    }

    public function deviceByChannel(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0]??'now'));
        $end_date = date('Ymd', strtotime($range_date[1]??'now'));

//        $start_time = date('Y-m-d 00:00:00', strtotime($range_date[0]??'now'));
//        $end_time = date('Y-m-d 23:59:59', strtotime($range_date[1]??'now'));

        // Device
//        $device_query = Device::query()
//            // ->whereBetween('created_at', [$start_time, $end_time])
//        ;
//
//        $total_device = $device_query->select([
//            DB::raw('count(1) as total_device_count'),
//        ])->first()->toArray();

        // Request
        $avg_request_query = \App\Models\Advertise\Request::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date)
            ->select([
                DB::raw('count(DISTINCT idfa) as total_device_count'),
                DB::raw('count(1) / count(DISTINCT idfa) as request_avg'),
            ]);

        $avg_request_query->addSelect('target_app_id')->groupBy('target_app_id')->with('channel');

        $avg_request_list = $avg_request_query->paginate();
        $target_app_id_list = array_column($avg_request_list->items(), 'target_app_id');
        // Impression
        $avg_impression_query = \App\Models\Advertise\Impression::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date)
            ->where(function($query) use($target_app_id_list) {
                $query->whereIn('target_app_id', $target_app_id_list)
                    ->orWhereNull('target_app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as impression_avg'),
            ]);

        $avg_impression_query->addSelect('target_app_id')->groupBy('target_app_id')->with('app');

        $avg_impression_list = $avg_impression_query->pluck('impression_avg', 'target_app_id')->toArray();
        // Clicks
        $avg_click_query = \App\Models\Advertise\Click::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date)
            ->where(function($query) use($target_app_id_list) {
                $query->whereIn('target_app_id', $target_app_id_list)
                    ->orWhereNull('target_app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as click_avg'),
            ]);

        $avg_click_query->addSelect('target_app_id')->groupBy('target_app_id')->with('app');

        $avg_click_list = $avg_click_query->pluck('click_avg', 'target_app_id')->toArray();
        // Installs
        $avg_install_query = \App\Models\Advertise\Install::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date)
            ->where(function($query) use($target_app_id_list) {
                $query->whereIn('target_app_id', $target_app_id_list)
                    ->orWhereNull('target_app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as install_avg'),
            ]);

        $avg_install_query->addSelect('target_app_id')->groupBy('target_app_id')->with('app');

        $avg_install_list = $avg_install_query->pluck('install_avg', 'target_app_id')->toArray();

        foreach($avg_request_list as &$avg_request){
            $avg_request['impression_avg'] = $avg_impression_list[$avg_request['target_app_id']] ?? 0;
            $avg_request['click_avg'] = $avg_click_list[$avg_request['target_app_id']] ?? 0;
            $avg_request['install_avg'] = $avg_install_list[$avg_request['target_app_id']] ?? 0;

        }
        return new JsonResource($avg_request_list);
    }

    public function deviceByApp(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0]??'now'));
        $end_date = date('Ymd', strtotime($range_date[1]??'now'));

//        $start_time = date('Y-m-d 00:00:00', strtotime($range_date[0]??'now'));
//        $end_time = date('Y-m-d 23:59:59', strtotime($range_date[1]??'now'));

        // Device
//        $device_query = Device::query()
//            // ->whereBetween('created_at', [$start_time, $end_time])
//        ;
//
//        $total_device = $device_query->select([
//            DB::raw('count(1) as total_device_count'),
//        ])->first()->toArray();

        // Request
        $avg_request_query = \App\Models\Advertise\Request::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date)
            ->select([
                DB::raw('count(DISTINCT idfa) as total_device_count'),
                DB::raw('count(1) / count(DISTINCT idfa) as request_avg'),
            ]);

        $avg_request_query->addSelect('app_id')->groupBy('app_id')->with('app');

        $avg_request_list = $avg_request_query->paginate();
        $app_id_list = array_column($avg_request_list->items(), 'app_id');
        // Impression
        $avg_impression_query = \App\Models\Advertise\Impression::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date)
            ->where(function($query) use($app_id_list) {
                $query->whereIn('app_id', $app_id_list)
                    ->orWhereNull('app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as impression_avg'),
            ]);

        $avg_impression_query->addSelect('app_id')->groupBy('app_id')->with('app');

        $avg_impression_list = $avg_impression_query->pluck('impression_avg', 'app_id')->toArray();
        // Clicks
        $avg_click_query = \App\Models\Advertise\Click::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date)
            ->where(function($query) use($app_id_list) {
                $query->whereIn('app_id', $app_id_list)
                    ->orWhereNull('app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as click_avg'),
        ]);

        $avg_click_query->addSelect('app_id')->groupBy('app_id')->with('app');

        $avg_click_list = $avg_click_query->pluck('click_avg', 'app_id')->toArray();
        // Installs
        $avg_install_query = \App\Models\Advertise\Install::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date)
            ->where(function($query) use($app_id_list) {
                $query->whereIn('app_id', $app_id_list)
                    ->orWhereNull('app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as install_avg'),
        ]);

        $avg_install_query->addSelect('app_id')->groupBy('app_id')->with('app');

        $avg_install_list = $avg_install_query->pluck('install_avg', 'app_id')->toArray();

        foreach($avg_request_list as &$avg_request){
            $avg_request['impression_avg'] = $avg_impression_list[$avg_request['app_id']] ?? 0;
            $avg_request['click_avg'] = $avg_click_list[$avg_request['app_id']] ?? 0;
            $avg_request['install_avg'] = $avg_install_list[$avg_request['app_id']] ?? 0;

        }
        return new JsonResource($avg_request_list);
    }

}
