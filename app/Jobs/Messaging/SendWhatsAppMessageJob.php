<?php

namespace App\Jobs\Messaging;

use App\Models\Messaging\OutboundMessage;
use App\Models\Messaging\TenantWhatsAppAccount;
use App\Services\Messaging\MetaWhatsAppProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\RateLimiter;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $tenantId,
        public int $outboundMessageId,
    ) {
    }

    public function handle(MetaWhatsAppProvider $provider): void
    {
        $message = OutboundMessage::where('id', $this->outboundMessageId)
            ->where('tenant_id', $this->tenantId)
            ->first();

        if (!$message) {
            return;
        }

        if (in_array($message->status, ['SENT', 'DELIVERED', 'READ'], true)) {
            return;
        }

        $account = TenantWhatsAppAccount::where('tenant_id', $this->tenantId)
            ->where('active', true)
            ->first();

        if (!$account) {
            $message->update([
                'status' => 'FAILED',
                'error' => 'No active WhatsApp account configurations found for this tenant.',
                'failed_at' => now(),
            ]);
            return;
        }

        // Apply rate limit of 80 messages per 60 seconds per phone number
        $limiterKey = 'whatsapp-send:' . $account->phone_number_id;
        if (RateLimiter::tooManyAttempts($limiterKey, 80)) {
            $this->release(10);
            return;
        }

        RateLimiter::hit($limiterKey, 60);

        $attempts = $message->attempts + 1;
        $message->update([
            'attempts' => $attempts,
            'queued_at' => $message->queued_at ?? now()
        ]);

        $result = $provider->sendMessage($account, $message->recipient, $message->payload_json);

        if ($result['success']) {
            $message->update([
                'status' => 'SENT',
                'provider_message_id' => $result['message_id'],
                'provider_response_json' => $result['response'],
                'sent_at' => now(),
                'error' => null,
            ]);

            try {
                $conversation = \App\Models\Messaging\WhatsAppConversation::getOrCreate($this->tenantId, $message->recipient);
                
                $bodyText = '';
                $payload = $message->payload_json;
                if (isset($payload['template']['name'])) {
                    $bodyText = "Template: " . $payload['template']['name'];
                } else {
                    $bodyText = $payload['text']['body'] ?? 'Template Message';
                }

                \App\Models\Messaging\WhatsAppMessage::updateOrCreate(
                    ['wamid' => $result['message_id']],
                    [
                        'tenant_id' => $this->tenantId,
                        'conversation_id' => $conversation->id,
                        'direction' => 'outbound',
                        'message_type' => isset($payload['template']['name']) ? 'template' : 'text',
                        'message_body' => $bodyText,
                        'timestamp' => now(),
                    ]
                );

                $conversation->update(['last_message_at' => now()]);
            } catch (\Exception $e) {
                Log::warning("Failed to record outbound conversation log: " . $e->getMessage());
            }
        } else {
            $status = $attempts >= $this->tries ? 'FAILED' : 'queued';
            $message->update([
                'status' => $status,
                'provider_response_json' => $result['response'] ?? null,
                'error' => $result['error'] ?? 'API Request Failed',
                'failed_at' => $status === 'FAILED' ? now() : null,
            ]);

            if ($status === 'queued') {
                $this->release(30);
            }
        }
    }
}
