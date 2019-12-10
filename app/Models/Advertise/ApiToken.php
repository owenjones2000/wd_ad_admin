<?php

namespace App\Models\Advertise;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $table = 'api_access_tokens';

    protected $guarded = [];

    public static function Make($bundle_id, $expired_at = '2100-11-11'){
        $token = Str::random(128);
        $api_token = ApiToken::create([
            'bundle_id' => $bundle_id,
            'access_token' => $token,
            'expired_at' => $expired_at,
        ]);

        return $api_token;
    }
}
