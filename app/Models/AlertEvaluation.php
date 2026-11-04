<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'episode_id',
        'rule_version_id',
        'evaluated_at',
        'state',
        'change_reason',
    ];

    protected $casts = [
        'evaluated_at' => 'datetime',
    ];

    public function episode(): BelongsTo
    {
        return $this->belongsTo(AlertEpisode::class, 'episode_id');
    }

    public function ruleVersion(): BelongsTo
    {
        return $this->belongsTo(RuleVersion::class);
    }
}
