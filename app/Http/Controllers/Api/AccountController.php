<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MakeAccountRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\PermissionTreeResource;
use App\Models\Advertise\Account;
use App\Http\Controllers\Controller;
use App\Models\Advertise\UaPermission;
use App\Models\Advertise\UaOperationLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class AccountController extends Controller
{
    const ITEM_PER_PAGE = 15;

    /**
     * Display a listing of the user resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return 
     */
    public function list(Request $request)
    {
        $searchParams = $request->all();
        $accountQuery = Account::query()->with('bill', 'advertisers', 'advertisers.bill');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        if (!empty($keyword)) {
            $accountQuery->where(function($query) use($keyword) {
                $query->where('realname', 'LIKE', '%' . $keyword . '%');
                $query->orwhere('email', 'LIKE', '%' . $keyword . '%');
            });
        }
        $accountQuery->orderBy('isAdvertiseEnabled', 'desc');
        return AccountResource::collection($accountQuery->paginate($limit));
    }

    public function opLog(Request $request)
    {
        $searchParams = $request->all();
        $opLogQuery = UaOperationLog::query()->with('account', 'mainAccount');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        $range_date = Arr::get($searchParams, 'daterange');
        $start_date = date('Y-m-d 00:00:00', strtotime($range_date[0]??'now'));
        $end_date = date('Y-m-d 23:59:59', strtotime($range_date[1]??'now'));
        $opLogQuery->whereBetween('created_at', [$start_date, $end_date]);

        if(!empty($keyword)){
            $like_keyword = '%'.$request->get('keyword').'%';
            $opLogQuery->WhereHas('account', function($query) use($like_keyword) {
                $query->where('realname', 'like', $like_keyword);
                $query->orWhere('email', 'like', $like_keyword);
            });
            $opLogQuery->orWhereHas('mainAccount', function($query) use($like_keyword) {
                $query->where('realname', 'like', $like_keyword);
                $query->orWhere('email', 'like', $like_keyword);
            });
        }

        if(!empty(Arr::get($searchParams, 'method'))){
            $opLogQuery->where('method', Arr::get($searchParams, 'method'));
        }

        $opLogQuery->orderBy('created_at', 'desc');
        return JsonResource::collection($opLogQuery->paginate($limit));
    }

    public function allPermission(){
        return PermissionTreeResource::collection(UaPermission::query()->where('parent_id', 0)->get());
    }

    public function permissions($id, $main_user_id = null){
        /** @var Account $account */
        $account = Account::query()->findOrFail($id);
        return JsonResource::make($account->permissions($main_user_id)->pluck('name'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Account    $account
     */
    public function updatePermissions(Request $request, Account $account, $main_account_id)
    {
        if ($account === null) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        $permissionIds = $request->get('permissions', []);

        $permissions = UaPermission::query()->whereIn('name', $permissionIds)->pluck('id')->toArray();
        $permissions = array_fill_keys($permissions,
            [
                'main_user_id' => $main_account_id,
                'model_type' => 'App\User',
            ]
        );
        $account->permissions($main_account_id)->sync($permissions);

        return response()->json(['code'=>0,'msg'=>'permission updated']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  MakeAccountRequest
     * @param  int  $id
     * @return AccountResource
     */
    public function save(MakeAccountRequest $request, $id = null)
    {
        $params = $request->all();
        $params['id'] = $id;
        $account = Account::Make($params);
        return new AccountResource($account);
    }

    /**
     * 启用
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function enable($id)
    {
        /** @var Account $account */
        $account = Account::findOrFail($id);
        $account->enable();
        return response()->json(['code'=>0,'msg'=>'Enabled']);
    }

    /**
     * 禁用
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function disable($id)
    {
        /** @var Account $account */
        $account = Account::findOrFail($id);
        $account->disable();
        return response()->json(['code'=>0,'msg'=>'Disabled']);
    }

    /**
     * 启用广告投放
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function enableAdvertising($id)
    {
        /** @var Account $account */
        $account = Account::findOrFail($id);
        $account->enableAdvertising();
        return response()->json(['code'=>0,'msg'=>'Enabled']);
    }

    /**
     * 禁用广告投放
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function disableAdvertising($id)
    {
        /** @var Account $account */
        $account = Account::findOrFail($id);
        $account->disableAdvertising();
        return response()->json(['code'=>0,'msg'=>'Disabled']);
    }

    /**
     * 启用广告变现
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function enablePublishing($id)
    {
        /** @var Account $account */
        $account = Account::findOrFail($id);
        $account->enablePublishing();
        return response()->json(['code'=>0,'msg'=>'Enabled']);
    }

    /**
     * 禁用广告变现
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function disablePublishing($id)
    {
        /** @var Account $account */
        $account = Account::findOrFail($id);
        $account->disablePublishing();
        return response()->json(['code'=>0,'msg'=>'Disabled']);
    }

    public function setBill(Request $request, $id)
    {
        /** @var Account $account */
        $account = Account::query()->findOrFail($id);
        $account->setBill($request->only('address', 'phone'));

        return response()->json(['code'=>0,'msg'=>'Bill set updated']);
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
//        /** @var User $op_user */
//        $op_user = Auth::user();
//        $result = User::query()
//            ->whereIn('id', $ids)
//            ->where('main_user_id', $op_user['id'])->delete();
//        if ($result){
//            return response()->json(['code'=>0,'msg'=>'删除成功']);
//        }
//        return response()->json(['code'=>1,'msg'=>'删除失败']);
//    }
}
