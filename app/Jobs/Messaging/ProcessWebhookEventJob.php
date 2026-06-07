<?php

namespace App\Jobs\Messaging;

use App\Models\Messaging\OutboundMessage;
use App\Models\Messaging\MessageTemplate;
use App\Models\Messaging\WhatsAppWebhookEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebhookEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $eventId
    ) {
    }

    public function handle(): void
    {
        $event = WhatsAppWebhookEvent::find($this->eventId);

        if (!$event || $event->processed) {
            return;
        }

        try {
            $payload = $event->payload_json ?? [];

            // 1. Process Message Status Updates (statuses)
            if (isset($payload['entry'])) {
                foreach ($payload['entry'] as $entry) {
                    foreach ($entry['changes'] ?? [] as $change) {
                        $field = $change['field'] ?? '';
                        $value = $change['value'] ?? [];

                        if ($field === 'messages') {
                            $statuses = $value['statuses'] ?? [];
                            foreach ($statuses as $statusItem) {
                                $wamId = $statusItem['id'] ?? null;
                                $status = strtoupper($statusItem['status'] ?? ''); // SENT, DELIVERED, READ, FAILED
                                $timestamp = isset($statusItem['timestamp']) ? date('Y-m-d H:i:s', $statusItem['timestamp']) : now();

                                if ($wamId) {
                                    $message = OutboundMessage::where('provider_message_id', $wamId)->first();
                                    if ($message) {
                                        $updateData = [
                                            'status' => $status,
                                        ];

                                        if ($status === 'SENT') {
                                            $updateData['sent_at'] = $timestamp;
                                        } elseif ($status === 'DELIVERED') {
                                            $updateData['delivered_at'] = $timestamp;
                                        } elseif ($status === 'READ') {
                                            $updateData['read_at'] = $timestamp;
                                        } elseif ($status === 'FAILED') {
                                            $updateData['failed_at'] = $timestamp;
                                            $errors = $statusItem['errors'] ?? [];
                                            $updateData['error'] = !empty($errors) ? ($errors[0]['message'] ?? 'Webhook reported failure') : 'Webhook reported failure';
                                        }

                                        $message->update($updateData);
                                    }
                                }
                            }
                            
                            // Inbound customer replies
                            $inboundMessages = $value['messages'] ?? [];
                            foreach ($inboundMessages as $msgItem) {
                                $from = $msgItem['from'] ?? null;
                                $wamid = $msgItem['id'] ?? null;
                                $type = $msgItem['type'] ?? 'text';
                                $unixTime = $msgItem['timestamp'] ?? time();
                                $timestamp = date('Y-m-d H:i:s', $unixTime);
                                $body = '';

                                if ($type === 'text') {
                                    $body = $msgItem['text']['body'] ?? '';
                                } elseif ($type === 'button') {
                                    $body = $msgItem['button']['text'] ?? '';
                                } elseif ($type === 'interactive') {
                                    $replyType = $msgItem['interactive']['type'] ?? '';
                                    if ($replyType === 'button_reply') {
                                        $body = $msgItem['interactive']['button_reply']['title'] ?? '';
                                    } elseif ($replyType === 'list_reply') {
                                        $body = $msgItem['interactive']['list_reply']['title'] ?? '';
                                    }
                                } elseif ($type === 'image') {
                                    $body = '[Image received]';
                                } elseif ($type === 'document') {
                                    $body = '[Document received]';
                                } else {
                                    $body = '[' . ucfirst($type) . ' message received]';
                                }

                                if ($from && $event->tenant_id) {
                                    $conversation = \App\Models\Messaging\WhatsAppConversation::getOrCreate($event->tenant_id, $from);

                                    \App\Models\Messaging\WhatsAppMessage::updateOrCreate(
                                        ['wamid' => $wamid],
                                        [
                                            'tenant_id' => $event->tenant_id,
                                            'conversation_id' => $conversation->id,
                                            'direction' => 'inbound',
                                            'message_type' => $type,
                                            'message_body' => $body,
                                            'timestamp' => $timestamp,
                                        ]
                                    );

                                    $conversation->update(['last_message_at' => $timestamp]);
                                }
                            }
                        }

                        // 2. Process Template Approval Updates (message_template_status_update)
                        if ($field === 'message_template_status_update') {
                            $templateName = $value['message_template_name'] ?? null;
                            $templateLanguage = $value['message_template_language'] ?? null;
                            $metaStatus = $value['event'] ?? null; // APPROVED, REJECTED, DISABLED
                            $rejectionReason = $value['rejection_reason'] ?? null;

                            if ($templateName) {
                                $query = MessageTemplate::where('name', $templateName);
                                if ($templateLanguage) {
                                    $query->where('language', $templateLanguage);
                                }
                                $templates = $query->get();
                                foreach ($templates as $tmpl) {
                                    $tmpl->update([
                                        'status' => $metaStatus,
                                        'rejection_reason' => $rejectionReason
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            $event->update(['processed' => true]);

        } catch (\Exception $e) {
            Log::error("ProcessWebhookEventJob Error (Event ID {$this->eventId}): " . $e->getMessage());
            throw $e;
        }
    }
}
