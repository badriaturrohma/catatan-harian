<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JourneyEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'journey_id',
        'activity',
        'occurred_at',
        'recorded_at',
        'actor_id',
        'location_id',
        'method',
        'request_id',
        'is_late',
        'correction_reference_id',
        'notes',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'recorded_at' => 'datetime',
        'is_late' => 'boolean',
    ];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function correctionRequests(): HasMany
    {
        return $this->hasMany(CorrectionRequest::class, 'target_event_id');
    }

    public function correctionReference(): BelongsTo
    {
        return $this->belongsTo(JourneyEvent::class, 'correction_reference_id');
    }

    public function getActivityLabel(): string
    {
        return match ($this->activity) {
            'keluar_pondok' => 'Keluar Pondok',
            'tiba_kampus' => 'Tiba Kampus',
            'keluar_kampus' => 'Keluar Kampus',
            'kembali_pondok' => 'Kembali Pondok',
            default => ucfirst(str_replace('_', ' ', $this->activity)),
        };
    }
}
