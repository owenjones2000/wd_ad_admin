<?php

namespace App\Models\Advertise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccountApiToken extends Model
{
    protected $table = 'a_user_access_tokens';

    protected $guarded = [];

    public static function Make($user_id, $expired_at = null){
        $token = Str::random(128);
        $api_token = static::create([
            'user_id' => $user_id,
            'access_token' => $token,
            'expired_at' => $expired_at,
        ]);

        return $api_token;
    }
}
