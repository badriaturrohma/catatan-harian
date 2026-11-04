<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'name',
        'start_date',
        'end_date',
        'departure_cutoff_time',
        'is_active',
        'rule_version_id',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function ruleVersion(): BelongsTo
    {
        return $this->belongsTo(RuleVersion::class);
    }

    /**
     * Check if a given date falls in this holiday period
     */
    public static function isHolidayForCampus(int $campusId, ?string $date = null): ?self
    {
        $checkDate = $date ?? now()->toDateString();

        return static::where('campus_id', $campusId)
            ->where('is_active', true)
            ->where('start_date', '<=', $checkDate)
            ->where('end_date', '>=', $checkDate)
            ->first();
    }
}
