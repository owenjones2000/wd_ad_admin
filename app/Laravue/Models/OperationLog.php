<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationLog extends Model
{
    protected $table = 'admin_operation_log';
    // protected $connection = 'mysql_system';
    protected $fillable = ['user_id', 'path', 'method', 'ip', 'input', 'name'];

    public static $methodColors = [
        'GET'    => 'primary',
        'POST'   => 'success',
        'PUT'    => 'blue',
        'DELETE' => 'danger',
    ];

    public static $methods = [
        'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH',
        'LINK', 'UNLINK', 'COPY', 'HEAD', 'PURGE',
    ];

    /**
     * Log belongs to users.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
