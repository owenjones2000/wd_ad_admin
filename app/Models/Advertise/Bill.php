<?php
namespace App\Models\Advertise;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    protected $table = 'a_bill';

    protected $fillable = ['start_date', 'end_date', 'fee_amount', 'due_date', 'paid_at'];

    public function account(){
        return $this->belongsTo(Account::class,'main_user_id','id');
    }

    public function pay(){
        if(!$this->paid_at){
            $this->paid_at = Carbon::now();
            $this->saveOrFail();
        }
        return true;
    }
}
