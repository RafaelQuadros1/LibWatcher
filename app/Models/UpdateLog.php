<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateLog extends Model
{
    protected $fillable = [
        'package_name',
        'package_type',
        'current_version',
        'latest_version',
        'has_update',
        'metadata',
        'checked_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'checked_at' => 'datetime',
        'has_update' => 'boolean'
    ];
}