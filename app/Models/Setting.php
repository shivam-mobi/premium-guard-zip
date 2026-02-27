<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'email',
        'phone_no',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];
}
