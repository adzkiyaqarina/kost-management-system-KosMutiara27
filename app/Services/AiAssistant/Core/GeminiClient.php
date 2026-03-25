<?php

namespace App\Services\AiAssistant\Core;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct()
    {
        $this->apiKey = AiConfig::getApiKey();
    }

    public function generateResponse(string $prompt): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key Gemini belum disetting di .env (GEMINI_API_KEY)'
            ];
        }

        $model = AiConfig::MODEL_NAME; // "gemini-3-pro-preview"

        try {
            $response = $this->callApi($model, $prompt);
            
            // Fallback logic if 404 (Model not found)
            if ($response->status() === 404 && $model !== AiConfig::FALLBACK_MODEL) {
                Log::warning("Gemini Model $model not found. Falling back to " . AiConfig::FALLBACK_MODEL);
                $response = $this->callApi(AiConfig::FALLBACK_MODEL, $prompt);
            }

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak dapat menghasilkan jawaban.';
                return [
                    'success' => true,
                    'text' => $text
                ];
            }

            return [
                'success' => false,
                'message' => 'Gemini API Error: ' . $response->body()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection Error: ' . $e->getMessage()
            ];
        }
    }

    protected function callApi(string $modelName, string $prompt)
    {
        $url = $this->baseUrl . $modelName . ':generateContent?key=' . $this->apiKey;
        
        return Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => AiConfig::TEMPERATURE,
                'maxOutputTokens' => AiConfig::MAX_OUTPUT_TOKENS
            ]
        ]);
    }
}
