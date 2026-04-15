<?php

namespace App\Services\AI\Parts;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiChatService extends AiBaseService
{
    /**
     * Stream chat response with SSE (Server-Sent Events).
     */
    public function streamChat(array $messages, array $sources = [], array $options = [])
    {
        $sessionId = $options['session_id'] ?? Str::uuid()->toString();
        $traceId = $options['trace_id'] ?? Str::uuid()->toString();
        $model = $options['model'] ?? config('imara_ai.defaults.default_model', 'qwen2.5:3b');
        $temperature = (float)($options['temperature'] ?? 0.3);

        $this->log('info', 'chat_stream_request', [
            'trace_id' => $traceId,
            'session_id' => $sessionId,
            'model' => $model,
            'message_count' => count($messages),
        ]);

        $startTime = microtime(true);

        try {
            $payload = [
                'messages' => $messages,
                'sources' => $sources,
                'model' => $model,
                'session_id' => $sessionId,
                'trace_id' => $traceId,
                'company_id' => optional(auth()->user())->company_id ?? 1,
                'generation_options' => [
                    'stream' => true,
                    'temperature' => $temperature,
                ],
            ];

            $guzzleClient = new GuzzleClient();
            $response = $guzzleClient->request('POST', "{$this->apiBaseUrl}/v1/chat/stream", [
                'stream' => true,
                'connect_timeout' => 5,
                // Streaming responses can legitimately exceed 60s end-to-end.
                'timeout' => 0,
                'json' => $payload,
            ]);

            $body = $response->getBody();
            $buffer = '';

            while (!$body->eof()) {
                $chunk = $body->read(1024);
                $buffer .= $chunk;

                while (($pos = strpos($buffer, "\n\n")) !== false) {
                    $event = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 2);

                    foreach (explode("\n", $event) as $line) {
                        if (strpos($line, 'data: ') === 0) {
                            yield substr($line, 6);
                        }
                    }
                }
            }

            $this->log('info', 'chat_stream_completed', [
                'trace_id' => $traceId,
                'latency_ms' => (int)((microtime(true) - $startTime) * 1000),
            ]);

        } catch (\Throwable $e) {
            $this->log('error', 'Stream chat failed', [
                'trace_id' => $traceId,
                'error' => $e->getMessage()
            ]);
            yield json_encode(['error' => 'AI Service unreachable: ' . $e->getMessage()]);
        }
    }

    /**
     * Simple chat retrieval (non-streaming).
     */
    public function chat(string $message, array $options = []): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(180)
                ->connectTimeout(5)
                ->post("{$this->apiBaseUrl}/v1/chat", [
                    'messages' => [['role' => 'user', 'content' => $message]],
                    'sources' => $options['sources'] ?? [],
                    'company_id' => auth()->user()->company_id ?? 1,
                    'trace_id' => $options['trace_id'] ?? null,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['reply' => 'No response from AI service.', 'error' => true];
        } catch (\Exception $e) {
            $this->log('error', 'Chat failed', ['error' => $e->getMessage()]);
            return ['reply' => 'AI Service unreachable', 'error' => true];
        }
    }
}
