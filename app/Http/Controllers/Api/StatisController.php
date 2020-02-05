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

        $group_by = $request->get('group');
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
        }, $start_date, $end_date)
            ->select([
                DB::raw('count(DISTINCT idfa) as total_device_count'),
                DB::raw('count(1) / count(DISTINCT idfa) as request_avg'),
        ]);

        $request_query->addSelect('target_app_id')->groupBy('target_app_id')->with('channel');

        $total_request_list = $request_query->get()->keyBy('target_app_id')->toArray();

        // Impression
        $impression_query = \App\Models\Advertise\Impression::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date)
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as impression_avg'),
        ]);

        $impression_query->addSelect('target_app_id')->groupBy('target_app_id')->with('channel');

        $total_impression_list = $impression_query->get()->keyBy('target_app_id')->toArray();
        // Clicks
        $click_query = \App\Models\Advertise\Click::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date)->select([
            DB::raw('count(1) / count(DISTINCT idfa) as click_avg'),
        ]);

        $click_query->addSelect('target_app_id')->groupBy('target_app_id')->with('channel');

        $total_click_list = $click_query->get()->keyBy('target_app_id')->toArray();
        // Installs
        $install_query = \App\Models\Advertise\Install::multiTableQuery(function($query) use($start_date, $end_date){
            $query->whereBetween('date', [$start_date, $end_date])
            ;
            return $query;
        }, $start_date, $end_date)->select([
            DB::raw('count(1) / count(DISTINCT idfa) as install_avg'),
        ]);

        $install_query->addSelect('target_app_id')->groupBy('target_app_id')->with('channel');

        $total_install_list = $install_query->get()->keyBy('target_app_id')->toArray();

        $result = [];
        foreach($total_request_list as $total_request){
            $result[$total_request['target_app_id']] = array_merge(
                $total_request_list[$total_request['target_app_id']] ?? [],
                $total_impression_list[$total_request['target_app_id']] ?? [],
                $total_click_list[$total_request['target_app_id']] ?? [],
                $total_install_list[$total_request['target_app_id']] ?? []
            );
        }
        return new JsonResource($result);
    }

}
