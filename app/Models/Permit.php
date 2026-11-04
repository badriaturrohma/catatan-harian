<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Permit extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'destination_type',
        'destination_name',
        'start_time',
        'duration_hours',
        'deadline',
        'reason',
        'status',
        'approver_id',
        'approved_at',
        'rejection_reason',
        'revision',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'deadline' => 'datetime',
        'approved_at' => 'datetime',
        'duration_hours' => 'integer',
        'revision' => 'integer',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function journey(): HasOne
    {
        return $this->hasOne(Journey::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isKampus(): bool
    {
        return $this->destination_type === 'kampus';
    }
}
