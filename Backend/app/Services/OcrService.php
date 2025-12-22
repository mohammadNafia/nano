<?php

namespace App\Services;

use App\Models\OcrFile;
use App\Models\UploadLog;
use App\Models\RateLimit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class OcrService
{
    protected OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    /**
     * Download file from cloud to temp local path for OCR processing.
     */
    protected function downloadToTemp(string $cloudPath): string
    {
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/' . basename($cloudPath);
        $contents = Storage::get($cloudPath);
        file_put_contents($tempPath, $contents);

        return $tempPath;
    }

    /**
     * Clean up temporary file.
     */
    protected function cleanupTemp(string $tempPath): void
    {
        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }

    /**
     * Process an uploaded image for OCR.
     *
     * @param UploadedFile $file
     * @param string $ipAddress
     * @param int|null $userId
     * @param string|null $userAgent
     * @return array
     */
    public function processImage(
        UploadedFile $file,
        string $ipAddress,
        ?int $userId = null,
        ?string $userAgent = null
    ): array {
        $startTime = microtime(true);
        
        // Generate file hash for caching
        $fileHash = hash_file('sha256', $file->getRealPath());
        
        // Create upload log
        $uploadLog = UploadLog::create([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'original_filename' => $file->getClientOriginalName(),
            'file_hash' => $fileHash,
            'file_size' => $file->getSize(),
            'status' => UploadLog::STATUS_PROCESSING,
            'user_agent' => $userAgent,
        ]);

        // Check if file already exists (by hash)
        $existingFile = OcrFile::where('file_hash', $fileHash)->first();
        
        if ($existingFile) {
            // If already processed successfully, return from cache
            if ($existingFile->ocr_processed && $existingFile->extracted_text) {
                $processingTime = (int) ((microtime(true) - $startTime) * 1000);
                
                $uploadLog->update([
                    'ocr_file_id' => $existingFile->id,
                    'status' => UploadLog::STATUS_CACHED,
                    'from_cache' => true,
                    'processing_time_ms' => $processingTime,
                ]);

                return [
                    'success' => true,
                    'from_cache' => true,
                    'file_id' => $existingFile->id,
                    'original_filename' => $existingFile->original_filename,
                    'extracted_text' => $existingFile->extracted_text,
                    'processed_at' => $existingFile->processed_at,
                    'processing_time_ms' => $processingTime,
                ];
            }
            
            // File exists but wasn't processed successfully - retry OCR
            $ocrFile = $existingFile;
            $uploadLog->update(['ocr_file_id' => $ocrFile->id]);
        } else {
            // Store the file to cloud storage
            $storedFilename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('ocr_images', $storedFilename);

            // Create OCR file record
            $ocrFile = OcrFile::create([
                'user_id' => $userId,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'file_path' => $filePath,
                'file_hash' => $fileHash,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'ip_address' => $ipAddress,
            ]);

            $uploadLog->update(['ocr_file_id' => $ocrFile->id]);
        }

        // Download from cloud to temp local file for Ollama processing
        $tempPath = $this->downloadToTemp($ocrFile->file_path);

        // Perform OCR extraction
        $result = $this->ollamaService->extractTextFromImage($tempPath);

        // Clean up temp file
        $this->cleanupTemp($tempPath);

        $processingTime = (int) ((microtime(true) - $startTime) * 1000);

        if ($result['success']) {
            $ocrFile->update([
                'extracted_text' => $result['text'],
                'ocr_processed' => true,
                'processed_at' => Carbon::now(),
            ]);

            $uploadLog->markCompleted($processingTime);

            return [
                'success' => true,
                'from_cache' => false,
                'file_id' => $ocrFile->id,
                'original_filename' => $ocrFile->original_filename,
                'extracted_text' => $result['text'],
                'processed_at' => $ocrFile->processed_at,
                'processing_time_ms' => $processingTime,
            ];
        }

        $uploadLog->markFailed($result['error']);

        return [
            'success' => false,
            'from_cache' => false,
            'error' => $result['error'],
            'processing_time_ms' => $processingTime,
        ];
    }

    /**
     * Check rate limit for an IP address.
     *
     * @param string $ipAddress
     * @return array
     */
    public function checkRateLimit(string $ipAddress): array
    {
        return RateLimit::canPerformAction($ipAddress, RateLimit::ACTION_UPLOAD);
    }

    /**
     * Record a rate limit attempt.
     *
     * @param string $ipAddress
     * @return bool
     */
    public function recordRateLimitAttempt(string $ipAddress): bool
    {
        return RateLimit::recordAttempt($ipAddress, RateLimit::ACTION_UPLOAD);
    }

    /**
     * Get OCR service status.
     *
     * @return array
     */
    public function getServiceStatus(): array
    {
        return $this->ollamaService->getStatus();
    }

    /**
     * Get file by ID.
     *
     * @param int $fileId
     * @return OcrFile|null
     */
    public function getFile(int $fileId): ?OcrFile
    {
        return OcrFile::find($fileId);
    }

    /**
     * Get user's upload history.
     *
     * @param int|null $userId
     * @param string|null $ipAddress
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUploadHistory(?int $userId = null, ?string $ipAddress = null, int $limit = 10)
    {
        $query = UploadLog::with('ocrFile')
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($ipAddress) {
            $query->where('ip_address', $ipAddress);
        }

        return $query->get();
    }
}