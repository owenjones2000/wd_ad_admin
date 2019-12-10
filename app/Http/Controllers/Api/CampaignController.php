<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertise\AdvertiseKpi;
use App\Models\Advertise\Campaign;
use App\Rules\AdvertiseName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    public function list(Request $request)
    {
        $range_date = $request->get('daterange');
        $start_date = date('Ymd', strtotime($range_date[0]??'now'));
        $end_date = date('Ymd', strtotime($range_date[1]??'now'));
        $campaign_base_query = Campaign::query();

        if(!empty($request->get('keyword'))){
            $campaign_base_query->where('name', 'like', '%'.$request->get('keyword').'%');
        }

        $campaign_id_query = clone $campaign_base_query;
        $campaign_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function($query) use($start_date, $end_date, $campaign_id_query){
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('campaign_id', $campaign_id_query);
            return $query;
        }, $start_date, $end_date);

        $advertise_kpi_query->select([
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
            ->orderBy('spend','desc')
            ->get()
            ->keyBy('campaign_id')
            ->toArray();
        $order_by_ids = implode(',', array_reverse(array_keys($advertise_kpi_list)));
        $campaign_query = clone $campaign_base_query;
        $campaign_query->with('app');
        if(!empty($order_by_ids)){
            $campaign_query->orderByRaw(DB::raw("FIELD(id,{$order_by_ids}) desc"));
        }
        $campaign_list = $campaign_query->orderBy($request->get('field','status'),$request->get('order','desc'))
            ->paginate($request->get('limit',30));

        foreach($campaign_list as &$campaign){
            if(isset($advertise_kpi_list[$campaign['id']])){
                $campaign->kpi = $advertise_kpi_list[$campaign['id']];
            }
        }
        return JsonResource::collection($campaign_list);
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
        $this->validate($request,[
            'name'  => ['required','string','max:100','unique:a_campaign,name,'.$id, new AdvertiseName()],
            'app_id' => 'exists:a_app,id',
            'regions' => 'string',
            'budget' => 'array',
            'budget.*.region_code' => 'required|string|max:3',
            'budget.*.amount' => 'numeric',
            'bid_by_region' => 'bool',
            'bid' => 'array',
            'bid.*.region_code' => 'required|string|max:3',
            'bid.*.amount' => 'numeric'

        ]);
        $params = $request->all();
        $params['id'] = $id;
//        $params['status'] = isset($params['status']) ? 1 : 0;
        if (Campaign::Make(Auth::user(), $params)){
            return redirect(route('advertise.campaign.edit', [$id]))->with(['status'=>'Save successfully.']);
        }
        return redirect(route('advertise.campaign.edit', [$id]))->withErrors(['status'=>'Error']);
    }

    /**
     * 启动
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function enable($id)
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::findOrFail($id);
        $campaign->enable();
        return response()->json(['code'=>0,'msg'=>'Enabled']);
    }

    /**
     * 停止
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function disable($id)
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::findOrFail($id);
        $campaign->disable();
        return response()->json(['code'=>0,'msg'=>'Disabled']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//    public function destroy(Request $request)
//    {
//        $ids = $request->get('ids');
//        if (empty($ids)){
//            return response()->json(['code'=>1,'msg'=>'请选择删除项']);
//        }
//        if (Campaign::destroy($ids)){
//            return response()->json(['code'=>0,'msg'=>'删除成功']);
//        }
//        return response()->json(['code'=>1,'msg'=>'删除失败']);
//    }
}