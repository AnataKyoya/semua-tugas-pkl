<?php

namespace App\Libraries;

use Generator;
use Exception;

/**
 * Gemini SDK – Enhanced Version
 * - No cURL
 * - API Key from .env (GEMINI_API_KEY)
 * - Chat Completion
 * - Streaming SSE
 * - File Upload (Gemini Files API)
 * - Safety Settings
 * - Generation Config
 */
class Gemini
{
    private string $apiKey;
    private string $model;
    private int $timeout = 30;

    private string $baseModels = "https://generativelanguage.googleapis.com/v1/models";
    private string $baseFiles  = "https://generativelanguage.googleapis.com/v1/files";

    private ?array $safetySettings = null;
    private ?array $generationConfig = null;

    public function __construct(?string $model = null)
    {
        $this->apiKey = getenv('GEMINI_API_KEY') ?: '';

        if (!$this->apiKey) {
            throw new Exception("Missing GEMINI_API_KEY in .env");
        }

        $this->model = $model ?: getenv('GEMINI_MODEL') ?: 'gemini-1.5-flash';
    }

    /* =========================
       CONFIGURATION METHODS
    ========================= */

    /**
     * Set safety settings untuk content filtering
     * 
     * @param array $settings Array of SafetySetting
     * @return self
     */
    public function setSafetySettings(array $settings): self
    {
        $this->safetySettings = array_map(function ($setting) {
            if ($setting instanceof SafetySetting) {
                return $setting->toArray();
            }
            return $setting;
        }, $settings);

        return $this;
    }

    /**
     * Set generation config untuk output customization
     * 
     * @param GenerationConfig|array $config
     * @return self
     */
    public function setGenerationConfig($config): self
    {
        if ($config instanceof GenerationConfig) {
            $this->generationConfig = $config->toArray();
        } else {
            $this->generationConfig = $config;
        }

        return $this;
    }

    /**
     * Quick method untuk disable semua safety filters
     * 
     * @return self
     */
    public function disableAllSafetyFilters(): self
    {
        $this->safetySettings = [
            ['category' => HarmCategory::HARM_CATEGORY_HARASSMENT, 'threshold' => HarmBlockThreshold::BLOCK_NONE],
            ['category' => HarmCategory::HARM_CATEGORY_HATE_SPEECH, 'threshold' => HarmBlockThreshold::BLOCK_NONE],
            ['category' => HarmCategory::HARM_CATEGORY_SEXUALLY_EXPLICIT, 'threshold' => HarmBlockThreshold::BLOCK_NONE],
            ['category' => HarmCategory::HARM_CATEGORY_DANGEROUS_CONTENT, 'threshold' => HarmBlockThreshold::BLOCK_NONE],
        ];

        return $this;
    }

    /* =========================
       CHAT COMPLETION
    ========================= */

    public function complete(array $messages): string
    {
        $payload = $this->buildPayload($messages);

        $res = $this->post(
            $this->modelEndpoint(false),
            $payload
        );

        return $res["candidates"][0]["content"]["parts"][0]["text"] ?? "";
    }

    /* =========================
       STREAMING (ASYNC)
    ========================= */

    public function stream(array $messages): Generator
    {
        $payload = $this->buildPayload($messages);

        $fp = $this->openStream(
            $this->modelEndpoint(true),
            $payload
        );

        return $this->readStream($fp);
    }

    /* =========================
       FILE UPLOAD (FILES API)
    ========================= */

    public function upload(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }

        $mime = mime_content_type($filePath) ?: "application/octet-stream";
        $data = file_get_contents($filePath);
        $name = basename($filePath);

        // Step 1 – init upload
        $initHeaders = [
            "X-Goog-Upload-Protocol: resumable",
            "X-Goog-Upload-Command: start",
            "X-Goog-Upload-Header-Content-Length: " . strlen($data),
            "X-Goog-Upload-Header-Content-Type: $mime",
            "Content-Type: application/json"
        ];

        $initBody = json_encode([
            "file" => ["display_name" => $name]
        ]);

        $ctx = stream_context_create([
            "http" => [
                "method" => "POST",
                "header" => implode("\r\n", $initHeaders) . "\r\n",
                "content" => $initBody,
                "timeout" => $this->timeout
            ]
        ]);

        $initUrl = "{$this->baseFiles}?key={$this->apiKey}";
        $res = file_get_contents($initUrl, false, $ctx);

        if ($res === false) {
            throw new Exception("Upload init failed");
        }

