<?php
namespace App\Models\Advertise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UaPermission extends Model
{
    use SoftDeletes;

    protected $table = 'ua_permissions';
}
