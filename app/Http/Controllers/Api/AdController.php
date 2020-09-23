<?php

namespace App\Http\Controllers\Api;

use App\Models\Advertise\AdvertiseKpi;
use App\Models\Advertise\App;
use App\Models\Advertise\Ad;
use App\Models\Advertise\Campaign;
use App\Rules\AdvertiseName;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AdTag;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdController extends Controller
{
    public function list(Request $request, $campaign_id)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));
        $order_by = explode('.', $request->get('field', 'status'));
        $order_sort = $request->get('order', 'desc');

        $ad_base_query = Ad::query()->where('campaign_id', $campaign_id);
        if (!empty($request->get('keyword'))) {
            $like_keyword = '%' . $request->get('keyword') . '%';
            $ad_base_query->where('name', 'like', $like_keyword);
            $ad_base_query->orWhereHas('campaign.advertiser', function ($query) use ($like_keyword) {
                $query->where('realname', 'like', $like_keyword);
            });
            $ad_base_query->orWhereHas('campaign.app', function ($query) use ($like_keyword) {
                $query->where('name', 'like', $like_keyword);
            });
        }

        $ad_id_query = clone $ad_base_query;
        $ad_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function ($query) use ($start_date, $end_date, $ad_id_query) {
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('ad_id', $ad_id_query);
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
            'ad_id',
        ]);
        $advertise_kpi_query->groupBy('ad_id');
        if ($order_by[0] === 'kpi' && isset($order_by[1])) {
            $advertise_kpi_query->orderBy($order_by[1], $order_sort);
        }

        $advertise_kpi_list = $advertise_kpi_query
            ->orderBy('spend', 'desc')
            ->get()
            ->keyBy('ad_id')
            ->toArray();
        $order_by_ids = implode(',', array_reverse(array_keys($advertise_kpi_list)));
        // dd($order_by_ids);
        $ad_base_query->with('campaign.app', 'campaign.advertiser');
        if (!empty($order_by_ids)) {
            $ad_base_query->orderByRaw(DB::raw("FIELD(id,{$order_by_ids}) desc"));
        }
        if ($order_by[0] !== 'kpi') {
            $ad_base_query->orderBy($order_by[0], $order_sort);
        }
        $ad_list = $ad_base_query->with('assets')
            ->orderBy('id', 'desc')
            ->paginate($request->get('limit', 30));

        foreach ($ad_list as &$ad) {
            if (isset($advertise_kpi_list[$ad['id']])) {
                $ad['kpi'] = $advertise_kpi_list[$ad['id']];
            }
        }

        return JsonResource::collection($ad_list);
    }
    public function adList(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));
        $order_by = explode('.', $request->get('field', 'status'));
        $order_sort = $request->get('order', 'desc');

        $ad_base_query = Ad::query()->where('status', 1);
        if (!empty($request->get('keyword'))) {
            $like_keyword = '%' . $request->get('keyword') . '%';
            $ad_base_query->where('name', 'like', $like_keyword);
            $ad_base_query->orWhereHas('campaign.advertiser', function ($query) use ($like_keyword) {
                $query->where('realname', 'like', $like_keyword);
            });
            $ad_base_query->orWhereHas('campaign.app', function ($query) use ($like_keyword) {
                $query->where('name', 'like', $like_keyword);
            });
        }

        $ad_id_query = clone $ad_base_query;
        $ad_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function ($query) use ($start_date, $end_date, $ad_id_query) {
            $query->where('installations', '>', 0)->whereBetween('date', [$start_date, $end_date])
                ->whereIn('ad_id', $ad_id_query);
            return $query;
        }, $start_date, $end_date);

        $advertise_kpi_query->select([
            DB::raw('sum(requests) as requests'),
            DB::raw('sum(impressions) as impressions'),
            DB::raw('sum(clicks) as clicks'),
            DB::raw('sum(installations) as installs'),
            // DB::raw('round(sum(clicks) * 100 / sum(impressions), 2) as ctr'),
            // DB::raw('round(sum(installations) * 100 / sum(clicks), 2) as cvr'),
            // DB::raw('round(sum(installations) * 100 / sum(impressions), 2) as ir'),
            DB::raw('round(sum(spend), 2) as spend'),
            DB::raw('round(sum(spend) / sum(installations), 2) as ecpi'),
            DB::raw('round(sum(spend) * 1000 / sum(impressions), 2) as ecpm'),
            'ad_id',
        ]);
        $advertise_kpi_query->groupBy('ad_id');
        // if($order_by[0] === 'kpi' && isset($order_by[1])){
        //     $advertise_kpi_query->orderBy($order_by[1], $order_sort);
        // }

        $advertise_kpi_list = $advertise_kpi_query
            ->orderBy('installs', 'desc')
            ->get()
            ->keyBy('ad_id')
            ->toArray();
        $order_by_ids = implode(',', array_reverse(array_keys($advertise_kpi_list)));
        // dd($order_by_ids);
        $ad_base_query->with('campaign.app', 'campaign.advertiser');
        if (!empty($order_by_ids)) {
            $ad_base_query->orderByRaw(DB::raw("FIELD(id,{$order_by_ids}) desc"));
        }
        // if($order_by[0] !== 'kpi'){
        //     $ad_base_query->orderBy($order_by[0], $order_sort);
        // }$campaign_query->whereIn('id', $channel_id_list);
        $ad_list = $ad_base_query->whereIn('id', array_keys($advertise_kpi_list))->with('assets', 'tags')
            ->orderBy('id', 'desc')
            ->paginate($request->get('limit', 30));

        foreach ($ad_list as &$ad) {
            if (isset($advertise_kpi_list[$ad['id']])) {
                $ad['kpi'] = $advertise_kpi_list[$ad['id']];
            }
        }

        return JsonResource::collection($ad_list);
    }

    public function bindTag(Request $request)
    {
        $this->validate($request, [
            'ads' => 'bail|required|array',
            'tags' => 'required|array',
        ]);
        $ads =  $request->input('ads', []);
        $tags = $request->input('tags', []);
        Log::info($ads, $tags);
        try {
            foreach ($ads as $key => $ad_id) {
                $ad = Ad::findOrFail($ad_id);
                if ($tags) {
                    $ad->tags()->sync($tags);
                }
            }
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['code' => 100, 'msg' => 'Failed']);
        }

        return response()->json(['code' => 0, 'msg' => 'Successful']);
    }
    public function listReview(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0] ?? 'now'));
        $end_date = date('Ymd', strtotime($range_date[1] ?? 'now'));
        $order_by = explode('.', $request->get('field', 'status'));
        $order_sort = $request->get('order', 'desc');

        $ad_base_query = Ad::query()->where('need_review', 1);
        if (!empty($request->get('keyword'))) {
            $like_keyword = '%' . $request->get('keyword') . '%';
            $ad_base_query->where('name', 'like', $like_keyword);
            $ad_base_query->orWhereHas('campaign.advertiser', function ($query) use ($like_keyword) {
                $query->where('realname', 'like', $like_keyword);
            });
            $ad_base_query->orWhereHas('campaign.app', function ($query) use ($like_keyword) {
                $query->where('name', 'like', $like_keyword);
            });
        }

        $ad_base_query->with('campaign.app', 'campaign.advertiser');
        if ($order_by[0] !== 'kpi') {
            $ad_base_query->orderBy($order_by[0], $order_sort);
        }
        $ad_list = $ad_base_query->with('assets')
            ->orderBy('id', 'desc')
            ->paginate($request->get('limit', 30));

        foreach ($ad_list as &$ad) {
            if (isset($advertise_kpi_list[$ad['id']])) {
                $ad['kpi'] = $advertise_kpi_list[$ad['id']];
            }
            $ad['playable'] = $ad->assets->pluck('type_id')->contains(8) ?? false;
        }

        return JsonResource::collection($ad_list);
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

    public function tagList(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $appTag = AdTag::when($keyword, function ($query) use ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%');
        })
            ->where('status', 1)
            ->paginate($request->get('limit', 30));;

        return JsonResource::collection($appTag);
    }
    public function tagAll(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $appTag = AdTag::when($keyword, function ($query) use ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%');
        })
            ->where('status', 1)
            ->get();

        return JsonResource::collection($appTag);
    }

    public function tagSave(Request $request, $id = null)
    {
        $this->validate($request, [
            'name'  => 'required|string|unique:a_ad_tag,name,' . $id . ',id',
        ]);
        try {
            $params = $request->all();
            $params['id'] = $id;
            AdTag::Make($params);
            return response()->json(['code' => 0, 'msg' => 'Successful']);
        } catch (Exception $ex) {
            Log::error($ex);
            return response()->json(['code' => 100, 'msg' => $ex->getMessage()]);
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($campaign_id, $id = null)
    {
        if ($id == null) {
            $ad = new Ad();
            $ad['campaign_id'] = $campaign_id;
        } else {
            $ad = Ad::query()->where(['id' => $id, 'campaign_id' => $campaign_id])->firstOrFail();
        }
        return view('advertise.campaign.ad.edit', compact('ad'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, $campaign_id, $id = null)
    {
        $this->validate($request, [
            'name'  => [
                'required', 'string', 'max:100',
                'unique:a_ad,name,' . $id . ',id,campaign_id,' . $campaign_id,
                new AdvertiseName()
            ],
            'app_id' => 'exists:a_app,id',
            'regions' => 'string',
            'asset' => 'array',
            'asset.*.id' => 'required|numeric',
            'asset.*.type' => 'required|numeric',
        ]);
        /** @var Campaign $campaign */
        $campaign = Campaign::query()->where([
            'id' => $campaign_id,
            'main_user_id' => Auth::user()->getMainId(),
        ])->firstOrFail();
        $params = $request->all();
        $params['id'] = $id;
        //        $params['status'] = isset($params['status']) ? 1 : 0;
        $ad = $campaign->makeAd(Auth::user(), $params);
        if ($ad) {
            return redirect(route('advertise.campaign.ad.edit', [$ad['campaign_id'], $ad['id']]))
                ->with(['status' => 'Save successfully.' . ($ad['status'] ? '' : ' But ad is not running.')]);
        }
        return redirect(route('advertise.campaign.ad.edit', [$ad['campaign_id'], $ad['id']]))->withErrors(['status' => 'Error']);
    }

    /**
     * 启动
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function enable($campaign_id, $id)
    {
        /** @var Ad $ad */
        $ad = Ad::query()->where(['id' => $id, 'campaign_id' => $campaign_id])->firstOrFail();
        $ad->enable();
        return response()->json(['code' => 0, 'msg' => 'Enabled']);
    }

    /**
     * 停止
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function disable($campaign_id, $id)
    {
        /** @var Ad $ad */
        $ad = Ad::query()->where(['id' => $id, 'campaign_id' => $campaign_id])->firstOrFail();
        $ad->disable();
        return response()->json(['code' => 0, 'msg' => 'Disabled']);
    }

    public function clearRedis($campaign_id, $id)
    {
        $ad = Ad::query()->where(['id' => $id, 'campaign_id' => $campaign_id])->firstOrFail();
        $ad->restart();
        return response()->json(['code' => 0, 'msg' => 'Restart']);
    }
    /**
     * 通过审核
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function passReview($campaign_id, $id)
    {
        /** @var Ad $ad */
        $ad = Ad::query()->where(['id' => $id, 'campaign_id' => $campaign_id])->firstOrFail();
        $ad->passReview();
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
        if (Ad::destroy($ids)) {
            return response()->json(['code' => 0, 'msg' => '删除成功']);
        }
        return response()->json(['code' => 1, 'msg' => '删除失败']);
    }
}
