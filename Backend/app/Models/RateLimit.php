<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RateLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'action',
        'attempts',
        'max_attempts',
        'window_start',
        'blocked_until',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'window_start' => 'datetime',
        'blocked_until' => 'datetime',
    ];

    const ACTION_UPLOAD = 'upload';
    const DEFAULT_MAX_ATTEMPTS = 5;
    const WINDOW_MINUTES = 60; // 1 hour window
    const BLOCK_MINUTES = 60; // Block for 1 hour

    /**
     * Check if this IP is currently blocked.
     */
    public function isBlocked(): bool
    {
        return $this->blocked_until && $this->blocked_until->isFuture();
    }

    /**
     * Get remaining attempts.
     */
    public function getRemainingAttempts(): int
    {
        if ($this->isBlocked()) {
            return 0;
        }
        
        // Reset if window has expired
        if ($this->window_start && $this->window_start->addMinutes(self::WINDOW_MINUTES)->isPast()) {
            return $this->max_attempts;
        }
        
        return max(0, $this->max_attempts - $this->attempts);
    }

    /**
     * Increment attempts and check if should block.
     */
    public function incrementAttempts(): bool
    {
        // If window expired, reset
        if (!$this->window_start || $this->window_start->addMinutes(self::WINDOW_MINUTES)->isPast()) {
            $this->window_start = Carbon::now();
            $this->attempts = 1;
            $this->blocked_until = null;
            $this->save();
            return true;
        }

        $this->attempts++;
        
        if ($this->attempts >= $this->max_attempts) {
            $this->blocked_until = Carbon::now()->addMinutes(self::BLOCK_MINUTES);
            $this->save();
            return false;
        }
        
        $this->save();
        return true;
    }

    /**
     * Find or create rate limit for an IP.
     */
    public static function findOrCreateForIp(string $ipAddress, string $action = self::ACTION_UPLOAD): self
    {
        return static::firstOrCreate(
            ['ip_address' => $ipAddress, 'action' => $action],
            [
                'attempts' => 0,
                'max_attempts' => self::DEFAULT_MAX_ATTEMPTS,
                'window_start' => Carbon::now(),
            ]
        );
    }

    /**
     * Check if an IP can perform an action.
     */
    public static function canPerformAction(string $ipAddress, string $action = self::ACTION_UPLOAD): array
    {
        $limit = static::findOrCreateForIp($ipAddress, $action);
        
        if ($limit->isBlocked()) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'blocked_until' => $limit->blocked_until,
                'message' => 'Rate limit exceeded. Please try again later.',
            ];
        }
        
        $remaining = $limit->getRemainingAttempts();
        
        return [
            'allowed' => $remaining > 0,
            'remaining' => $remaining,
            'blocked_until' => null,
            'message' => $remaining > 0 ? null : 'Rate limit exceeded.',
        ];
    }

    /**
     * Record an action attempt.
     */
    public static function recordAttempt(string $ipAddress, string $action = self::ACTION_UPLOAD): bool
    {
        $limit = static::findOrCreateForIp($ipAddress, $action);
        return $limit->incrementAttempts();
    }
}

