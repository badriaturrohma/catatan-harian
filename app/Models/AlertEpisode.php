<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlertEpisode extends Model
{
    use HasFactory;

    protected $fillable = [
        'journey_id',
        'type',
        'highest_level',
        'threshold_at',
        'detected_at',
        'resolved_at',
        'resolution_kind',
    ];

    protected $casts = [
        'threshold_at' => 'datetime',
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(AlertEvaluation::class, 'episode_id');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(Followup::class, 'episode_id');
    }

    public function isResolved(): bool
    {
        return ! is_null($this->resolved_at);
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'belum_tiba_kampus' => 'Belum Tercatat Tiba di Kampus',
            'belum_kembali_reguler' => 'Belum Tercatat Kembali ke Pondok',
            'belum_kembali_izin' => 'Belum Tercatat Kembali Sesuai Izin',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
