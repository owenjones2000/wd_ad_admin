<?php

namespace App\Http\Controllers\Api;

use App\Models\Advertise\AdvertiseKpi;
use App\Models\Advertise\App;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class AppController extends Controller
{
    public function list(Request $request)
    {
        if(!empty($request->get('rangedate'))){
            $range_date = explode(' ~ ',$request->get('rangedate'));
        }
        $start_date = date('Ymd', strtotime($range_date[0]??'now'));
        $end_date = date('Ymd', strtotime($range_date[1]??'now'));
        $app_base_query = App::query();
        if(!empty($request->get('keyword'))){
            $app_base_query->where('name', 'like', '%'.$request->get('name').'%');
        }
        $app_id_query = clone $app_base_query;
        $app_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function($query) use($start_date, $end_date, $app_id_query){
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('app_id', $app_id_query)
                ->select(['requests', 'impressions', 'clicks', 'installations', 'spend',
                    'app_id',
                ])
            ;
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
            ->orderBy('spend','desc')
            ->get()
            ->keyBy('app_id')
            ->toArray();
        $order_by_ids = implode(',', array_reverse(array_keys($advertise_kpi_list)));
        $app_query = clone $app_base_query;
        if(!empty($order_by_ids)){
            $app_query->orderByRaw(DB::raw("FIELD(id,{$order_by_ids}) desc"));
        }
        $app_list = $app_query->orderBy($request->get('field','name'),$request->get('order','desc'))
            ->paginate($request->get('limit',30));

        foreach($app_list as &$app){
            if(isset($advertise_kpi_list[$app['id']])){
                $app->kpi = $advertise_kpi_list[$app['id']];
            }
        }
        return JsonResource::collection($app_list);
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
        return response()->json(['code'=>0,'msg'=>'Successful']);
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
        return response()->json(['code'=>0,'msg'=>'Successful']);
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
        if (empty($ids)){
            return response()->json(['code'=>1,'msg'=>'请选择删除项']);
        }
        if (App::destroy($ids)){
            return response()->json(['code'=>0,'msg'=>'删除成功']);
        }
        return response()->json(['code'=>1,'msg'=>'删除失败']);
    }
}
