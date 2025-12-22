<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OcrService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class OcrController extends Controller
{
    protected OcrService $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Upload and process an image for OCR extraction.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $ipAddress = $request->ip();
        $userId = auth()->id();
        $userAgent = $request->userAgent();

        // Check rate limit
        $rateLimit = $this->ocrService->checkRateLimit($ipAddress);
        
        if (!$rateLimit['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $rateLimit['message'],
                'remaining_attempts' => 0,
                'blocked_until' => $rateLimit['blocked_until']?->toIso8601String(),
            ], 429);
        }

        // Record the attempt
        $this->ocrService->recordRateLimitAttempt($ipAddress);

        // Process the image
        $result = $this->ocrService->processImage(
            $request->file('image'),
            $ipAddress,
            $userId,
            $userAgent
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'OCR processing failed',
                'processing_time_ms' => $result['processing_time_ms'] ?? null,
            ], 500);
        }

        // Get updated rate limit info
        $updatedRateLimit = $this->ocrService->checkRateLimit($ipAddress);

        return response()->json([
            'success' => true,
            'message' => $result['from_cache'] ? 'Text extracted from cache' : 'Text extracted successfully',
            'data' => [
                'file_id' => $result['file_id'],
                'original_filename' => $result['original_filename'],
                'extracted_text' => $result['extracted_text'],
                'from_cache' => $result['from_cache'],
                'processed_at' => $result['processed_at']?->toIso8601String(),
                'processing_time_ms' => $result['processing_time_ms'],
            ],
            'rate_limit' => [
                'remaining_attempts' => $updatedRateLimit['remaining'],
            ],
        ]);
    }

    /**
     * Get OCR result by file ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $file = $this->ocrService->getFile($id);

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $file->id,
                'original_filename' => $file->original_filename,
                'extracted_text' => $file->extracted_text,
                'ocr_processed' => $file->ocr_processed,
                'processed_at' => $file->processed_at?->toIso8601String(),
                'created_at' => $file->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get upload history.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $ipAddress = $request->ip();
        $limit = min((int) $request->get('limit', 10), 50);

        $history = $this->ocrService->getUploadHistory($userId, $ipAddress, $limit);

        return response()->json([
            'success' => true,
            'data' => $history->map(function ($log) {
                return [
                    'id' => $log->id,
                    'filename' => $log->original_filename,
                    'status' => $log->status,
                    'from_cache' => $log->from_cache,
                    'processing_time_ms' => $log->processing_time_ms,
                    'extracted_text' => $log->ocrFile?->extracted_text,
                    'created_at' => $log->created_at->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Get OCR service status.
     *
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        $status = $this->ocrService->getServiceStatus();

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Get rate limit status for current IP.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function rateLimit(Request $request): JsonResponse
    {
        $ipAddress = $request->ip();
        $rateLimit = $this->ocrService->checkRateLimit($ipAddress);

        return response()->json([
            'success' => true,
            'data' => [
                'allowed' => $rateLimit['allowed'],
                'remaining_attempts' => $rateLimit['remaining'],
                'blocked_until' => $rateLimit['blocked_until']?->toIso8601String(),
            ],
        ]);
    }
}

