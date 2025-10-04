<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'company_limit',
        'price',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
