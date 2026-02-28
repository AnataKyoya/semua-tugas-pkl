<?php

namespace App\Libraries;

use Exception;

class OpenRouter
{
    private string $apiKey;
    private string $model;
    private int $timeout = 30;
    private string $baseUrl = "https://openrouter.ai/api/v1/chat/completions";
    private array $defaultConfig = [];

    public function __construct(?string $model = null)
    {
        $this->apiKey = getenv('OPENROUTER_API_KEY') ?: '';

        if (!$this->apiKey) {
            throw new Exception("Missing OPENROUTER_API_KEY in .env");
        }

        $this->model = $model ?: getenv('OPENROUTER_MODEL') ?: 'deepseek/deepseek-r1-0528:free';
    }

    /**
     * Set default generation configuration
     * 
     * @param array $config Configuration array with camelCase keys
     * @return self
     * 
     * @example
     * $router->setGenerationConfig([
     *     'temperature' => 0.7,
     *     'topP' => 0.9,
     *     'maxTokens' => 500
     * ]);
     */
    public function setGenerationConfig(array $config): self
    {
        $this->defaultConfig = $this->normalizeConfig($config);
        return $this;
    }

    /**
     * Get current generation configuration
     * 
     * @return array
     */
    public function getGenerationConfig(): array
    {
        return $this->defaultConfig;
    }

    /**
     * Reset generation configuration to default
     * 
     * @return self
     */
    public function resetGenerationConfig(): self
    {
        $this->defaultConfig = [];
        return $this;
    }

