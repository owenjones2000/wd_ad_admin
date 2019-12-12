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
use App\Http\Resources\UserResource;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use App\Models\Advertise\AdvertiseKpi;
use App\Models\Advertise\ApiToken;
use App\Models\Advertise\Channel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Validator;

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
        if(!empty($request->get('rangedate'))){
            $range_date = explode(' ~ ',$request->get('rangedate'));
        }
        $start_date = date('Ymd', strtotime($range_date[0]??'now'));
        $end_date = date('Ymd', strtotime($range_date[1]??'now'));
        $channel_base_query = Channel::query();
        if(!empty($request->get('keyword'))){
            $channel_base_query->where('name', 'like', '%'.$request->get('name').'%');
        }
        $channel_id_query = clone $channel_base_query;
        $channel_id_query->select('id');
        $advertise_kpi_query = AdvertiseKpi::multiTableQuery(function($query) use($start_date, $end_date, $channel_id_query){
            $query->whereBetween('date', [$start_date, $end_date])
                ->whereIn('target_app_id', $channel_id_query)
                ->select(['impressions', 'clicks', 'installations', 'spend',
                    'target_app_id',
                    ])
            ;
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
            'target_app_id',
        ]);
        $advertise_kpi_query->groupBy('target_app_id');

        $advertise_kpi_list = $advertise_kpi_query
            ->orderBy('spend','desc')
            ->get()
            ->keyBy('target_app_id')
            ->toArray();
        $order_by_ids = implode(',', array_reverse(array_keys($advertise_kpi_list)));
        $channel_query = clone $channel_base_query;
        if(!empty($order_by_ids)){
            $channel_query->orderByRaw(DB::raw("FIELD(id,{$order_by_ids}) desc"));
        }
        $channel_list = $channel_query->orderBy($request->get('field','name'),$request->get('order','desc'))
            ->paginate($request->get('limit',30));

        foreach($channel_list as &$channel){
            if(isset($advertise_kpi_list[$channel['id']])){
                $channel->kpi = $advertise_kpi_list[$channel['id']];
            }
        }
        return JsonResource::collection($channel_list);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->getValidationRules(),
                [
                    'password' => ['required', 'min:6'],
                    'confirmPassword' => 'same:password',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $user = User::create([
                'name' => $params['name'],
                'email' => $params['email'],
                'password' => Hash::make($params['password']),
            ]);
            $role = Role::findByName($params['role']);
            $user->syncRoles($role);

            return new UserResource($user);
        }
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
        $validator = Validator::make($request->all(), $this->getValidationRules());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }
        if (empty($id)) {
            $channel = new Channel();
        } else {
            $channel = Channel::query()->where([
                'id' => $id
            ])->firstOrFail();
        }
        $channel->fill($request->all());
        if(empty($channel['name_hash'])){
            $channel['name_hash'] = md5($channel['bundle_id'].$channel['name'].$channel['platform']);
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
    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            response()->json(['error' => 'Ehhh! Can not delete admin user'], 403);
        }

        try {
            $user->delete();
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }

        return response()->json(null, 204);
    }

    public function tokenList($id){
        $channel = Channel::findOrFail($id);
        return ApiToken::query()->where(['bundle_id' => $channel['bundle_id']])->get();
    }

    public function makeToken($id){
        $channel = Channel::findOrFail($id);
        $api_token = ApiToken::Make($channel['bundle_id']);
        return ['api_token' => $api_token['access_token']];
    }

    /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules()
    {
        return [
            'name' => 'required',
        ];
    }
}
