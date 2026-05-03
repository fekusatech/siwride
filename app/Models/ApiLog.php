<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $fillable = [
        'method',
        'path',
        'request_headers',
        'request_body',
        'response_body',
        'status_code',
        'user_id',
        'user_email',
        'user_role',
        'ip_address',
        'user_agent',
        'duration_ms',
    ];
}
