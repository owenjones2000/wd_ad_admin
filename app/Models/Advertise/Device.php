<?php
namespace App\Models\Advertise;

use App\Exceptions\BizException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Device extends Model
{
    protected $table = 'p_devices';
}
