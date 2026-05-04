<?php

namespace App\Services\Messaging;

use App\Jobs\Messaging\SendAfricasTalkingWhatsappMessageJob;
use App\Models\Messaging\OutboundMessage;
use App\Models\Messaging\TenantMessageMapping;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class AfricasTalkingWhatsappService
{
    public function sendTemplateMessage(string $tenantId, string $eventCode, string $recipient, array $variables): OutboundMessage
    {
        $eventDefinition = config("messaging.events.{$eventCode}");

        if (!$eventDefinition) {
            throw new RuntimeException("Unsupported messaging event [{$eventCode}].");
        }

        $mapping = TenantMessageMapping::query()
            ->where('tenant_id', $tenantId)
            ->where('event_code', $eventCode)
            ->where('active', true)
            ->first();

        if (!$mapping) {
            throw new RuntimeException("No active tenant message mapping found for tenant [{$tenantId}] and event [{$eventCode}].");
        }

        $payload = $this->buildPayload($mapping, $eventDefinition, $recipient, $variables);

        $message = OutboundMessage::query()->create([
            'tenant_id' => $tenantId,
            'event_code' => $eventCode,
            'recipient' => $recipient,
            'provider_template_id' => $mapping->provider_template_id,
            'payload_json' => $payload,
            'status' => 'queued',
            'attempts' => 0,
            'idempotency_key' => $variables['idempotency_key'] ?? (string) Str::uuid(),
        ]);

        SendAfricasTalkingWhatsappMessageJob::dispatch($tenantId, $message->id);

        return $message;
    }

    public function deliverQueuedMessage(string $tenantId, int $outboundMessageId): array
    {
        $message = OutboundMessage::query()
            ->where('id', $outboundMessageId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$message) {
            throw (new ModelNotFoundException())->setModel(OutboundMessage::class, [$outboundMessageId]);
        }

        if (in_array($message->status, ['SENT', 'DELIVERED', 'READ'], true)) {
            return $message->provider_response_json ?? [];
        }

        $attempts = $message->attempts + 1;
        $endpoint = rtrim((string) config('services.africastalking.base_url'), '/') . '/whatsapp/message/send';

        try {
            /** @var Response $response */
            $response = Http::acceptJson()
                ->contentType('application/json')
                ->withHeaders([
                    'apikey' => (string) config('services.africastalking.api_key'),
                    'Idempotency-Key' => $message->idempotency_key,
                ])
                ->timeout(30)
                ->post($endpoint, $message->payload_json);
        } catch (\Throwable $exception) {
            $this->markFailure($message, $attempts, $exception->getMessage());
            throw $exception;
        }

        $responseBody = $response->json();

        if (!$response->successful()) {
            $error = sprintf('Africa\'s Talking request failed with status %s.', $response->status());
            $this->markFailure($message, $attempts, $error, $responseBody ?: ['raw' => $response->body()]);
            throw new RuntimeException($error);
        }

        $status = $responseBody['status'] ?? null;
        $providerMessageId = $responseBody['messageId'] ?? null;

        if (!$providerMessageId || $status === 'FAILED') {
            $error = $responseBody['errorMessage'] ?? 'Africa\'s Talking did not return a successful WhatsApp message response.';
            $this->markFailure($message, $attempts, $error, $responseBody ?: []);
            throw new RuntimeException($error);
        }

        $message->update([
            'status' => $status,
            'attempts' => $attempts,
            'provider_message_id' => $providerMessageId,
            'provider_response_json' => $responseBody,
            'error' => null,
        ]);

        Log::info('Africa\'s Talking WhatsApp message queued delivery completed', [
            'outbound_message_id' => $message->id,
            'tenant_id' => $tenantId,
            'provider_message_id' => $providerMessageId,
            'status' => $status,
        ]);

        return $responseBody;
    }

    protected function buildPayload(TenantMessageMapping $mapping, array $eventDefinition, string $recipient, array $variables): array
    {
        $bodyValues = [];

        foreach ($eventDefinition['provider_payload']['body_values'] ?? [] as $variableKey) {
            if (!array_key_exists($variableKey, $variables)) {
                throw new RuntimeException("Missing required variable [{$variableKey}] for event [{$mapping->event_code}].");
            }

            $bodyValues[] = (string) $variables[$variableKey];
        }

        $body = [
            'templateId' => $mapping->provider_template_id,
            'bodyValues' => $bodyValues,
        ];

        $headerValue = $variables['header_value'] ?? ($eventDefinition['provider_payload']['header_value'] ?? null);

        if ($headerValue !== null && $headerValue !== '') {
            $body['headerValue'] = (string) $headerValue;
        }

        $waNumber = $mapping->wa_number ?: config('services.africastalking.whatsapp_from');

        if (!$waNumber) {
            throw new RuntimeException('AFRICASTALKING_WHATSAPP_FROM is not configured and no tenant-specific wa_number was found.');
        }

        if (!config('services.africastalking.username') || !config('services.africastalking.api_key')) {
            throw new RuntimeException('Africa\'s Talking credentials are not configured.');
        }

        return [
            'username' => (string) config('services.africastalking.username'),
            'waNumber' => $waNumber,
            'phoneNumber' => $recipient,
            'body' => $body,
        ];
    }

    protected function markFailure(OutboundMessage $message, int $attempts, string $error, array $responseBody = []): void
    {
        $message->update([
            'status' => 'FAILED',
            'attempts' => $attempts,
            'provider_response_json' => $responseBody,
            'error' => $error,
        ]);

        Log::error('Africa\'s Talking WhatsApp message delivery failed', [
            'outbound_message_id' => $message->id,
            'tenant_id' => $message->tenant_id,
            'error' => $error,
        ]);
    }
}