<?php

namespace App\Http\Controllers\Api;

use App\Models\Advertise\AdvertiseKpi;
use App\Models\Advertise\Device;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Advertise\Ad;
use App\Models\Advertise\App;
use App\Models\Advertise\Campaign;
use App\Models\Advertise\Channel;
use App\Models\Statis;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class StatisController extends Controller
{
    public function total(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));
        $group_by = $request->get('grouping');
        $order = $request->get('order', 'desc');
        $os = $request->input('os');
        $channelIds = null;
        if ($os) {
            $channelIds = Channel::where('platform', $os)->select('id');
        }
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function ($query) use ($start_date, $end_date, $channelIds) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->select([
                    'date', 'requests', 'impressions', 'clicks', 'installations', 'spend',
                    'app_id', 'campaign_id', 'ad_id', 'target_app_id', 'country'
                ]);
            if ($channelIds) {
                $query->whereIn('target_app_id', $channelIds);
            }
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
        if ($group_by) {
            $advertise_kpi_query->addSelect('date');
            $advertise_kpi_query->groupBy('date');
            $advertise_kpi_query->orderBy('date', $order);
        }

        $advertise_kpi_list = $advertise_kpi_query->paginate();

        return JsonResource::collection($advertise_kpi_list);
    }

    public function newAdd(Request $request)
    {
        $range_date = $request->get('daterange');
        $begin = Carbon::parse($range_date[0] ?? 'now')->startOfDay();
        $end = Carbon::parse($range_date[1] ?? 'now')->endOfDay();
        $newapp = App::query()->whereBetween('created_at', [$begin, $end])->count();
        $newchannel = Channel::query()->whereBetween('created_at', [$begin, $end])->count();
        $newcampaign = Campaign::query()->whereBetween('created_at', [$begin, $end])->count();
        $newad = Ad::query()->whereBetween('created_at', [$begin, $end])->count();

        return response()->json([
            'newapp'  => $newapp,
            'newchannel' => $newchannel, 
            'newcampaign' => $newcampaign,
            'newad' => $newad,
            // 'newapp'  => 100,
            // 'newchannel' => 80, 
            // 'newcampaign' => 1000,
            // 'newad' => 46,
        ]);
    }

    public function group(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));

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
        $count_request_query = \App\Models\Advertise\Request::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query_table = $query->from;
            $query->leftJoin('p_devices', "{$query_table}.idfa", '=', 'p_devices.idfa');
            $query->select("{$query_table}.idfa", 'ending_frame_group');
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->select([
                DB::raw('count(DISTINCT idfa) as total_device_count'),
                DB::raw('count(*) as request_count'),
            ]);

        $count_request_query->addSelect('ending_frame_group')->groupBy('ending_frame_group');

        $count_request_list = $count_request_query->paginate();
        // Impression
        $count_impression_query = \App\Models\Advertise\Impression::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query_table = $query->from;
            $query->leftJoin('p_devices', "{$query_table}.idfa", '=', 'p_devices.idfa');
            $query->select("{$query_table}.idfa", 'ending_frame_group');
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->select([
                DB::raw('count(*) as impression_count'),
            ]);

        $count_impression_query->addSelect('ending_frame_group')->groupBy('ending_frame_group');

        $count_impression_list = $count_impression_query->pluck('impression_count', 'ending_frame_group')->toArray();
        // Clicks
        $count_click_query = \App\Models\Advertise\Click::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query_table = $query->from;
            $query->leftJoin('p_devices', "{$query_table}.idfa", '=', 'p_devices.idfa');
            $query->select("{$query_table}.idfa", 'ending_frame_group');
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->select([
                DB::raw('count(*) as click_count'),
            ]);

        $count_click_query->addSelect('ending_frame_group')->groupBy('ending_frame_group');

        $count_click_list = $count_click_query->pluck('click_count', 'ending_frame_group')->toArray();
        // Installs
        $count_install_query = \App\Models\Advertise\Install::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query_table = $query->from;
            $query->leftJoin('p_devices', "{$query_table}.idfa", '=', 'p_devices.idfa');
            $query->select("{$query_table}.idfa", 'ending_frame_group', 'target_app_id', 'spend');
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->select([
                DB::raw('count(*) as install_count'),
                DB::raw('round(sum(spend), 2) as total_spend'),
            ]);

        $count_install_query->addSelect('ending_frame_group')->groupBy('ending_frame_group');

        $count_install_list = $count_install_query->get()->keyBy('ending_frame_group')->toArray();

        foreach ($count_request_list as &$count_request) {
            $count_request['impression_count'] = $count_impression_list[$count_request['ending_frame_group']] ?? 0;
            $count_request['click_count'] = $count_click_list[$count_request['ending_frame_group']] ?? 0;
            $count_request['install_count'] = $count_install_list[$count_request['ending_frame_group']]['install_count'] ?? 0;
            $count_request['total_spend'] = $count_install_list[$count_request['ending_frame_group']]['total_spend'] ?? '0.00';
        }
        return new JsonResource($count_request_list);
    }

    public function groupByChannel(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));

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
        $count_request_query = \App\Models\Advertise\Request::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query_table = $query->from;
            $query->leftJoin('p_devices', "{$query_table}.idfa", '=', 'p_devices.idfa');
            $query->select("{$query_table}.idfa", 'ending_frame_group', 'target_app_id');
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->select([
                DB::raw('count(DISTINCT idfa) as total_device_count'),
                DB::raw('count(*) as request_count'),
            ]);

        $count_request_query->addSelect(
            DB::raw('CONCAT_WS(\',\', ending_frame_group, target_app_id) as primaryKey'),
            'target_app_id',
            'ending_frame_group'
        )->groupBy('target_app_id', 'ending_frame_group')->with('channel');

        $count_request_list = $count_request_query->paginate();
        $target_app_id_list = array_column($count_request_list->items(), 'target_app_id');
        // Impression
        $count_impression_query = \App\Models\Advertise\Impression::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query_table = $query->from;
            $query->leftJoin('p_devices', "{$query_table}.idfa", '=', 'p_devices.idfa');
            $query->select("{$query_table}.idfa", 'ending_frame_group', 'target_app_id');
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->where(function ($query) use ($target_app_id_list) {
                $query->whereIn('target_app_id', $target_app_id_list)
                    ->orWhereNull('target_app_id');
            })
            ->select([
                DB::raw('count(*) as impression_count'),
            ]);

        $count_impression_query->addSelect(
            DB::raw('CONCAT_WS(\',\', ending_frame_group, target_app_id) as primaryKey'),
            'ending_frame_group',
            'target_app_id'
        )->groupBy('ending_frame_group', 'target_app_id');

        $count_impression_list = $count_impression_query->pluck('impression_count', 'primaryKey')->toArray();
        // Clicks
        $count_click_query = \App\Models\Advertise\Click::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query_table = $query->from;
            $query->leftJoin('p_devices', "{$query_table}.idfa", '=', 'p_devices.idfa');
            $query->select("{$query_table}.idfa", 'ending_frame_group', 'target_app_id');
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->where(function ($query) use ($target_app_id_list) {
                $query->whereIn('target_app_id', $target_app_id_list)
                    ->orWhereNull('target_app_id');
            })
            ->select([
                DB::raw('count(*) as click_count'),
            ]);

        $count_click_query->addSelect(
            DB::raw('CONCAT_WS(\',\', ending_frame_group, target_app_id) as primaryKey'),
            'ending_frame_group',
            'target_app_id'
        )->groupBy('ending_frame_group', 'target_app_id');

        $count_click_list = $count_click_query->pluck('click_count', 'primaryKey')->toArray();
        // Installs
        $count_install_query = \App\Models\Advertise\Install::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query_table = $query->from;
            $query->leftJoin('p_devices', "{$query_table}.idfa", '=', 'p_devices.idfa');
            $query->select("{$query_table}.idfa", 'ending_frame_group', 'target_app_id', 'spend');
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->where(function ($query) use ($target_app_id_list) {
                $query->whereIn('target_app_id', $target_app_id_list)
                    ->orWhereNull('target_app_id');
            })
            ->select([
                DB::raw('count(*) as install_count'),
                DB::raw('round(sum(spend), 2) as total_spend'),
            ]);

        $count_install_query->addSelect(
            DB::raw('CONCAT_WS(\',\', ending_frame_group, target_app_id) as primaryKey'),
            'ending_frame_group',
            'target_app_id'
        )->groupBy('ending_frame_group', 'target_app_id');

        $count_install_list = $count_install_query->get()->keyBy('primaryKey')->toArray();

        foreach ($count_request_list as &$count_request) {
            $count_request['impression_count'] = $count_impression_list[$count_request['primaryKey']] ?? 0;
            $count_request['click_count'] = $count_click_list[$count_request['primaryKey']] ?? 0;
            $count_request['install_count'] = $count_install_list[$count_request['primaryKey']]['install_count'] ?? 0;
            $count_request['total_spend'] = $count_install_list[$count_request['primaryKey']]['total_spend'] ?? '0.00';
        }
        return new JsonResource($count_request_list);
    }

    public function device(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));

        $devices  = Statis::whereBetween('date', [$start_date, $end_date])->orderBy('date', 'desc')->get()->toArray();
        foreach ($devices as $key => &$value) {
            $value['statis']['request_avg']  = round($value['statis']['request_avg'], 4);
        }

        return new JsonResource($devices);
    }

    public function deviceByChannel(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));

        // Request
        $avg_request_query = \App\Models\Advertise\Request::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date]);
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
        $avg_impression_query = \App\Models\Advertise\Impression::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->where(function ($query) use ($target_app_id_list) {
                $query->whereIn('target_app_id', $target_app_id_list)
                    ->orWhereNull('target_app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as impression_avg'),
            ]);

        $avg_impression_query->addSelect('target_app_id')->groupBy('target_app_id');

        $avg_impression_list = $avg_impression_query->pluck('impression_avg', 'target_app_id')->toArray();
        // Clicks
        $avg_click_query = \App\Models\Advertise\Click::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->where(function ($query) use ($target_app_id_list) {
                $query->whereIn('target_app_id', $target_app_id_list)
                    ->orWhereNull('target_app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as click_avg'),
            ]);

        $avg_click_query->addSelect('target_app_id')->groupBy('target_app_id');

        $avg_click_list = $avg_click_query->pluck('click_avg', 'target_app_id')->toArray();
        // Installs
        $avg_install_query = \App\Models\Advertise\Install::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->where(function ($query) use ($target_app_id_list) {
                $query->whereIn('target_app_id', $target_app_id_list)
                    ->orWhereNull('target_app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as install_avg'),
            ]);

        $avg_install_query->addSelect('target_app_id')->groupBy('target_app_id');

        $avg_install_list = $avg_install_query->pluck('install_avg', 'target_app_id')->toArray();

        foreach ($avg_request_list as &$avg_request) {
            $avg_request['impression_avg'] = $avg_impression_list[$avg_request['target_app_id']] ?? 0;
            $avg_request['click_avg'] = $avg_click_list[$avg_request['target_app_id']] ?? 0;
            $avg_request['install_avg'] = $avg_install_list[$avg_request['target_app_id']] ?? 0;
        }
        return new JsonResource($avg_request_list);
    }

    public function deviceByApp(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));

        // Request
        $avg_request_query = \App\Models\Advertise\Request::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date]);
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
        $avg_impression_query = \App\Models\Advertise\Impression::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->where(function ($query) use ($app_id_list) {
                $query->whereIn('app_id', $app_id_list)
                    ->orWhereNull('app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as impression_avg'),
            ]);

        $avg_impression_query->addSelect('app_id')->groupBy('app_id');

        $avg_impression_list = $avg_impression_query->pluck('impression_avg', 'app_id')->toArray();
        // Clicks
        $avg_click_query = \App\Models\Advertise\Click::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->where(function ($query) use ($app_id_list) {
                $query->whereIn('app_id', $app_id_list)
                    ->orWhereNull('app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as click_avg'),
            ]);

        $avg_click_query->addSelect('app_id')->groupBy('app_id');

        $avg_click_list = $avg_click_query->pluck('click_avg', 'app_id')->toArray();
        // Installs
        $avg_install_query = \App\Models\Advertise\Install::multiTableQuery(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date]);
            return $query;
        }, $start_date, $end_date)
            ->where(function ($query) use ($app_id_list) {
                $query->whereIn('app_id', $app_id_list)
                    ->orWhereNull('app_id');
            })
            ->select([
                DB::raw('count(1) / count(DISTINCT idfa) as install_avg'),
            ]);

        $avg_install_query->addSelect('app_id')->groupBy('app_id');

        $avg_install_list = $avg_install_query->pluck('install_avg', 'app_id')->toArray();

        foreach ($avg_request_list as &$avg_request) {
            $avg_request['impression_avg'] = $avg_impression_list[$avg_request['app_id']] ?? 0;
            $avg_request['click_avg'] = $avg_click_list[$avg_request['app_id']] ?? 0;
            $avg_request['install_avg'] = $avg_install_list[$avg_request['app_id']] ?? 0;
        }
        return new JsonResource($avg_request_list);
    }
}
