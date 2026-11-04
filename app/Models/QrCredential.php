<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'santri_id',
        'token_hash',
        'plaintext_preview',
        'issued_at',
        'revoked_at',
        'is_active',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class);
    }

    public static function generateForSantri(Santri $santri): array
    {
        // Deactivate old tokens
        static::where('santri_id', $santri->id)->update([
            'is_active' => false,
            'revoked_at' => now(),
        ]);

        $plainToken = 'SNT-'.strtoupper(bin2hex(random_bytes(16)));
        $tokenHash = hash('sha256', $plainToken);

        $credential = static::create([
            'santri_id' => $santri->id,
            'token_hash' => $tokenHash,
            'plaintext_preview' => substr($plainToken, 0, 8),
            'issued_at' => now(),
            'is_active' => true,
        ]);

        return [
            'credential' => $credential,
            'token' => $plainToken,
        ];
    }
}
