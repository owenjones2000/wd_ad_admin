<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MakeAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Advertise\Account;
use App\Http\Controllers\Controller;
use App\Models\Advertise\BillSet;
use Illuminate\Http\Request;
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
    public function list(Request $request, $main_user_id = 0)
    {
        $searchParams = $request->all();
        $accountQuery = Account::query()->with('children', 'bill')->where('main_user_id', $main_user_id);
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $accountQuery->where('realname', 'LIKE', '%' . $keyword . '%');
            $accountQuery->where('email', 'LIKE', '%' . $keyword . '%');
        }

        return AccountResource::collection($accountQuery->paginate($limit));
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

    public function setBill(Request $request, $id)
    {
        /** @var BillSet $bill_set */
        BillSet::query()->updateOrCreate(
            ['id' => $id],
            [
                'address' => $request->input('address'),
                'phone' => $request->input('phone'),
            ]
        );
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
