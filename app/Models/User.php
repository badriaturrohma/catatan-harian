<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isPengurus(): bool
    {
        return $this->role === 'pengurus';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    public function isWali(): bool
    {
        return $this->role === 'wali';
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OfficerAssignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(OfficerAssignment::class)
            ->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->latest('valid_from');
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function guardianSantris(): HasMany
    {
        return $this->hasMany(GuardianSantri::class);
    }

    public function santris(): BelongsToMany
    {
        return $this->belongsToMany(Santri::class, 'guardian_santris', 'user_id', 'santri_id')
            ->withPivot(['relationship_type', 'is_verified', 'verified_by', 'verified_at'])
            ->withTimestamps();
    }

    public function verifiedChildren()
    {
        return $this->hasManyThrough(
            Santri::class,
            GuardianSantri::class,
            'user_id',
            'id',
            'id',
            'santri_id'
        )->where('guardian_santris.is_verified', true);
    }
}
