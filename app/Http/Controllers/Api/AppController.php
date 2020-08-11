<?php

namespace App\Http\Controllers\Api;

use App\Models\Advertise\AdvertiseKpi;
use App\Models\Advertise\App;
use App\Models\Advertise\Channel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Advertise\Campaign;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class AppController extends Controller
{
    public function list(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));
        $order_by = explode('.', $request->get('field', 'status'));
        $order_sort = $request->get('order', 'desc');

        $app_base_query = App::query();
        if (!empty($request->get('keyword'))) {
            $like_keyword = '%' . $request->get('keyword') . '%';
            $app_base_query->where('name', 'like', $like_keyword);
            $app_base_query->orWhereHas('advertiser', function ($query) use ($like_keyword) {
                $query->where('realname', 'like', $like_keyword);
            });
        }
        if ($request->input('os')) {
            $os = $request->input('os');
            $app_base_query->where('os', $os);
        }
        $country = $request->input('country');
        $type = $request->input('type');
        $app_id_query = clone $app_base_query;
        $app_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function ($query) use (
            $start_date,
            $end_date,
            $app_id_query,
            $type,
            $country
        ) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('app_id', $app_id_query)
                ->when($country, function ($query) use ($country) {
                    $query->where('country', $country);
                })
                ->when($type, function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->select([
                    'requests', 'impressions', 'clicks', 'installations', 'spend',
                    'app_id',
                ]);
            return $query;
        }, $start_date, $end_date);

        $advertise_kpi_query->select([
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
            'app_id',
        ]);
        $advertise_kpi_query->groupBy('app_id');
        if ($order_by[0] === 'kpi' && isset($order_by[1])) {
            $advertise_kpi_query->orderBy($order_by[1], $order_sort);
        }

        $advertise_kpi_list = $advertise_kpi_query
            ->orderBy('spend', 'desc')
            ->get()
            ->keyBy('app_id')
            ->toArray();
        $order_by_ids = implode(',', array_reverse(array_keys($advertise_kpi_list)));
        $app_query = clone $app_base_query;
        if (!empty($order_by_ids)) {
            $app_query->orderByRaw(DB::raw("FIELD(id,{$order_by_ids}) desc"));
        }
        if ($order_by[0] !== 'kpi') {
            $app_query->orderBy($order_by[0], $order_sort);
        }
        $app_list = $app_query->with('advertiser')->paginate($request->get('limit', 30));

        foreach ($app_list as &$app) {
            if (isset($advertise_kpi_list[$app['id']])) {
                $app->kpi = $advertise_kpi_list[$app['id']];
            }
        }
        return JsonResource::collection($app_list);
    }

    public function data(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));
        $group_by = $request->get('grouping');

        $app_base_query = App::query();
        if (!empty($request->get('keyword'))) {
            $app_base_query->where('name', 'like', '%' . $request->get('name') . '%');
        }
        if (!empty($request->get('id'))) {
            $app_base_query->where('id', $request->get('id'));
        }
        $app_id_query = clone $app_base_query;
        $app_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function ($query) use ($start_date, $end_date, $app_id_query) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('app_id', $app_id_query)
                ->select([
                    'date', 'requests', 'impressions', 'clicks', 'installations', 'spend',
                    'app_id',
                ]);
            return $query;
        }, $start_date, $end_date);

        $advertise_kpi_query->select([
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
            'app_id',
        ]);
        if ($group_by == 'date') {
            $advertise_kpi_query->addSelect('date');
            $advertise_kpi_query->groupBy($group_by);
            $advertise_kpi_query->orderByDesc('date');
        } else {
            $advertise_kpi_query->groupBy('app_id');
        }

        $advertise_kpi_list = $advertise_kpi_query
            ->orderBy('spend', 'desc')
            ->get();

        return JsonResource::collection($advertise_kpi_list);
    }

    public function channel(Request $request, $app_id)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));

        $channel_base_query = Channel::query();
        if (!empty($request->get('keyword'))) {
            $channel_base_query->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $channel_id_query = clone $channel_base_query;
        $channel_id_query->select('id');
        $type = $request->input('type');
        $country = $request->input('country');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function ($query) use (
            $start_date,
            $end_date,
            $channel_id_query,
            $type,
            $country,
            $app_id
        ) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('target_app_id', $channel_id_query)
                ->where('app_id', $app_id)
                ->when($type, function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->when($country, function ($query) use ($country) {
                    $query->where('country', $country);
                })
                ->select([
                    'requests', 'impressions', 'clicks', 'installations', 'spend',
                    'target_app_id',
                ]);
            return $query;
        }, $start_date, $end_date);

        $advertise_kpi_query->select([
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
            'target_app_id',
        ]);
        $advertise_kpi_query->groupBy('target_app_id');

        $advertise_kpi_list = $advertise_kpi_query
            ->orderBy('spend', 'desc')
            ->get()
            ->keyBy('target_app_id')
            ->toArray();
        $channel_id_list = array_reverse(array_keys($advertise_kpi_list));
        $order_by_ids = implode(',', $channel_id_list);
        $channel_query = clone $channel_base_query;
        $channel_query->whereIn('id', $channel_id_list);
        if (!empty($order_by_ids)) {
            $channel_query->orderByRaw(DB::raw("FIELD(id,{$order_by_ids}) desc"));
        }
        $channel_list = $channel_query->orderBy($request->get('field', 'name'), $request->get('order', 'desc'))
            ->paginate($request->get('limit', 30));

        foreach ($channel_list as $index => &$channel) {
            if (isset($advertise_kpi_list[$channel['id']])) {
                $channel->kpi = $advertise_kpi_list[$channel['id']];
            }
        }
        return JsonResource::collection($channel_list);
    }

    public function campaign(Request $request, $app_id)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));

        $channel_base_query = Campaign::query();
        if (!empty($request->get('keyword'))) {
            $channel_base_query->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $channel_id_query = clone $channel_base_query;
        $channel_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function ($query) use ($start_date, $end_date, $channel_id_query, $app_id) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('campaign_id', $channel_id_query)
                ->where('app_id', $app_id)
                ->select([
                    'requests', 'impressions', 'clicks', 'installations', 'spend',
                    'campaign_id',
                ]);
            return $query;
        }, $start_date, $end_date);

        $advertise_kpi_query->select([
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
            'campaign_id',
        ]);
        $advertise_kpi_query->groupBy('campaign_id');

        $advertise_kpi_list = $advertise_kpi_query
            ->orderBy('spend', 'desc')
            ->get()
            ->keyBy('campaign_id')
            ->toArray();
        $channel_id_list = array_reverse(array_keys($advertise_kpi_list));
        $order_by_ids = implode(',', $channel_id_list);
        $channel_query = clone $channel_base_query;
        $channel_query->with('advertiser',  'audience');
        $channel_query->whereIn('id', $channel_id_list);
        if (!empty($order_by_ids)) {
            $channel_query->orderByRaw(DB::raw("FIELD(id,{$order_by_ids}) desc"));
        }
        $channel_list = $channel_query->orderBy($request->get('field', 'name'), $request->get('order', 'desc'))
            ->paginate($request->get('limit', 30));

        foreach ($channel_list as $index => &$channel) {
            if (isset($advertise_kpi_list[$channel['id']])) {
                $channel->kpi = $advertise_kpi_list[$channel['id']];
            }
        }
        return JsonResource::collection($channel_list);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //    public function save(Request $request, $id = null)
    //    {
    //        $this->validate($request,[
    //            'name'  => 'required|string|unique:a_app,name,'.$id,
    //            'bundle_id'  => 'required|unique:a_app,bundle_id,'.$id,
    //        ]);
    //        try{
    //            $params = $request->all();
    //            $params['id'] = $id;
    //            App::Make(Auth::user(), $params);
    //            return redirect(route('advertise.app.edit', [$id]))->with(['status'=>'更新成功']);
    //        } catch(BizException $ex){
    //            return redirect(route('advertise.app.edit', [$id]))->withErrors(['status'=>$ex->getMessage()]);
    //        }
    //    }

    /**
     * 启动
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function enable($id)
    {
        /** @var App $apps */
        $apps = App::findOrFail($id);
        $apps->enable();
        return response()->json(['code' => 0, 'msg' => 'Successful']);
    }

    /**
     * 停止
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function disable($id)
    {
        /** @var App $apps */
        $apps = App::findOrFail($id);
        $apps->disable();
        return response()->json(['code' => 0, 'msg' => 'Successful']);
    }

    public function enableAudi($id)
    {
        /** @var App $apps */
        $apps = App::findOrFail($id);
        $apps->is_audience = 1;
        $apps->save();
        return response()->json(['code' => 0, 'msg' => 'Enabled']);
    }

    public function disableAudi($id)
    {
        /** @var App $apps */
        $apps = App::findOrFail($id);
        $apps->is_audience = 0;
        $apps->save();
        return response()->json(['code' => 0, 'msg' => 'Enabled']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->get('ids');
        if (empty($ids)) {
            return response()->json(['code' => 1, 'msg' => '请选择删除项']);
        }
        if (App::destroy($ids)) {
            return response()->json(['code' => 0, 'msg' => '删除成功']);
        }
        return response()->json(['code' => 1, 'msg' => '删除失败']);
    }
}
