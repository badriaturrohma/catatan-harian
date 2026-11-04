<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Santri extends Model
{
    use HasFactory;

    protected $fillable = [
        'niup',
        'name',
        'gender',
        'photo',
        'campus_id',
        'program_id',
        'region_id',
        'area_id',
        'room_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function qrCredentials(): HasMany
    {
        return $this->hasMany(QrCredential::class);
    }

    public function activeQrCredential(): HasOne
    {
        return $this->hasOne(QrCredential::class)->where('is_active', true)->latest('issued_at');
    }

    public function guardianSantris(): HasMany
    {
        return $this->hasMany(GuardianSantri::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardian_santris', 'santri_id', 'user_id')
            ->withPivot(['relationship_type', 'is_verified', 'verified_by', 'verified_at'])
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->guardians();
    }

    public function journeys(): HasMany
    {
        return $this->hasMany(Journey::class);
    }

    public function permits(): HasMany
    {
        return $this->hasMany(Permit::class);
    }

    public function activeJourney(): HasOne
    {
        return $this->hasOne(Journey::class)
            ->whereNotIn('status', ['selesai', 'rekonsiliasi'])
            ->latest('started_at');
    }

    /**
     * Get a snapshot of santri organization at point in time
     */
    public function getOrganizationSnapshot(): array
    {
        return [
            'campus_id' => $this->campus_id,
            'campus_name' => $this->campus?->name,
            'program_id' => $this->program_id,
            'program_name' => $this->program?->name,
            'region_id' => $this->region_id,
            'region_name' => $this->region?->name,
            'area_id' => $this->area_id,
            'area_name' => $this->area?->name,
            'room_id' => $this->room_id,
            'room_name' => $this->room?->name,
            'niup' => $this->niup,
            'name' => $this->name,
        ];
    }
}