        $uploadUrl = null;
        foreach ($http_response_header ?? [] as $h) {
            if (stripos($h, "X-Goog-Upload-URL:") === 0) {
                $uploadUrl = trim(substr($h, 18));
            }
        }

        if (!$uploadUrl) {
            throw new Exception("Upload URL missing");
        }

        // Step 2 – send file
        $uploadHeaders = [
            "X-Goog-Upload-Command: upload, finalize",
            "X-Goog-Upload-Offset: 0",
            "Content-Length: " . strlen($data)
        ];

        $ctx2 = stream_context_create([
            "http" => [
                "method" => "POST",
                "header" => implode("\r\n", $uploadHeaders) . "\r\n",
                "content" => $data,
                "timeout" => $this->timeout
            ]
        ]);

        $final = file_get_contents($uploadUrl, false, $ctx2);

        if ($final === false) {
            throw new Exception("File upload failed");
        }

        return json_decode($final, true) ?? [];
    }

    public function filePart(string $fileUri, string $mime): array
    {
        return [
            "file_data" => [
                "file_uri" => $fileUri,
                "mime_type" => $mime
            ]
        ];
    }

    /* =========================
       INTERNAL HELPERS
    ========================= */

    private function buildPayload(array $messages): array
    {
        $payload = [
            "contents" => $this->formatMessages($messages)
        ];

        // Add safety settings if configured
        if ($this->safetySettings !== null) {
            $payload["safetySettings"] = $this->safetySettings;
        }

        // Add generation config if configured
        if ($this->generationConfig !== null) {
            $payload["generationConfig"] = $this->generationConfig;
        }

        return $payload;
    }

    /* =========================
       INTERNAL HTTP
    ========================= */

    private function modelEndpoint(bool $stream): string
    {
        $mode = $stream ? "streamGenerateContent" : "generateContent";
        $alt = $stream ? "&alt=sse" : "";
        return "{$this->baseModels}/{$this->model}:{$mode}?key={$this->apiKey}{$alt}";
    }

    private function post(string $url, array $payload): array
    {
        $ctx = stream_context_create([
            "http" => [
                "method" => "POST",
                "header" => "Content-Type: application/json\r\n",
                "content" => json_encode($payload),
                "timeout" => $this->timeout
            ]
        ]);

        $res = file_get_contents($url, false, $ctx);

        if ($res === false) {
            throw new Exception("HTTP request failed");
        }

        return json_decode($res, true) ?? [];
    }

    private function openStream(string $url, array $payload)
    {
        $ctx = stream_context_create([
            "http" => [
                "method" => "POST",
                "header" => "Content-Type: application/json\r\n",
                "content" => json_encode($payload),
                "timeout" => 60
            ],
            "ssl" => [
                "verify_peer" => true,
                "verify_peer_name" => true
            ]
        ]);

        $fp = @fopen($url, "r", false, $ctx);

        if (!$fp) {
            $error = error_get_last();
            $errorMsg = $error ? $error['message'] : 'Unknown error';
            throw new Exception("Stream open failed: " . $errorMsg);
        }

        // Set blocking mode TRUE untuk stream
        stream_set_blocking($fp, true);

        // Set read timeout yang panjang
        stream_set_timeout($fp, 120);

        return $fp;
    }

    private function readStream($fp): Generator
    {
        $buffer = "";
        $lineCount = 0;

        while (!feof($fp)) {
            $line = fgets($fp);

            if ($line === false) {
                // Check for timeout
                $info = stream_get_meta_data($fp);
                if ($info['timed_out']) {
                    log_message('error', 'Stream timeout');
                    break;
                }
                continue;
            }

            $lineCount++;
            $line = trim($line);

            log_message('debug', "Stream line #{$lineCount}: " . substr($line, 0, 200));

            // Skip empty lines
            if ($line === '') {
                continue;
            }

            // SSE format: "data: {...}"
            if (strpos($line, 'data: ') === 0) {
                $json = trim(substr($line, 6));

                // Skip [DONE] marker
                if ($json === '[DONE]') {
                    log_message('debug', 'Stream [DONE] received');
                    break;
                }

                // Try to parse JSON
                $decoded = json_decode($json, true);

                if ($decoded !== null) {
                    log_message('debug', 'Yielding chunk: ' . json_encode($decoded));
                    yield $decoded;
                } else {
                    log_message('warning', 'Failed to decode JSON: ' . substr($json, 0, 200));
                }
            }
        }

        log_message('debug', "Stream ended. Total lines: {$lineCount}");
        fclose($fp);
    }

    private function formatMessages(array $messages): array
    {
        return array_map(fn($msg) => [
            "role" => $msg["role"],
            "parts" => is_array($msg["content"])
                ? $msg["content"]
                : [["text" => $msg["content"]]]
        ], $messages);
    }
}

