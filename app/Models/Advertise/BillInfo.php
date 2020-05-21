<?php
namespace App\Models\Advertise;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillInfo extends Model
{
    protected $table = 'a_bill_info';

    protected $fillable = [
        'bill_id',
        'campaign_id',
        'campagin_name',
        'spend',
        'installations',
        // 'extra_data'
    ];

    // protected $casts = [
    //     'extra_data'=> 'array',
    // ];
    public function bill(){
        return $this->belongsTo(Bill::class,'bill_id','id');
    }
    // public function getBillInfo()
    // {
    //     $query = 
    // }

    
}
