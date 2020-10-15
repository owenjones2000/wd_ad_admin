<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChannelTag extends Model
{
    //
    protected $table= 'a_target_app_tag';
    protected $fillable = [
        'name',
        'status', 
        'group'
    ];

    public static function Make($params)
    {
        $apps = DB::transaction(function () use ($params) {
            if (empty($params['id'])) {
                $apps = new self();
            } else {
                $apps = self::query()->where([
                    'id' => $params['id'],
                ])->firstOrFail();
            }

            $apps->fill($params);
            $apps->saveOrFail();

            return $apps;
        }, 3);
        return $apps;
    }

    public function children()
    {
        return $this->hasMany(ChannelTag::class, 'group', 'id')->with('children');
    }
}
