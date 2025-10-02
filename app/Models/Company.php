<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'rut',
        'sii_password',
    ];

    protected $hidden = [
        'sii_password',
    ];

    protected $casts = [
        'sii_password' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
