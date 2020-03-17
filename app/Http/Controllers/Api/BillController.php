<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillResource;
use App\Models\Advertise\Account;
use App\Models\Advertise\Bill;
use Barryvdh\DomPDF\Facade;
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
        $bill_query = Bill::query()->with('account');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        if (!empty($keyword)) {
            $account_query = Account::query()->select('id');
            $account_query->where('realname', 'LIKE', '%' . $keyword . '%');
            $account_query->orWhere('email', 'LIKE', '%' . $keyword . '%');
            $bill_query->whereIn('main_user_id', $account_query);
        }

        $bill_query
            ->orderBy('paid_at')
            ->orderByRaw('IF(ISNULL(due_date),1,0), due_date')
        ;
        return BillResource::collection($bill_query->paginate($limit));
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

    public function invoice($id){
        $bill = Bill::query()->where('id', $id)->with('account')->firstOrFail();
        return view('bill.invoice', ['bill' => $bill]);
    }

    public function invoicePdf($id){
        $bill = Bill::query()->where('id', $id)->with('account')->firstOrFail();
        $pdf = Facade::loadView('bill.invoice', ['bill' => $bill]);
        return $pdf->download('invoice.pdf');
    }
}
