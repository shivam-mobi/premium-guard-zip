<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'app_key',
        'platform',
        'min_version',
        'latest_version',
        'force_update',
        'update_message',
        'store_url',
    ];

    protected $casts = [
        'force_update' => 'boolean',
    ];

    protected $visible = [
        'latest_version',
        'force_update',
        'update_message',
    ];
}
