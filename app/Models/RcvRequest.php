<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RcvRequest extends Model
{
    protected $fillable = [
        'user_id',
        'period',
        'type',
        'status',
        'company_ids',
        'response_data',
        'error_message',
        'requested_at',
        'completed_at',
    ];

    protected $casts = [
        'company_ids' => 'array',
        'response_data' => 'array',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
