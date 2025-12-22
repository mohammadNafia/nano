<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ocr_file_id',
        'ip_address',
        'original_filename',
        'file_hash',
        'file_size',
        'status',
        'from_cache',
        'error_message',
        'user_agent',
        'processing_time_ms',
    ];

    protected $casts = [
        'from_cache' => 'boolean',
        'file_size' => 'integer',
        'processing_time_ms' => 'integer',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CACHED = 'cached';

    /**
     * Get the user that owns the log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated OCR file.
     */
    public function ocrFile()
    {
        return $this->belongsTo(OcrFile::class);
    }

    /**
     * Mark as completed.
     */
    public function markCompleted(int $processingTimeMs = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processing_time_ms' => $processingTimeMs,
        ]);
    }

    /**
     * Mark as failed.
     */
    public function markFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
        ]);
    }

    /**
     * Mark as served from cache.
     */
    public function markCached(): void
    {
        $this->update([
            'status' => self::STATUS_CACHED,
            'from_cache' => true,
        ]);
    }
}

