<?php
namespace App\Models\Advertise;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillSet extends Model
{
    protected $table = 'a_user_bill';

    protected $fillable = ['id', 'address', 'phone','company'];

    public function account(){
        return $this->belongsTo(Account::class,'id','id');
    }
}