    /**
     * Complete a chat request (non-streaming)
     * 
     * @param array $messages Array of message objects
     * @param array $options Additional parameters (will override defaultConfig)
     * @return string Response content
     */
    public function complete(array $messages, array $options = []): string
    {
        $mergedOptions = array_merge($this->defaultConfig, $this->normalizeConfig($options));
        $payload = $this->buildPayload($messages, $mergedOptions);

        $res = $this->post($this->baseUrl, $payload);

        return $res['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Stream completion with callback for each chunk
     * 
     * @param array $messages Array of message objects
     * @param callable $callback Function called for each chunk: function(string $chunk, bool $isDone)
     * @param array $options Additional parameters (will override defaultConfig)
     * @return void
     */
    public function stream(array $messages, callable $callback, array $options = []): void
    {
        $mergedOptions = array_merge($this->defaultConfig, $this->normalizeConfig($options));
        $payload = $this->buildPayload($messages, array_merge($mergedOptions, ['stream' => true]));

        $ctx = stream_context_create([
            "http" => [
                "method" => "POST",
                "header" =>
                "Authorization: Bearer {$this->apiKey}\r\n" .
                    "Content-Type: application/json\r\n",
                "content" => json_encode($payload),
                "timeout" => $this->timeout,
                "ignore_errors" => true
            ],
            "ssl" => [
                "verify_peer" => true,
                "verify_peer_name" => true,
                "allow_self_signed" => false,
                "SNI_enabled" => true,
                "crypto_method" => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
            ]
        ]);

        $stream = fopen($this->baseUrl, 'r', false, $ctx);

        if ($stream === false) {
            throw new Exception("Failed to open stream");
        }

        try {
            while (!feof($stream)) {
                $line = fgets($stream);

                if ($line === false) {
                    continue;
                }

                $line = trim($line);

                if (empty($line)) {
                    continue;
                }

                if (strpos($line, 'data: ') === 0) {
                    $data = substr($line, 6);

                    if ($data === '[DONE]') {
                        $callback('', true);
                        break;
                    }

                    $json = json_decode($data, true);

                    if (isset($json['choices'][0]['delta']['content'])) {
                        $chunk = $json['choices'][0]['delta']['content'];
                        $callback($chunk, false);
                    }

                    if (
                        isset($json['choices'][0]['finish_reason']) &&
                        $json['choices'][0]['finish_reason'] !== null
                    ) {
                        $callback('', true);
                        break;
                    }
                }
            }
        } finally {
            fclose($stream);
        }
    }

    /**
     * Stream completion and return full text
     * 
     * @param array $messages Array of message objects
     * @param callable|null $onChunk Optional callback for each chunk
     * @param array $options Additional parameters (will override defaultConfig)
     * @return string Complete response text
     */
    public function streamComplete(array $messages, ?callable $onChunk = null, array $options = []): string
    {
        $fullText = '';

        $this->stream($messages, function ($chunk, $isDone) use (&$fullText, $onChunk) {
            if (!$isDone) {
                $fullText .= $chunk;

                if ($onChunk) {
                    $onChunk($chunk, $isDone);
                }
            } elseif ($onChunk) {
                $onChunk('', $isDone);
            }
        }, $options);

        return $fullText;
    }

    /**
     * Normalize config keys from camelCase to snake_case
     * 
     * @param array $config Config with camelCase keys
     * @return array Config with snake_case keys
     */
    private function normalizeConfig(array $config): array
    {
        $normalized = [];

        $mapping = [
            'temperature' => 'temperature',
            'topP' => 'top_p',
            'topK' => 'top_k',
            'frequencyPenalty' => 'frequency_penalty',
            'presencePenalty' => 'presence_penalty',
            'repetitionPenalty' => 'repetition_penalty',
            'minP' => 'min_p',
            'topA' => 'top_a',
            'seed' => 'seed',
            'maxTokens' => 'max_tokens',
            'logitBias' => 'logit_bias',
            'logprobs' => 'logprobs',
            'topLogprobs' => 'top_logprobs',
            'responseFormat' => 'response_format',
            'structuredOutputs' => 'structured_outputs',
            'stop' => 'stop',
            'tools' => 'tools',
            'toolChoice' => 'tool_choice',
            'parallelToolCalls' => 'parallel_tool_calls',
            'verbosity' => 'verbosity',
            'model' => 'model',
            'stream' => 'stream'
        ];

        foreach ($config as $key => $value) {
            // Check if camelCase key exists in mapping
            if (isset($mapping[$key])) {
                $normalized[$mapping[$key]] = $value;
            }
            // Otherwise use the key as-is (might already be snake_case)
            else {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    /**
     * Build payload with all supported parameters
     * 
     * @param array $messages Messages array
     * @param array $options Additional options
     * @return array Complete payload
     */
    private function buildPayload(array $messages, array $options = []): array
    {
        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages
        ];

        // Sampling parameters
        $this->addIfSet($payload, $options, 'temperature', 'float', 0.0, 2.0);
        $this->addIfSet($payload, $options, 'top_p', 'float', 0.0, 1.0);
        $this->addIfSet($payload, $options, 'top_k', 'int', 0);
        $this->addIfSet($payload, $options, 'frequency_penalty', 'float', -2.0, 2.0);
        $this->addIfSet($payload, $options, 'presence_penalty', 'float', -2.0, 2.0);
        $this->addIfSet($payload, $options, 'repetition_penalty', 'float', 0.0, 2.0);
        $this->addIfSet($payload, $options, 'min_p', 'float', 0.0, 1.0);
        $this->addIfSet($payload, $options, 'top_a', 'float', 0.0, 1.0);

        // Generation parameters
        $this->addIfSet($payload, $options, 'seed', 'int');
        $this->addIfSet($payload, $options, 'max_tokens', 'int', 1);

        // Advanced parameters
        if (isset($options['logit_bias']) && is_array($options['logit_bias'])) {
            $payload['logit_bias'] = $options['logit_bias'];
        }

        $this->addIfSet($payload, $options, 'logprobs', 'bool');
        $this->addIfSet($payload, $options, 'top_logprobs', 'int', 0, 20);

        // Response format
        if (isset($options['response_format'])) {
            $payload['response_format'] = $options['response_format'];
        }

        $this->addIfSet($payload, $options, 'structured_outputs', 'bool');

        // Stop sequences
        if (isset($options['stop'])) {
            $payload['stop'] = is_array($options['stop']) ? $options['stop'] : [$options['stop']];
        }

        // Tool calling
        if (isset($options['tools']) && is_array($options['tools'])) {
            $payload['tools'] = $options['tools'];
        }

        if (isset($options['tool_choice'])) {
            $payload['tool_choice'] = $options['tool_choice'];
        }

        $this->addIfSet($payload, $options, 'parallel_tool_calls', 'bool');

        // Verbosity
        if (
            isset($options['verbosity']) &&
            in_array($options['verbosity'], ['low', 'medium', 'high', 'max'])
        ) {
            $payload['verbosity'] = $options['verbosity'];
        }

        // Stream flag
        if (isset($options['stream'])) {
            $payload['stream'] = (bool)$options['stream'];
        }

        return $payload;
    }

    /**
     * Add parameter to payload if set in options
     */
    private function addIfSet(array &$payload, array $options, string $key, string $type, $min = null, $max = null): void
    {
        if (!isset($options[$key])) {
            return;
        }

        $value = $options[$key];

        switch ($type) {
            case 'float':
                $value = (float)$value;
                if ($min !== null && $value < $min) $value = $min;
                if ($max !== null && $value > $max) $value = $max;
                break;
            case 'int':
                $value = (int)$value;
                if ($min !== null && $value < $min) $value = $min;
                if ($max !== null && $value > $max) $value = $max;
                break;
            case 'bool':
                $value = (bool)$value;
                break;
        }

        $payload[$key] = $value;
    }

    private function post(string $url, array $payload): array
    {
        $ctx = stream_context_create([
            "http" => [
                "method" => "POST",
                "header" =>
                "Authorization: Bearer {$this->apiKey}\r\n" .
                    "Content-Type: application/json\r\n",
                "content" => json_encode($payload),
                "timeout" => $this->timeout,
                "ignore_errors" => true
            ],
            "ssl" => [
                "verify_peer" => true,
                "verify_peer_name" => true,
                "allow_self_signed" => false,
                "SNI_enabled" => true,
                "crypto_method" => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
            ]
        ]);

        $res = file_get_contents($url, false, $ctx);

        if ($res === false) {
            throw new Exception("HTTP request failed");
        }

        $decoded = json_decode($res, true);

        if (isset($decoded['error'])) {
            throw new Exception("API Error: " . ($decoded['error']['message'] ?? 'Unknown error'));
        }

        return $decoded ?? [];
    }
}
