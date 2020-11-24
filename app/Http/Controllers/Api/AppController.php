<?php

namespace App\Http\Controllers\Api;

use App\Helper\Helper;
use App\Models\Advertise\AdvertiseKpi;
use App\Models\Advertise\App;
use App\Models\Advertise\Channel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Advertise\Campaign;
use App\Models\AppTag;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $app_base_query->orWhere('id', '=', $request->get('keyword'));
            $app_base_query->orWhereHas('advertiser', function ($query) use ($like_keyword) {
                $query->where('realname', 'like', $like_keyword);
            });
        }
        if ($request->input('os')) {
            $os = $request->input('os');
            $app_base_query->where('os', $os);
        }
        if ($request->input('is_admin_disable')) {
            $app_base_query->where('is_admin_disable', true);
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
            $query
                ->whereBetween('date', [$start_date, $end_date])
                ->whereIn('app_id', $app_id_query)
                ->when($country, function ($query) use ($country) {
                    $query->whereIn('country', $country);
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

    public function appList(Request $request)
    {
        $app_base_query = App::query();
        if (!empty($request->get('keyword'))) {
            $like_keyword = '%' . $request->get('keyword') . '%';
            $app_base_query->where('name', 'like', $like_keyword);
            $app_base_query->orWhereHas('advertiser', function ($query) use ($like_keyword) {
                $query->where('realname', 'like', $like_keyword);
            });
        }
        $is_admin_disable = $request->input('is_admin_disable');
        if ($is_admin_disable != '') {
            $app_base_query->where('is_admin_disable', $is_admin_disable);
        }

        $apps = $app_base_query->with(['advertiser'])->orderBy('is_admin_disable', 'desc')->orderBy('id', 'desc')->paginate($request->get('limit', 30));
        return JsonResource::collection($apps);
    }
    public function appTagList(Request $request)
    {
        $app_base_query = App::query();
        if (!empty($request->get('keyword'))) {
            $like_keyword = '%' . $request->get('keyword') . '%';
            $app_base_query->where('name', 'like', $like_keyword);
            $app_base_query->orWhereHas('advertiser', function ($query) use ($like_keyword) {
                $query->where('realname', 'like', $like_keyword);
            });
        }

        $apps = $app_base_query->with(['advertiser', 'tags'])
            // ->where('status', 1)
            ->orderBy('id', 'desc')
            ->paginate($request->get('limit', 30));
        return JsonResource::collection($apps);
    }

    public function iosInfo($id)
    {
        $app = App::where('id', $id)->where('os', 'ios')->firstOrFail();
        $appid = substr($app->app_id, 2);
        $client = new Client();
        // $res = $client->get("https://itunes.apple.com/lookup?id=1524898135");
        $res = $client->get("https://itunes.apple.com/lookup", [
            'query' => [
                'id' => $appid
            ]
        ]);
        $content = $res->getBody()->getContents();
        $data = json_decode($content, true);
        return response()->json($data['results'][0] ?? []);
    }
    public function data(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));
        $group_by = $request->get('grouping');

        $app_base_query = App::query();
        if (!empty($request->get('keyword'))) {
            $like_keyword = '%' . $request->get('keyword') . '%';
            $app_base_query->where('name', 'like', $like_keyword);
            $app_base_query->orWhere('id', '=', $request->get('keyword'));
            $app_base_query->orWhereHas('advertiser', function ($query) use ($like_keyword) {
                $query->where('realname', 'like', $like_keyword);
            });
        }
        if (!empty($request->get('id'))) {
            $app_base_query->where('id', $request->get('id'));
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
                    $query->whereIn('country', $country);
                })
                ->when($type, function ($query) use ($type) {
                    $query->where('type', $type);
                })
                ->select([
                    'requests', 'impressions', 'clicks', 'installations', 'spend',
                    'app_id', 'date'
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
        $channel_list = $channel_query
            ->orderBy('id', 'desc')
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
        $order_by = explode('.', $request->get('field', 'status'));
        $order_sort = $request->get('order', 'desc');

        $campaign_base_query = Campaign::query()->where('app_id', $app_id);
        if (!empty($request->get('keyword'))) {
            $campaign_base_query->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $campaign_id_query = clone $campaign_base_query;
        $campaign_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function ($query) use (
            $start_date,
            $end_date,
            $campaign_id_query
        ) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('campaign_id', $campaign_id_query)
                // ->where('app_id', $app_id)
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
        if ($order_by[0] === 'kpi' && isset($order_by[1])) {
            $advertise_kpi_query->orderBy($order_by[1], $order_sort);
        }

        $advertise_kpi_list = $advertise_kpi_query
            ->orderBy('spend', 'desc')
            ->get()
            ->keyBy('campaign_id')
            ->toArray();
        $channel_id_list = array_reverse(array_keys($advertise_kpi_list));
        $order_by_ids = implode(',', $channel_id_list);
        $campaign_query = clone $campaign_base_query;
        $campaign_query->with('advertiser',  'audience');
        // $campaign_query->whereIn('id', $channel_id_list);
        if (!empty($order_by_ids)) {
            $campaign_query->orderByRaw(DB::raw("FIELD(id,{$order_by_ids}) desc"));
        }
        $channel_list = $campaign_query
            ->orderBy('id', 'desc')
            ->paginate($request->get('limit', 30));

        foreach ($channel_list as $index => &$channel) {
            if (isset($advertise_kpi_list[$channel['id']])) {
                $channel->kpi = $advertise_kpi_list[$channel['id']];
            }
        }
        return JsonResource::collection($channel_list);
    }

    public function tagList(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $appTag = AppTag::when($keyword, function ($query) use ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%');
        })
            ->where('status', 1)
            ->where('group', 0)->with('children')
            ->get()->toArray();
        // $tree  = [];
        // $appTag = Helper::ListToTree($appTag, 'id', 'group', 'children', 0, $tree);
        // dd($tree);
        return JsonResource::collection($appTag);
    }
    public function tagAll(Request $request)
    {
        $appTag = AppTag::where('status', 1)
            // ->where('group', 0)->with('children')
            ->get()->toArray();
        // $tree  = [];
        // $tree = Helper::ListToTree($appTag, 'id', 'group', 'children', 0, $tree);
        // dd($tree);
        return JsonResource::collection($appTag);
    }

    public function tagSave(Request $request, $id = null)
    {
        $this->validate($request, [
            'name'  => 'required|string|unique:a_app_tag,name,' . $id . ',id',
        ]);
        try {
            $params = $request->all();
            $params['id'] = $id;
            AppTag::Make($params);
            return response()->json(['code' => 0, 'msg' => 'Successful']);
        } catch (Exception $ex) {
            Log::error($ex);
            return response()->json(['code' => 100, 'msg' => $ex->getMessage()]);
        }
    }

    public function bindTag(Request $request)
    {
        $this->validate($request, [
            'apps' => 'bail|required|array',
            'tags' => 'required|array',
        ]);
        $apps =  $request->input('apps', []);
        $tags = $request->input('tags', []);
        $appTag = AppTag::
            // ->where('group', 0)->with('children')
            get()->keyBy('id')->toArray();
        try {
            foreach ($tags as $key => $tagId) {
                $isOk = Helper::isParaentSel($tagId, $appTag, $tags);
            }

            // Log::info($apps, $tags);

            foreach ($apps as $key => $appid) {
                $app = App::findOrFail($appid);
                $oldTags = $app->tags->pluck('id')->toArray();
                if (array_diff($tags, $oldTags)) {
                    $app->tags()->sync($tags);
                }
            }
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['code' => 100, 'msg' => $e->getMessage()]);
        }

        return response()->json(['code' => 0, 'msg' => 'Successful']);
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
    public function save(Request $request, $id = null)
    {
        $this->validate($request, [
            // 'name'  => 'required|string|unique:a_app,name,' . $id . ',id',
            'name'  => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request, $id) {
                    $taken = App::where('name', $value)->where('os', $request->input('os'))
                        ->where('id', '<>', $id??0)->first();
                    if ($taken) {
                        $fail($attribute . ' has been taken.');
                    }
                },
            ],
            'bundle_id'  => 'required|string',
        ]);
        try {
            $params = $request->all();
            $params['id'] = $id;
            App::Make($params);
            return response()->json(['code' => 0, 'msg' => 'Successful']);
        } catch (Exception $ex) {
            Log::error($ex);
            return response()->json(['code' => 100, 'msg' => $ex->getMessage()]);
        }
    }

    /**
     * 启动
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function enable($id)
    {
        /** @var App $app */
        $app = App::findOrFail($id);
        if ($app->tags->isEmpty()) {
            return response()->json(['code' => 100, 'msg' => 'App Tag Required']);
        }
        $app->enable();
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
        /** @var App $app */
        $app = App::findOrFail($id);
        $app->disable();
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
