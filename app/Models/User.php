<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'plan_id',
        'is_accountant',
        'is_approved',
        'approved_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_accountant' => 'boolean',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function canAddMoreCompanies(): bool
    {
        if (!$this->plan) {
            return false;
        }

        $currentCount = $this->companies()->count();
        return $currentCount < $this->plan->company_limit;
    }

    public function remainingCompanies(): int
    {
        if (!$this->plan) {
            return 0;
        }

        $currentCount = $this->companies()->count();
        return max(0, $this->plan->company_limit - $currentCount);
    }
}
