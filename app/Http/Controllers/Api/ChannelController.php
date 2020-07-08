<?php

/**
 * File UserController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChannelResource;
use App\Laravue\Models\User;
use App\Models\Advertise\AdvertiseKpi;
use App\Models\Advertise\ApiToken;
use App\Models\Advertise\App;
use App\Models\Advertise\Channel;
use App\Models\Advertise\Impression;
use App\Models\Advertise\Install;
use App\Rules\AdvertiseName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserController
 *
 * @package App\Http\Controllers
 */
class ChannelController extends Controller
{
    const ITEM_PER_PAGE = 15;

    /**
     * Display a listing of the channel resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function list(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));
        $order_by = explode('.', $request->get('field', 'name'));
        $order_sort = $request->get('order', 'desc');

        $channel_base_query = Channel::query();
        if (!empty($request->get('keyword'))) {
            $like_keyword = '%' . $request->get('keyword') . '%';
            $channel_base_query->where('name', 'like', $like_keyword);
            $channel_base_query->orWhereHas('publisher', function ($query) use ($like_keyword) {
                $query->where('realname', 'like', $like_keyword);
            });
        }
        if ($request->input('os')) {
            $os = $request->input('os');
            $channel_base_query->where('platform', $os);
        }
        $channel_id_query = clone $channel_base_query;
        $channel_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function ($query) use ($start_date, $end_date, $channel_id_query) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('target_app_id', $channel_id_query)
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
        if ($order_by[0] === 'kpi' && isset($order_by[1])) {
            $advertise_kpi_query->orderBy($order_by[1], $order_sort);
        }

        $advertise_kpi_list = $advertise_kpi_query
            ->orderBy('spend', 'desc')
            ->get()
            ->keyBy('target_app_id')
            ->toArray();
        $order_by_ids = implode(',', array_reverse(array_keys($advertise_kpi_list)));
        $channel_query = clone $channel_base_query;
        $channel_query->with('publisher');
        if (!empty($order_by_ids)) {
            $channel_query->orderByRaw(DB::raw("FIELD(id,{$order_by_ids}) desc"));
        }
        if ($order_by[0] !== 'kpi') {
            $channel_query->orderBy($order_by[0], $order_sort);
        }
        $channel_list = $channel_query->paginate($request->get('limit', 30));
        //install表统计
        $install_query = Install::multiTableQuery(function ($query) use ($start_date, $end_date, $channel_id_query) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('target_app_id', $channel_id_query)
                ->select([
                    'cost',
                    'spend',
                    'target_app_id',

                ]);
            return $query;
        }, $start_date, $end_date);
        $install_list = $install_query->select([
            DB::raw('round(sum(spend), 2) as spend'),
            DB::raw('round(sum(cost), 2) as cost'),
            'target_app_id',
        ])->groupBy('target_app_id')
            ->get()
            ->keyBy('target_app_id')
            ->toArray();
        foreach ($advertise_kpi_list as $key => &$kpi) {
            $kpi['cost'] = $install_list[$key]['cost'] ?? 0;
        }
        //impression表
        $impression_query = Impression::multiTableQuery(function ($query) use ($start_date, $end_date, $channel_id_query) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('target_app_id', $channel_id_query)
                ->select([
                    'ecpm',
                    'target_app_id',
                ]);
            return $query;
        }, $start_date, $end_date);
        $impression_list = $impression_query->select([
            DB::raw('round(sum(ecpm)/1000, 2) as cpm'),
            'target_app_id',
            ])->groupBy('target_app_id')
            ->get()
            ->keyBy('target_app_id')
            ->toArray();
        foreach ($advertise_kpi_list as $key => &$kpi) {
            $kpi['cpm'] = $impression_list[$key]['cpm'] ?? 0;
        }

        foreach ($channel_list as $channel) {
            if (isset($advertise_kpi_list[$channel['id']])) {
                $channel->kpi = $advertise_kpi_list[$channel['id']];
            }
        }
        return JsonResource::collection($channel_list);
    }

    public function data(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));
        $group_by = $request->get('grouping');

        $channel_base_query = App::query();
        if (!empty($request->get('keyword'))) {
            $channel_base_query->where('name', 'like', '%' . $request->get('name') . '%');
        }
        if (!empty($request->get('id'))) {
            $channel_base_query->where('id', $request->get('id'));
        }
        $channel_id_query = clone $channel_base_query;
        $channel_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function ($query) use ($start_date, $end_date, $channel_id_query) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('target_app_id', $channel_id_query)
                ->select([
                    'date', 'requests', 'impressions', 'clicks', 'installations', 'spend',
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
        $install_kpi_query = Install::multiTableQuery(function ($query) use ($start_date, $end_date, $channel_id_query) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('target_app_id', $channel_id_query)
                ->select([
                    'date', 'cost', 'spend',
                    'target_app_id',
                ]);
            return $query;
        }, $start_date, $end_date)->select([
            DB::raw('round(sum(cost), 2) as cost'),
            'target_app_id',
        ]);

        if ($group_by == 'date') {
            $advertise_kpi_query->addSelect('date');
            $advertise_kpi_query->groupBy($group_by);
            $advertise_kpi_query->orderByDesc('date');
            $install_kpi_query->addSelect('date');
            $install_kpi_query->groupBy($group_by);
        } else {
            $advertise_kpi_query->groupBy('target_app_id');
        }
        $install_kpi_list = $install_kpi_query->get()->keyBy('date');
        $advertise_kpi_list = $advertise_kpi_query
            ->orderBy('spend', 'desc')
            ->get();
        // dump($advertise_kpi_list->toArray());
        // dd($install_kpi_list->toArray());
        foreach ($advertise_kpi_list as $key => $kpi) {
            $kpi->cost = $install_kpi_list[$kpi->date]['cost'] ?? 0;
        }
        return JsonResource::collection($advertise_kpi_list);
    }

    public function app(Request $request, $channel_id)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));

        $app_base_query = App::query();
        if (!empty($request->get('keyword'))) {
            $app_base_query->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $app_id_query = clone $app_base_query;
        $app_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function ($query) use ($start_date, $end_date, $app_id_query, $channel_id) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('app_id', $app_id_query)
                ->where('target_app_id', $channel_id)
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

        $advertise_kpi_list = $advertise_kpi_query
            ->orderBy('spend', 'desc')
            ->get()
            ->keyBy('app_id')
            ->toArray();
        $app_id_list = array_reverse(array_keys($advertise_kpi_list));
        $order_by_ids = implode(',', $app_id_list);
        $app_query = clone $app_base_query;
        $app_query->whereIn('id', $app_id_list);
        if (!empty($order_by_ids)) {
            $app_query->orderByRaw(DB::raw("FIELD(id,{$order_by_ids}) desc"));
        }
        $app_list = $app_query->orderBy($request->get('field', 'name'), $request->get('order', 'desc'))
            ->paginate($request->get('limit', 30));

        foreach ($app_list as $index => &$app) {
            if (isset($advertise_kpi_list[$app['id']])) {
                $app->kpi = $advertise_kpi_list[$app['id']];
            }
        }
        return JsonResource::collection($app_list);
    }

    /**
     * Display the specified resource.
     *
     * @return ChannelResource|\Illuminate\Http\JsonResponse
     */
    public function show(Channel $channel)
    {
        return new ChannelResource($channel);
    }

