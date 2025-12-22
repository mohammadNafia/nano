<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OcrFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_hash',
        'mime_type',
        'file_size',
        'extracted_text',
        'ocr_processed',
        'processed_at',
        'ip_address',
    ];

    protected $casts = [
        'ocr_processed' => 'boolean',
        'processed_at' => 'datetime',
        'file_size' => 'integer',
    ];

    /**
     * Get the user that owns the file.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get upload logs for this file.
     */
    public function uploadLogs()
    {
        return $this->hasMany(UploadLog::class);
    }

    /**
     * Find a file by its hash (for caching).
     */
    public static function findByHash(string $hash): ?self
    {
        return static::where('file_hash', $hash)
            ->where('ocr_processed', true)
            ->first();
    }

    /**
     * Check if OCR has been processed for this file.
     */
    public function isProcessed(): bool
    {
        return $this->ocr_processed && !empty($this->extracted_text);
    }
}

