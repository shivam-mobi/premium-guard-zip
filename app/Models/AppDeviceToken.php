<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppDeviceToken extends Model
{
    public $timestamps = false;

    protected $table = 'app_device_tokens';

    protected $fillable = [
        'device_token',
    ];
}
