<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Agent extends Authenticatable implements AuthenticatableContract, FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'phone_number',
        'area',
        'commission_rate',
        'profile_picture',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function getTenants(Panel $panel): array|\Illuminate\Support\Collection
    {
        return []; // Implement this if you're using multi-tenancy
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return true; // Implement your own logic if needed
    }

    // The following methods are already implemented by the Authenticatable class,
    // but you can override them if you need custom behavior:

    // public function getAuthIdentifierName()
    // {
    //     return 'id';
    // }

    // public function getAuthIdentifier()
    // {
    //     return $this->getKey();
    // }

    // public function getAuthPassword()
    // {
    //     return $this->password;
    // }

    // public function getRememberToken()
    // {
    //     return $this->remember_token;
    // }

    // public function setRememberToken($value)
    // {
    //     $this->remember_token = $value;
    // }

    // public function getRememberTokenName()
    // {
    //     return 'remember_token';
    // }
}