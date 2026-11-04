<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Journey extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'type',
        'operational_date',
        'status',
        'permit_id',
        'started_at',
        'completed_at',
        'organization_snapshot',
        'is_incomplete',
        'reconciliation_notes',
    ];

    protected $casts = [
        'operational_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'organization_snapshot' => 'array',
        'is_incomplete' => 'boolean',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function permit(): BelongsTo
    {
        return $this->belongsTo(Permit::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(JourneyEvent::class)->orderBy('occurred_at', 'asc');
    }

    public function latestEvent(): HasOne
    {
        return $this->hasOne(JourneyEvent::class)->latestOfMany('occurred_at');
    }

    public function alertEpisodes(): HasMany
    {
        return $this->hasMany(AlertEpisode::class);
    }

    public function activeAlertEpisode(): HasOne
    {
        return $this->hasOne(AlertEpisode::class)
            ->whereNull('resolved_at')
            ->latest('detected_at');
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['selesai', 'rekonsiliasi']);
    }

    public function getEvent(string $activity): ?JourneyEvent
    {
        return $this->events->firstWhere('activity', $activity);
    }
}
