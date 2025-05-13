<?php
namespace AlgoliaIndexLLMAddon\LLM\Providers;

use AlgoliaIndexLLMAddon\Interfaces\LLMStream;
use AlgoliaIndexLLMAddon\Interfaces\Prompt;

class OpenAIStreamProvider implements LLMStream
{
    private string $apiKey;
    private int $temperature = 0;
    private string $model = 'gpt-4o-mini';

    public function __construct(
        string $apiKey,
        array $settings = []
    ) {
        $this->apiKey      = $apiKey;
        $this->model       = $settings['model'] ?? $this->model;
        $this->temperature = $settings['temperature'] ?? $this->temperature;
    }

    public function stream(Prompt $prompt): void
    {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        $payload = json_encode([
            'model'       => $this->model,
            'stream'      => true,
            'temperature' => $this->temperature,
            'messages'    => $prompt->toMessages(),
        ]);

        // Buffer to accumulate partial SSE frames
        $sseBuf = '';

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER    => [
                "Authorization: Bearer {$this->apiKey}",
                'Content-Type: application/json',
            ],
            CURLOPT_POST          => true,
            CURLOPT_POSTFIELDS    => $payload,
            CURLOPT_WRITEFUNCTION => function($curl, $chunk) use (&$sseBuf) {
                $sseBuf .= $chunk;

                // process each complete SSE frame
                while (false !== ($pos = strpos($sseBuf, "\n\n"))) {
                    $frame    = substr($sseBuf, 0, $pos);
                    $sseBuf   = substr($sseBuf, $pos + 2);

                    foreach (explode("\n", trim($frame)) as $line) {
                        if (stripos($line, 'data:') !== 0) {
                            continue;
                        }
                        $data = trim(substr($line, 5));
                        if ($data === '' || $data === '[DONE]') {
                            continue;
                        }

                        $json  = json_decode($data, true);
                        $delta = $json['choices'][0]['delta']['content'] ?? '';
                        if ($delta === '') {
                            continue;
                        }

                        // **Immediately** emit whatever delta we got
                        echo $delta;
                        @ob_flush();
                        @flush();
                    }
                }

                return strlen($chunk);
            },
            CURLOPT_TIMEOUT       => 0,
        ]);

        curl_exec($ch);
        curl_close($ch);

        // any leftover in case the last frame had no trailing "\n\n"
        if ($sseBuf !== '') {
            // process leftover as a final frame
            if (preg_match_all('/^data:\s*(.+)$/m', $sseBuf, $matches)) {
                foreach ($matches[1] as $raw) {
                    if ($raw === '[DONE]') {
                        continue;
                    }
                    $json  = json_decode($raw, true);
                    $delta = $json['choices'][0]['delta']['content'] ?? '';
                    if ($delta !== '') {
                        echo $delta;
                        @ob_flush();
                        @flush();
                    }
                }
            }
        }
    }
}