/* =========================
   ENUMS & HELPER CLASSES
========================= */

/**
 * Harm Categories untuk Safety Settings
 */
class HarmCategory
{
    const HARM_CATEGORY_UNSPECIFIED = 'HARM_CATEGORY_UNSPECIFIED';
    const HARM_CATEGORY_DEROGATORY = 'HARM_CATEGORY_DEROGATORY';
    const HARM_CATEGORY_TOXICITY = 'HARM_CATEGORY_TOXICITY';
    const HARM_CATEGORY_VIOLENCE = 'HARM_CATEGORY_VIOLENCE';
    const HARM_CATEGORY_SEXUAL = 'HARM_CATEGORY_SEXUAL';
    const HARM_CATEGORY_MEDICAL = 'HARM_CATEGORY_MEDICAL';
    const HARM_CATEGORY_DANGEROUS = 'HARM_CATEGORY_DANGEROUS';
    const HARM_CATEGORY_HARASSMENT = 'HARM_CATEGORY_HARASSMENT';
    const HARM_CATEGORY_HATE_SPEECH = 'HARM_CATEGORY_HATE_SPEECH';
    const HARM_CATEGORY_SEXUALLY_EXPLICIT = 'HARM_CATEGORY_SEXUALLY_EXPLICIT';
    const HARM_CATEGORY_DANGEROUS_CONTENT = 'HARM_CATEGORY_DANGEROUS_CONTENT';
}

/**
 * Harm Block Thresholds
 */
class HarmBlockThreshold
{
    const HARM_BLOCK_THRESHOLD_UNSPECIFIED = 'HARM_BLOCK_THRESHOLD_UNSPECIFIED';
    const BLOCK_LOW_AND_ABOVE = 'BLOCK_LOW_AND_ABOVE';
    const BLOCK_MEDIUM_AND_ABOVE = 'BLOCK_MEDIUM_AND_ABOVE';
    const BLOCK_ONLY_HIGH = 'BLOCK_ONLY_HIGH';
    const BLOCK_NONE = 'BLOCK_NONE';
}

/**
 * Safety Setting Class
 */
class SafetySetting
{
    private string $category;
    private string $threshold;

    public function __construct(string $category, string $threshold)
    {
        $this->category = $category;
        $this->threshold = $threshold;
    }

    public function toArray(): array
    {
        return [
            'category' => $this->category,
            'threshold' => $this->threshold
        ];
    }

    public static function create(string $category, string $threshold): self
    {
        return new self($category, $threshold);
    }
}

/**
 * Generation Config Class
 */
class GenerationConfig
{
    private ?float $temperature = null;
    private ?int $topK = null;
    private ?float $topP = null;
    private ?int $maxOutputTokens = null;
    private ?array $stopSequences = null;
    private ?int $candidateCount = null;

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function setTopK(int $topK): self
    {
        $this->topK = $topK;
        return $this;
    }

    public function setTopP(float $topP): self
    {
        $this->topP = $topP;
        return $this;
    }

    public function setMaxOutputTokens(int $maxOutputTokens): self
    {
        $this->maxOutputTokens = $maxOutputTokens;
        return $this;
    }

    public function setStopSequences(array $stopSequences): self
    {
        $this->stopSequences = $stopSequences;
        return $this;
    }

    public function setCandidateCount(int $candidateCount): self
    {
        $this->candidateCount = $candidateCount;
        return $this;
    }

    public function toArray(): array
    {
        $config = [];

        if ($this->temperature !== null) {
            $config['temperature'] = $this->temperature;
        }
        if ($this->topK !== null) {
            $config['topK'] = $this->topK;
        }
        if ($this->topP !== null) {
            $config['topP'] = $this->topP;
        }
        if ($this->maxOutputTokens !== null) {
            $config['maxOutputTokens'] = $this->maxOutputTokens;
        }
        if ($this->stopSequences !== null) {
            $config['stopSequences'] = $this->stopSequences;
        }
        if ($this->candidateCount !== null) {
            $config['candidateCount'] = $this->candidateCount;
        }

        return $config;
    }

    public static function create(): self
    {
        return new self();
    }
}
