<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'commerce_order',
        'flow_order',
        'flow_token',
        'amount',
        'description',
        'payer_email',
        'billing_rut',
        'billing_name',
        'billing_type',
        'document_type',
        'billing_address',
        'status',
        'paid_at',
        'confirmed_at',
        'payment_data',
    ];

    protected $casts = [
        'payment_data' => 'array',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
