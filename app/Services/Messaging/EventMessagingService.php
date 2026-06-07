<?php

namespace App\Services\Messaging;

use App\Models\Messaging\OutboundMessage;
use App\Models\Messaging\EventMessageMapping;
use App\Models\Messaging\TenantWhatsAppAccount;
use App\Jobs\Messaging\SendWhatsAppMessageJob;
use Illuminate\Support\Str;
use RuntimeException;

class EventMessagingService
{
    /**
     * Trigger WhatsApp notification based on a LIMS event.
     */
    public function sendEventMessage(string $tenantId, string $eventCode, string $recipient, array $variables): OutboundMessage
    {
        // 1. Get active event mapping
        $mapping = EventMessageMapping::where('tenant_id', $tenantId)
            ->where('event_code', $eventCode)
            ->where('active', true)
            ->first();

        if (!$mapping) {
            throw new RuntimeException("No active template mapping found for tenant [{$tenantId}] and event [{$eventCode}].");
        }

        $template = $mapping->template;
        if (!$template || $template->status !== 'APPROVED') {
            throw new RuntimeException("Mapped template for event [{$eventCode}] is not approved or not found.");
        }

        // 2. Build Meta-specific payload components
        $bodyParameters = [];
        $variablesConfig = $template->variables_json ?? [];

        if (!empty($variablesConfig)) {
            // Sort variables by index/sequence to align with Meta API
            usort($variablesConfig, function ($a, $b) {
                return $a['index'] <=> $b['index'];
            });

            foreach ($variablesConfig as $var) {
                $placeholder = $var['placeholder'] ?? '';
                // Check if placeholder name is key in $variables
                $val = $variables[$placeholder] ?? ($variables[$var['index']] ?? '');
                $bodyParameters[] = [
                    'type' => 'text',
                    'text' => (string) $val
                ];
            }
        } else {
            // Fallback: search for variables in body content and map sequentially from $variables values
            preg_match_all('/\{\{(\d+)\}\}/', $template->body_content, $matches);
            if (!empty($matches[1])) {
                $uniqueIndices = array_unique($matches[1]);
                sort($uniqueIndices);
                foreach ($uniqueIndices as $idx) {
                    $val = $variables[$idx] ?? array_values($variables)[$idx - 1] ?? '';
                    $bodyParameters[] = [
                        'type' => 'text',
                        'text' => (string) $val
                    ];
                }
            }
        }

        $components = [];

        // Build header parameters for media templates (IMAGE, DOCUMENT, VIDEO)
        if (in_array($template->header_type, ['IMAGE', 'DOCUMENT', 'VIDEO'], true)) {
            $mediaUrl = $variables['media_url'] ?? null;
            if ($mediaUrl) {
                $mediaType = strtolower($template->header_type); // image, document, video
                $mediaObj = ['link' => $mediaUrl];
                if ($template->header_type === 'DOCUMENT') {
                    $mediaObj['filename'] = $variables['media_filename'] ?? 'report.pdf';
                }
                
                $components[] = [
                    'type' => 'header',
                    'parameters' => [
                        [
                            'type' => $mediaType,
                            $mediaType => $mediaObj
                        ]
                    ]
                ];
            }
        }

        if (!empty($bodyParameters)) {
            $components[] = [
                'type' => 'body',
                'parameters' => $bodyParameters
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
            'type' => 'template',
            'template' => [
                'name' => $template->name,
                'language' => [
                    'code' => $template->language
                ]
            ]
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        // 3. Persist OutboundMessage
        $message = OutboundMessage::create([
            'tenant_id' => $tenantId,
            'event_code' => $eventCode,
            'recipient' => $recipient,
            'provider_template_id' => $template->meta_template_id ?? $template->name,
            'template_id' => $template->id,
            'payload_json' => $payload,
            'status' => 'queued',
            'attempts' => 0,
            'idempotency_key' => $variables['idempotency_key'] ?? (string) Str::uuid(),
        ]);

        // 4. Dispatch Job
        SendWhatsAppMessageJob::dispatch($tenantId, $message->id);

        return $message;
    }
}
