<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OllamaService
{
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.url', 'http://localhost:11434');
        $this->model = config('services.ollama.model', 'llava');
        $this->timeout = config('services.ollama.timeout', 120);
    }

    /**
     * Extract text from an image using LLaVA model.
     *
     * @param string $imagePath Path to the image file
     * @return array{success: bool, text: string|null, error: string|null}
     */
    public function extractTextFromImage(string $imagePath): array
    {
        try {
            if (!file_exists($imagePath)) {
                Log::error('OCR - Image file not found', ['path' => $imagePath]);
                return [
                    'success' => false,
                    'text' => null,
                    'error' => 'Image file not found',
                ];
            }

            // Read and encode the image
            $imageData = file_get_contents($imagePath);
            $base64Image = base64_encode($imageData);
            
            // Detect mime type for the image
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $imagePath);
            finfo_close($finfo);
            
            // Get image dimensions
            $imageInfo = @getimagesize($imagePath);
            
            Log::info('OCR - Image details', [
                'path' => $imagePath,
                'image_size_bytes' => strlen($imageData),
                'base64_length' => strlen($base64Image),
                'mime_type' => $mimeType,
                'dimensions' => $imageInfo ? "{$imageInfo[0]}x{$imageInfo[1]}" : 'unknown',
                'model' => $this->model,
            ]);

            // Use chat API with system message for better OCR behavior
            $systemPrompt = "You are an OCR system. Read text from images. " .
                "Output the text you see, line by line. " .
                "Do not format as tables. Do not add dashes or separators. " .
                "Do not say you cannot read the text. Just output what you see.";
            
            $userPrompt = "Read all visible text in this image. List each text item on a new line:";

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/chat", [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => $userPrompt,
                            'images' => [$base64Image],
                        ]
                    ],
                    'stream' => false,
                    'options' => [
                        'temperature' => 0.0,
                        'num_predict' => 8192,
                    ],
                ]);

            Log::info('OCR - Ollama response status', [
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if (!$response->successful()) {
                Log::error('Ollama API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                
                return [
                    'success' => false,
                    'text' => null,
                    'error' => 'Failed to connect to Ollama API: ' . $response->status(),
                ];
            }

            $data = $response->json();
            
            Log::info('OCR - Ollama raw response', [
                'full_response' => $data,
            ]);

            // Chat API returns response in message.content
            $extractedText = $data['message']['content'] ?? $data['response'] ?? '';

            // If model says no text, return that
            if (empty(trim($extractedText))) {
                return [
                    'success' => true,
                    'text' => 'No text found in image.',
                    'error' => null,
                ];
            }

            return [
                'success' => true,
                'text' => trim($extractedText),
                'error' => null,
            ];

        } catch (Exception $e) {
            Log::error('OCR extraction failed', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath,
            ]);

            return [
                'success' => false,
                'text' => null,
                'error' => 'OCR extraction failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if Ollama service is available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");
            return $response->successful();
        } catch (Exception $e) {
            Log::warning('Ollama service unavailable', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if the required model is available.
     *
     * @return bool
     */
    public function isModelAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");
            
            if (!$response->successful()) {
                return false;
            }

            $data = $response->json();
            $models = $data['models'] ?? [];
            
            foreach ($models as $model) {
                if (str_contains($model['name'] ?? '', $this->model)) {
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get service status.
     *
     * @return array
     */
    public function getStatus(): array
    {
        $serviceAvailable = $this->isAvailable();
        $modelAvailable = $serviceAvailable ? $this->isModelAvailable() : false;

        return [
            'service_available' => $serviceAvailable,
            'model_available' => $modelAvailable,
            'model_name' => $this->model,
            'base_url' => $this->baseUrl,
        ];
    }
}

