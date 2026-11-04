<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['campus_id', 'name', 'type', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OfficerAssignment::class);
    }

    public function journeyEvents(): HasMany
    {
        return $this->hasMany(JourneyEvent::class);
    }

    public function isPondok(): bool
    {
        return $this->type === 'pondok';
    }

    public function isKampus(): bool
    {
        return $this->type === 'kampus';
    }
}
