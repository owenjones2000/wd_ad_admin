<?php
namespace App\Models\Advertise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    protected $table = 'a_bill';

    protected $fillable = ['start_date', 'end_date', 'fee_amount', 'due_date'];
}
