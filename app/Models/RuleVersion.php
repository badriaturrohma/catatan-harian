<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RuleVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'scope',
        'scope_id',
        'parameters',
        'effective_at',
        'created_by',
        'previous_version_id',
        'change_reason',
        'is_active',
    ];

    protected $casts = [
        'parameters' => 'array',
        'effective_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(RuleVersion::class, 'previous_version_id');
    }

    public function holidayPeriods(): HasMany
    {
        return $this->hasMany(HolidayPeriod::class);
    }

    /**
     * Get active parameters for given campus or global
     */
    public static function getActiveParameters(?int $campusId = null): array
    {
        // 1. Campus specific rule if exists and active
        if ($campusId) {
            $campusRule = static::where('scope', 'campus')
                ->where('scope_id', $campusId)
                ->where('is_active', true)
                ->where('effective_at', '<=', now())
                ->latest('effective_at')
                ->first();

            if ($campusRule && ! empty($campusRule->parameters)) {
                return $campusRule->parameters;
            }
        }

        // 2. Global rule
        $globalRule = static::where('scope', 'global')
            ->where('is_active', true)
            ->where('effective_at', '<=', now())
            ->latest('effective_at')
            ->first();

        if ($globalRule && ! empty($globalRule->parameters)) {
            return $globalRule->parameters;
        }

        // Fallback default rules matching PRD section 5
        return [
            'threshold_yellow_arrive_campus' => 15, // menit
            'threshold_red_arrive_campus' => 30,    // menit
            'regular_departure_cutoff' => '16:00',  // batas jam keluar
            'regular_return_curfew' => '16:00',     // batas jam kembali
            'curfew_late_yellow' => 15,            // menit setelah curfew
            'curfew_late_red' => 30,               // menit setelah curfew
            'permit_min_duration' => 1,            // jam
            'permit_max_duration' => 12,           // jam
            'permit_late_yellow' => 15,            // menit setelah permit deadline
            'permit_late_red' => 30,               // menit setelah permit deadline
        ];
    }
}
