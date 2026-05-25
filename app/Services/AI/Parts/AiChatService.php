<?php

namespace App\Services\AI\Parts;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Services\AI\AnalyticsCollectorService;

class AiChatService extends AiBaseService
{
    protected AnalyticsCollectorService $analytics;

    public function __construct(AnalyticsCollectorService $analytics)
    {
        parent::__construct();
        $this->analytics = $analytics;
    }

    /**
     * Stream chat response with SSE (Server-Sent Events).
     */
    public function streamChat(array $messages, array $options = [])
    {
        $traceId = $options['trace_id'] ?? Str::uuid()->toString();
        $startTime = microtime(true);
        $totalTokens = 0;
        $modelName = $options['model'] ?? 'unknown';
        $queryText = end($messages)['content'] ?? 'Unknown Query';
        $userId = $this->aiRuntimeUserId(auth()->id());
        $companyId = $this->aiRuntimeCompanyId(optional(auth()->user())->company_id);
        $sessionId = $options['session_id'] ?? null;
        
        try {
            $payload = [
                'messages' => $messages,
                'company_id' => $companyId,
                'user_id' => $userId,
                'trace_id' => $traceId,
                'use_visuals' => (bool) ($options['use_visuals'] ?? true),
                'model' => $options['model'] ?? null,
                'module_context' => $options['module_context'] ?? null,
                'mode' => $options['mode'] ?? 'general',
            ];

            $client = new GuzzleClient();
            $response = $client->post("{$this->apiBaseUrl}/v1/chat/stream", [
                'json' => $payload,
                'stream' => true,
                'timeout' => 180,
            ]);

            $body = $response->getBody();
            $resource = $body->detach();
            $buffer = '';
            
            if ($resource) {
                while (!feof($resource)) {
                    $line = fgets($resource);
                    if ($line === false) {
                        break;
                    }
                    $buffer .= $line;
                    
                    while (($pos = strpos($buffer, "\n\n")) !== false) {
                        $event = substr($buffer, 0, $pos);
                        $buffer = substr($buffer, $pos + 2);

                        if (strpos($event, "data: ") === 0) {
                            $jsonData = substr($event, 6);
                            if (trim($jsonData) !== '[DONE]') {
                                $decoded = json_decode($jsonData, true);
                                if (isset($decoded['usage']['total_tokens'])) {
                                    $totalTokens = $decoded['usage']['total_tokens'];
                                }
                                if (isset($decoded['model'])) {
                                    $modelName = $decoded['model'];
                                }
                                if (isset($decoded['route_name'])) {
                                    $options['route_name'] = $decoded['route_name'];
                                }
                                if (isset($decoded['confidence'])) {
                                    $options['confidence'] = $decoded['confidence'];
                                }
                                yield $jsonData;
                            }
                        }
                    }
                }
                fclose($resource);
            }

            // Log successful completion
            $latencyMs = (int)((microtime(true) - $startTime) * 1000);
            $this->analytics->recordModelPerformance(
                $modelName,
                $latencyMs,
                $totalTokens,
                true,
                null,
                $queryText,
                $userId,
                $companyId,
                $sessionId
            );

            // Log routing if intent was detected
            if (isset($options['route_name'])) {
                $this->analytics->recordRouting(
                    $options['route_name'],
                    $modelName,
                    $options['route_name'],
                    (float) ($options['confidence'] ?? 1.0),
                    $queryText,
                    $userId,
                    $companyId,
                    $sessionId
                );
            }
        } catch (\Throwable $e) {
            $this->log('error', 'Simplified stream chat failed', ['error' => $e->getMessage()]);
            
            $latencyMs = (int)((microtime(true) - $startTime) * 1000);
            $this->analytics->recordModelPerformance(
                $modelName,
                $latencyMs,
                0,
                false,
                $e->getMessage(),
                $queryText,
                $userId,
                $companyId,
                $sessionId
            );

            yield json_encode(['error' => 'AI Service unreachable']);
        }
    }

