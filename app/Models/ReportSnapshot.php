<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_type',
        'period_key',
        'scope',
        'revision',
        'source_watermark',
        'totals',
        'status',
        'generated_at',
        'supersedes_id',
        'revision_reason',
        'created_by',
    ];

    protected $casts = [
        'totals' => 'array',
        'generated_at' => 'datetime',
        'revision' => 'integer',
    ];

    public function rows(): HasMany
    {
        return $this->hasMany(ReportRow::class, 'snapshot_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(ReportSnapshot::class, 'supersedes_id');
    }
}
