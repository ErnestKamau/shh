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
    public function streamChat(array $messages, array $options = [])
    {
        $traceId = $options['trace_id'] ?? Str::uuid()->toString();
        
        try {
            $payload = [
                'messages' => $messages,
                'company_id' => optional(auth()->user())->company_id ?? 1,
                'trace_id' => $traceId,
                'use_visuals' => (bool) ($options['use_visuals'] ?? true),
                'model' => $options['model'] ?? null,
            ];

            $client = new GuzzleClient();
            $response = $client->post("{$this->apiBaseUrl}/v1/chat/stream", [
                'json' => $payload,
                'stream' => true,
                'timeout' => 180,
            ]);

            $body = $response->getBody();
            $buffer = '';
            
            while (!$body->eof()) {
                $chunk = $body->read(1024);
                $buffer .= $chunk;
                
                while (($pos = strpos($buffer, "\n\n")) !== false) {
                    $event = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 2);
                    
                    if (strpos($event, "data: ") === 0) {
                        $jsonData = substr($event, 6);
                        if (trim($jsonData) !== '[DONE]') {
                            yield $jsonData;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->log('error', 'Simplified stream chat failed', ['error' => $e->getMessage()]);
            yield json_encode(['error' => 'AI Service unreachable']);
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
                    'company_id' => auth()->user()->company_id ?? 1,
                    'trace_id' => $options['trace_id'] ?? null,
                    'use_visuals' => (bool) ($options['use_visuals'] ?? true),
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
    public function deleteKnowledge(string $entityType, $entityId, int $companyId = 0): array
    {
        try {
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
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->post("{$this->apiBaseUrl}/v1/index/search", [
                    'query' => $query,
                    'company_id' => $options['company_id'] ?? 1,
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
                    'permission'   => $data['permission'] ?? 'General.View',
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
}
