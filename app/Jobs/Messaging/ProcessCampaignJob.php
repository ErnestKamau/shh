<?php

namespace App\Jobs\Messaging;

use App\Models\Messaging\MessageCampaign;
use App\Models\Messaging\OutboundMessage;
use App\Services\Messaging\AudienceBuilderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Exception;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes timeout for larger audiences

    public function __construct(
        public int $campaignId
    ) {
    }

    public function handle(AudienceBuilderService $audienceService): void
    {
        $campaign = MessageCampaign::find($this->campaignId);

        if (!$campaign || in_array($campaign->status, ['COMPLETED', 'RUNNING'], true)) {
            return;
        }

        $campaign->update([
            'status' => 'RUNNING',
            'started_at' => now(),
        ]);

        try {
            $audience = $campaign->audience;
            $template = $campaign->template;

            if (!$audience || !$template) {
                throw new Exception("Audience or Template is missing from Campaign configuration.");
            }

            if ($template->status !== 'APPROVED') {
                throw new Exception("Campaign template [{$template->name}] is not approved (Status: {$template->status}).");
            }

            $query = $audienceService->getAudienceContactsQuery($audience);

            // Chunk contacts to keep memory usage low and scale to thousands of recipients
            $query->chunkById(250, function ($contacts) use ($campaign, $template) {
                foreach ($contacts as $contact) {
                    $recipient = $contact->mobile ?: $contact->telephone;
                    if (empty($recipient)) {
                        continue;
                    }

                    $firstName = $contact->first_name ?? '';
                    $lastName = $contact->last_name ?? '';
                    $fullName = trim($firstName . ' ' . $lastName);

                    $variables = [
                        'customer_name' => $fullName ?: 'Valued Customer',
                        'company_name' => $contact->customer_name ?? 'LIMS Laboratory',
                        'date' => now()->toDateString(),
                    ];

                    $bodyParameters = [];
                    $variablesConfig = $template->variables_json ?? [];

                    if (!empty($variablesConfig)) {
                        usort($variablesConfig, function ($a, $b) {
                            return $a['index'] <=> $b['index'];
                        });

                        foreach ($variablesConfig as $var) {
                            $placeholder = $var['placeholder'] ?? '';
                            $val = $variables[$placeholder] ?? ($variables[$var['index']] ?? '');
                            $bodyParameters[] = [
                                'type' => 'text',
                                'text' => (string) $val
                            ];
                        }
                    } else {
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

                    // Create message record
                    $message = OutboundMessage::create([
                        'tenant_id' => $campaign->tenant_id,
                        'event_code' => 'CAMPAIGN_BROADCAST',
                        'recipient' => $recipient,
                        'provider_template_id' => $template->meta_template_id ?? $template->name,
                        'template_id' => $template->id,
                        'campaign_id' => $campaign->id,
                        'payload_json' => $payload,
                        'status' => 'queued',
                        'attempts' => 0,
                        'idempotency_key' => 'campaign-' . $campaign->id . '-' . Str::uuid(),
                    ]);

                    // Dispatch individual send job
                    SendWhatsAppMessageJob::dispatch($campaign->tenant_id, $message->id);
                }
            }, 'crm_customer_contacts.id');

            $campaign->update([
                'status' => 'COMPLETED',
                'completed_at' => now(),
            ]);

        } catch (Exception $e) {
            $campaign->update([
                'status' => 'FAILED',
            ]);
            throw $e;
        }
    }
}
