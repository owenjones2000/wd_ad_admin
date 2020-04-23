<?php

namespace App\Models\Advertise;

use Illuminate\Database\Eloquent\Model;

class UaOperationLog extends Model
{
    protected $table = 'ua_operation_logs';

    public function account(){
        return $this->belongsTo(Account::class, 'user_id', 'id');
    }

    public function mainAccount(){
        return $this->belongsTo(Account::class, 'main_user_id', 'id');
    }
}