    /**
     * save the resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return ChannelResource|\Illuminate\Http\JsonResponse
     */
    public function save(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), $this->getValidationRules($request));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }
        if (empty($id)) {
            $channel = new Channel();
            $channel->access_key = Str::random(8);
            $channel->access_secret = Str::random(16);
        } else {
            $channel = Channel::query()->where([
                'id' => $id
            ])->firstOrFail();
        }
        $channel->fill($request->all());
        if (empty($channel['name_hash'])) {
            $channel['name_hash'] = md5($channel['bundle_id'] . $channel['name'] . $channel['platform']);
        }
        $channel->saveOrFail();

        return new ChannelResource($channel);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User $user
     * @return \Illuminate\Http\Response
     */
    //    public function destroy(User $user)
    //    {
    //        if ($user->isAdmin()) {
    //            response()->json(['error' => 'Ehhh! Can not delete admin user'], 403);
    //        }
    //
    //        try {
    //            $user->delete();
    //        } catch (\Exception $ex) {
    //            response()->json(['error' => $ex->getMessage()], 403);
    //        }
    //
    //        return response()->json(null, 204);
    //    }

    public function tokenList($id)
    {
        $channel = Channel::findOrFail($id);
        return ApiToken::query()->where(['bundle_id' => $channel['bundle_id']])->get();
    }

    public function makeToken($id)
    {
        $channel = Channel::findOrFail($id);
        $api_token = ApiToken::Make($channel['bundle_id']);
        return ['api_token' => $api_token['access_token']];
    }

    /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules(Request $request)
    {
        $id = $request->input('id');
        return [
            'name' => [
                'required',
                'string',
                'unique:a_target_apps,name,' . $id . ',id,platform,' . $request->input('platform'),
                new AdvertiseName()
            ],
            'bundle_id' => 'required|max:255',
            'platform' => 'required|in:ios,android',
            'put_mode' => 'required|integer|in:1,2',
            'rate' => 'required|numeric|min:0|max:100',
        ];
    }
}