    /**
     * Simple chat retrieval (non-streaming).
     */
    public function chat(string $message, array $options = []): array
    {
        $userId = $this->aiRuntimeUserId(auth()->id());
        $companyId = $this->aiRuntimeCompanyId(optional(auth()->user())->company_id);
        $sessionId = $options['session_id'] ?? null;

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(180)
                ->connectTimeout(5)
                ->post("{$this->apiBaseUrl}/v1/chat", [
                    'messages' => [['role' => 'user', 'content' => $message]],
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'trace_id' => $options['trace_id'] ?? null,
                    'use_visuals' => (bool) ($options['use_visuals'] ?? true),
                    'mode' => $options['mode'] ?? 'general',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Log performance
                $this->analytics->recordModelPerformance(
                    $data['model'] ?? ($options['model'] ?? 'unknown'),
                    (int) ($response->header('X-Response-Time') ?? 0),
                    (int) ($data['usage']['total_tokens'] ?? 0),
                    true,
                    null,
                    $message,
                    $userId,
                    $companyId,
                    $sessionId
                );

                // Log routing if intent was detected
                if (isset($data['route_name'])) {
                    $this->analytics->recordRouting(
                        $data['route_name'],
                        $data['model'] ?? 'unknown',
                        $data['route_name'],
                        (float) ($data['confidence'] ?? 1.0),
                        $message,
                        $userId,
                        $companyId,
                        $sessionId
                    );
                }

                return $data;
            }

            $this->analytics->recordModelPerformance(
                $options['model'] ?? 'unknown',
                0,
                0,
                false,
                "API Error: " . $response->status(),
                $message,
                $userId,
                $companyId,
                $sessionId
            );

            return ['reply' => 'No response from AI service.', 'error' => true];
        } catch (\Exception $e) {
            $this->log('error', 'Chat failed', ['error' => $e->getMessage()]);
            
            $this->analytics->recordModelPerformance(
                $options['model'] ?? 'unknown',
                0,
                0,
                false,
                $e->getMessage(),
                $message,
                $userId,
                $companyId,
                $sessionId
            );

            return ['reply' => 'AI Service unreachable', 'error' => true];
        }
    }

    /**
     * Index knowledge content via the Python service.
     */
    public function indexKnowledge(array $data): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(120)
                ->post("{$this->apiBaseUrl}/v1/index", $data);

            if ($response->successful()) {
                return $response->json();
            }

            return ['status' => 'error', 'message' => 'Python indexing service failed: ' . $response->status()];
        } catch (\Exception $e) {
            $this->log('error', 'Indexing request failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'AI Service unreachable'];
        }
    }

    /**
     * Delete knowledge entry/entries via the Python service.
     */
    public function deleteKnowledge(string $entityType, $entityId, $companyId = 0): array
    {
        try {
            $companyId = $this->aiRuntimeCompanyId($companyId, 0);

            if (is_array($entityId)) {
                $response = \Illuminate\Support\Facades\Http::timeout(60)
                    ->post("{$this->apiBaseUrl}/v1/index/bulk-delete", [
                        'entity_type' => $entityType,
                        'entity_ids' => $entityId,
                        'company_id' => $companyId
                    ]);
            } else {
                $response = \Illuminate\Support\Facades\Http::timeout(30)
                    ->delete("{$this->apiBaseUrl}/v1/index/{$entityType}/{$entityId}?company_id={$companyId}");
            }

            if ($response->successful()) {
                return $response->json();
            }

            return ['status' => 'error', 'message' => 'Python deletion service failed: ' . $response->status()];
        } catch (\Exception $e) {
            $this->log('error', 'Knowledge deletion failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'AI Service unreachable'];
        }
    }

    /**
     * Test semantic search retrieval via the Python service.
     */
    public function searchKnowledge(string $query, array $options = []): array
    {
        try {
            $companyId = $this->aiRuntimeCompanyId($options['company_id'] ?? 1);

            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->post("{$this->apiBaseUrl}/v1/index/search", [
                    'query' => $query,
                    'company_id' => $companyId,
                    'collections' => $options['collections'] ?? null,
                    'limit' => $options['limit'] ?? 5,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['status' => 'error', 'message' => 'Python search service failed'];
        } catch (\Exception $e) {
            $this->log('error', 'Knowledge search failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'AI Service unreachable'];
        }
    }

    /**
     * Upload and index a file via the Python service.
     */
    public function uploadKnowledgeFile($file, array $data): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(300)
                ->attach(
                    'file', 
                    file_get_contents($file->getRealPath()), 
                    $file->getClientOriginalName()
                )
                ->post("{$this->apiBaseUrl}/v1/index/upload", [
                    'collection'   => $data['collection'],
                    'permission'   => $data['permission'] ?? 'general.view',
                    'manual_doc_id' => $data['manual_doc_id'] ?? null,
                    'metadata'     => json_encode($data['metadata'] ?? []),
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status' => 'error', 
                'message' => 'Python upload service failed: ' . ($response->json()['detail'] ?? $response->status())
            ];
        } catch (\Exception $e) {
            $this->log('error', 'Knowledge file upload failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'AI Service unreachable or request timed out'];
        }
    }

    /**
     * Cancel running database queries for a given trace ID.
     */
    public function cancel(string $traceId): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->post("{$this->apiBaseUrl}/v1/chat/cancel", [
                    'trace_id' => $traceId,
                ]);

            if ($response->successful()) {
                return $response->json();
            }
            return ['success' => false, 'error' => 'API Error: ' . $response->status()];
        } catch (\Exception $e) {
            $this->log('error', 'Cancel request failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'AI Service unreachable'];
        }
    }

    protected function aiRuntimeCompanyId($value, int $default = 1): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return $default;
    }

    protected function aiRuntimeUserId($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return null;
    }
}
