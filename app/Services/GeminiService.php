<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GeminiService
{
    /**
     * Generate a descriptive prompt from an uploaded image using Gemini API.
     *
     * @param UploadedFile $image
     * @return string
     * @throws \Exception
     */
    public function generatePromptFromImage(UploadedFile $image): string
    {
        try {
            // Validate image
            if (!$image->isValid()) {
                throw new \Exception('Invalid image file');
            }

            $apiKey = config('services.gemini.key');
            
            // First, test if API key is valid by listing models
            $listResponse = Http::withOptions([
                'verify' => config('app.env') !== 'local'
            ])->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");

            Log::info('List Models Response:', [
                'status' => $listResponse->status(),
                'body' => $listResponse->json()
            ]);

            if ($listResponse->failed()) {
                throw new \Exception('Invalid API key or API request failed: ' . $listResponse->body());
            }

            // Encode image as base64
            $imageData = base64_encode(file_get_contents($image->getPathname()));
            $mimeType = $image->getMimeType();

            // Get available models from the API response
            $availableModels = $listResponse->json()['models'] ?? [];
            $visionModels = [];
            
            foreach ($availableModels as $model) {
                $modelName = $model['name'] ?? '';
                $supportedMethods = $model['supportedGenerationMethods'] ?? [];
                
                Log::info('Model found:', [
                    'name' => $modelName,
                    'methods' => $supportedMethods
                ]);
                
                // Check if model supports generateContent and has vision capability
                if (in_array('generateContent', $supportedMethods)) {
                    // Extract model ID from full name (e.g., "models/gemini-pro-vision" -> "gemini-pro-vision")
                    $modelId = str_replace('models/', '', $modelName);
                    $visionModels[] = $modelId;
                }
            }

            Log::info('Vision-capable models:', ['models' => $visionModels]);

            if (empty($visionModels)) {
                throw new \Exception('No vision-capable models found for your API key');
            }

            // Try each vision model
            foreach ($visionModels as $modelName) {
                try {
                    Log::info("Attempting Gemini model: {$modelName}");
                    
                    $response = Http::withOptions([
                        'verify' => config('app.env') !== 'local'
                    ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}", [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => 'Analyze this image and generate a detailed, descriptive prompt that could be used to recreate a similar image with an AI image generation tool. The prompt should be comprehensive, describing the visual elements, style, composition, lighting, colors, and any other relevant details. Make it detailed enough that someone could use it to generate a similar image. You must preserve the aspect ratio exactly as the original image has, or very close to it.'
                                    ],
                                    [
                                        'inline_data' => [
                                            'mime_type' => $mimeType,
                                            'data' => $imageData
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]);

                    Log::info("Response from {$modelName}:", [
                        'status' => $response->status(),
                        'body' => $response->json()
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        
                        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                            Log::info("SUCCESS with model: {$modelName}");
                            return $data['candidates'][0]['content']['parts'][0]['text'];
                        }
                    }
                    
                } catch (\Exception $e) {
                    Log::warning("Error with model {$modelName}: " . $e->getMessage());
                    continue;
                }
            }
            
            throw new \Exception('All available Gemini models failed. Check logs for details.');
            
        } catch (\Exception $e) {
            Log::error('Gemini API Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }
}