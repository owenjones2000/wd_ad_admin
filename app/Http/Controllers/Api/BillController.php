<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillResource;
use App\Models\Advertise\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BillController extends Controller
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
        $billQuery = Bill::query()->with('account');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $billQuery->where('realname', 'LIKE', '%' . $keyword . '%');
            $billQuery->where('email', 'LIKE', '%' . $keyword . '%');
        }
        return BillResource::collection($billQuery->paginate($limit));
    }

    /**
     * 已支付
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function pay($id)
    {
        /** @var Bill $bill */
        $bill = Bill::findOrFail($id);
        $bill->pay();
        return response()->json(['code'=>0,'msg'=>'Payed']);
    }
}
