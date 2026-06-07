<?php

namespace App\Services\Messaging;

use App\Models\Messaging\TenantWhatsAppAccount;
use App\Models\Messaging\MessageTemplate;
use App\Models\Messaging\WhatsAppActivityLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWhatsAppProvider implements MessagingProviderInterface
{
    protected string $version = 'v20.0';

    /**
     * Send template/transactional message via Meta Cloud API.
     */
    public function sendMessage(TenantWhatsAppAccount $account, string $recipient, array $payload): array
    {
        $url = "https://graph.facebook.com/{$this->version}/{$account->phone_number_id}/messages";
        
        // Log request
        $this->logActivity($account->tenant_id, 'request', [
            'url' => $url,
            'recipient' => $recipient,
            'payload' => $payload
        ]);

        try {
            $response = Http::withToken($account->access_token)
                ->timeout(15)
                ->post($url, $payload);

            $responseData = $response->json();

            // Log response
            $this->logActivity($account->tenant_id, $response->successful() ? 'response' : 'failure', [
                'status_code' => $response->status(),
                'response' => $responseData
            ]);

            if ($response->successful()) {
                $messageId = $responseData['messages'][0]['id'] ?? null;
                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'response' => $responseData
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['error']['message'] ?? 'Unknown API Error',
                'response' => $responseData
            ];
        } catch (\Exception $e) {
            Log::error("MetaWhatsAppProvider SendMessage Exception: " . $e->getMessage());
            
            $this->logActivity($account->tenant_id, 'failure', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync templates from Meta Business Manager.
     */
    public function syncTemplates(TenantWhatsAppAccount $account): array
    {
        $url = "https://graph.facebook.com/{$this->version}/{$account->waba_id}/message_templates";
        
        try {
            $response = Http::withToken($account->access_token)
                ->timeout(20)
                ->get($url, [
                    'limit' => 100
                ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'templates' => $responseData['data'] ?? []
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['error']['message'] ?? 'Sync templates failed'
            ];
        } catch (\Exception $e) {
            Log::error("MetaWhatsAppProvider SyncTemplates Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Submit template to Meta for approval.
     */
    public function submitTemplate(TenantWhatsAppAccount $account, MessageTemplate $template): array
    {
        $url = "https://graph.facebook.com/{$this->version}/{$account->waba_id}/message_templates";
        
        $components = [];

        // Header Component
        if ($template->header_type !== 'NONE') {
            $header = [
                'type' => 'HEADER',
                'format' => $template->header_type,
            ];
            if ($template->header_type === 'TEXT') {
                $header['text'] = $template->header_content;
                // Add default example variables if variable exists
                if (str_contains($template->header_content, '{{1}}')) {
                    $header['example'] = [
                        'header_text' => ['Example Value']
                    ];
                }
            } elseif (in_array($template->header_type, ['IMAGE', 'VIDEO', 'DOCUMENT'], true)) {
                // Media formats require a sample file handle or URL link in submission
                $header['example'] = [
                    'header_handle' => [
                        'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
                    ]
                ];
            }
            $components[] = $header;
        }

        // Body Component
        $body = [
            'type' => 'BODY',
            'text' => $template->body_content,
        ];
        // Parse and include examples for variables like {{1}}, {{2}}...
        preg_match_all('/\{\{(\d+)\}\}/', $template->body_content, $matches);
        if (!empty($matches[1])) {
            $examples = [];
            foreach ($matches[1] as $index) {
                $examples[] = "Value {$index}";
            }
            $body['example'] = [
                'body_text' => [$examples]
            ];
        }
        $components[] = $body;

        // Footer Component
        if ($template->footer_content) {
            $components[] = [
                'type' => 'FOOTER',
                'text' => $template->footer_content,
            ];
        }

        // Buttons Component
        if ($template->buttons_json && is_array($template->buttons_json)) {
            $buttons = [];
            foreach ($template->buttons_json as $btn) {
                $buttonItem = [
                    'type' => $btn['type'], // QUICK_REPLY, PHONE_NUMBER, URL
                    'text' => $btn['text'],
                ];
                if ($btn['type'] === 'PHONE_NUMBER') {
                    $buttonItem['phone_number'] = $btn['phone_number'];
                } elseif ($btn['type'] === 'URL') {
                    $buttonItem['url'] = $btn['url'];
                    if (!empty($btn['url_example'])) {
                        $buttonItem['example'] = [$btn['url_example']];
                    }
                }
                $buttons[] = $buttonItem;
            }
            if (!empty($buttons)) {
                $components[] = [
                    'type' => 'BUTTONS',
                    'buttons' => $buttons,
                ];
            }
        }

        $payload = [
            'name' => $template->name,
            'category' => $template->category,
            'language' => $template->language,
            'components' => $components,
        ];

        $this->logActivity($account->tenant_id, 'request', [
            'url' => $url,
            'action' => 'submit_template',
            'payload' => $payload
        ]);

        try {
            $response = Http::withToken($account->access_token)
                ->timeout(20)
                ->post($url, $payload);

            $responseData = $response->json();

            $this->logActivity($account->tenant_id, $response->successful() ? 'response' : 'failure', [
                'status_code' => $response->status(),
                'response' => $responseData
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'meta_template_id' => $responseData['id'] ?? null,
                    'status' => $responseData['status'] ?? 'PENDING'
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['error']['message'] ?? 'Submit template failed'
            ];
        } catch (\Exception $e) {
            Log::error("MetaWhatsAppProvider SubmitTemplate Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete template from Meta Business Manager.
     */
    public function deleteTemplate(TenantWhatsAppAccount $account, string $templateName): array
    {
        $url = "https://graph.facebook.com/{$this->version}/{$account->waba_id}/message_templates";
        
        try {
            $response = Http::withToken($account->access_token)
                ->timeout(20)
                ->delete($url, [
                    'name' => $templateName
                ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'response' => $responseData
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['error']['message'] ?? 'Delete template failed'
            ];
        } catch (\Exception $e) {
            Log::error("MetaWhatsAppProvider DeleteTemplate Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate WhatsApp Phone Number ID & credentials connection.
     */
    public function validateConnection(TenantWhatsAppAccount $account): array
    {
        $url = "https://graph.facebook.com/{$this->version}/{$account->phone_number_id}";
        
        try {
            $response = Http::withToken($account->access_token)
                ->timeout(15)
                ->get($url);

            $responseData = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'verified_name' => $responseData['verified_name'] ?? 'Unknown Business Name',
                    'display_phone_number' => $responseData['display_phone_number'] ?? $account->sender_phone_number,
                    'quality_rating' => $responseData['quality_rating'] ?? 'GREEN',
                    'status' => $responseData['status'] ?? 'APPROVED',
                    'code_verification_status' => $responseData['code_verification_status'] ?? 'VERIFIED'
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['error']['message'] ?? 'Connection validation failed'
            ];
        } catch (\Exception $e) {
            Log::error("MetaWhatsAppProvider validateConnection Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send direct free-text message.
     */
    public function sendDirectMessage(TenantWhatsAppAccount $account, string $recipient, string $body): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
            'type' => 'text',
            'text' => [
                'body' => $body
            ]
        ];

        return $this->sendMessage($account, $recipient, $payload);
    }

    /**
     * Upload a media file directly to Meta Cloud.
     */
    public function uploadMedia(TenantWhatsAppAccount $account, string $filePath, string $mimeType): array
    {
        $url = "https://graph.facebook.com/{$this->version}/{$account->phone_number_id}/media";

        try {
            $response = Http::withToken($account->access_token)
                ->attach('file', file_get_contents($filePath), basename($filePath), [
                    'Content-Type' => $mimeType
                ])
                ->post($url, [
                    'messaging_product' => 'whatsapp'
                ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'media_id' => $responseData['id'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['error']['message'] ?? 'Media upload failed'
            ];
        } catch (\Exception $e) {
            Log::error("MetaWhatsAppProvider uploadMedia Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Log details to database.
     */
    protected function logActivity(string $tenantId, string $type, array $payload): void
    {
        try {
            WhatsAppActivityLog::create([
                'tenant_id' => $tenantId,
                'activity_type' => $type,
                'payload' => $payload
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to write to WhatsAppActivityLog: " . $e->getMessage());
        }
    }
}
