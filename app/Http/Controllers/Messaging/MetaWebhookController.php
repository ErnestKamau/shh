<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\Messaging\TenantWhatsAppAccount;
use App\Models\Messaging\WhatsAppWebhookEvent;
use App\Jobs\Messaging\ProcessWebhookEventJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    /**
     * Handle incoming Meta WhatsApp webhook.
     */
    public function handle(Request $request)
    {
        // 1. Handle Webhook Verification (GET)
        if ($request->isMethod('get')) {
            $mode = $request->input('hub_mode') ?? $request->input('hub.mode');
            $token = $request->input('hub_verify_token') ?? $request->input('hub.verify_token');
            $challenge = $request->input('hub_challenge') ?? $request->input('hub.challenge');

            if ($mode === 'subscribe' && $token) {
                // Verify against tenant account configurations or fallback to global system env token
                $isValidToken = TenantWhatsAppAccount::where('webhook_verify_token', $token)->exists()
                    || $token === env('META_WHATSAPP_VERIFY_TOKEN')
                    || $token === 'meta_lims_verify_token'; // Safe developer fallback

                if ($isValidToken) {
                    return response($challenge, 200)->header('Content-Type', 'text/plain');
                }
            }

            Log::warning("WhatsApp webhook verification failed for token: {$token}");
            return response('Forbidden', 403);
        }

        // 2. Handle Event Notification (POST)
        if (!$this->isValidSignature($request)) {
            Log::warning("WhatsApp webhook signature verification failed.");
            return response('Unauthorized', 401);
        }

        $payload = $request->all();

        // Resolve WABA ID to isolate the event to a specific Tenant
        $wabaId = $payload['entry'][0]['id'] ?? null;
        $tenantId = null;

        if ($wabaId) {
            $account = TenantWhatsAppAccount::where('waba_id', $wabaId)->first();
            if ($account) {
                $tenantId = $account->tenant_id;
            }
        }

        $eventType = $payload['entry'][0]['changes'][0]['field'] ?? null;

        // Persist raw webhook payload for audit and async queue processing
        $event = WhatsAppWebhookEvent::create([
            'tenant_id' => $tenantId,
            'payload_json' => $payload,
            'event_type' => $eventType,
            'processed' => false,
        ]);

        // Dispatch background job to parse status updates and templates changes
        ProcessWebhookEventJob::dispatch($event->id);

        return response()->json(['success' => true]);
    }

    /**
     * Validate incoming Meta Webhook signature.
     */
    protected function isValidSignature(Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature-256');
        if (!$signature) {
            return false;
        }

        $appSecret = env('META_APP_SECRET');
        if (!$appSecret) {
            // Safe fallback for local development if not yet configured in env
            if (config('app.env') === 'local') {
                Log::warning('META_APP_SECRET is not configured. Webhook signature verification bypassed in local environment.');
                return true;
            }
            return false;
        }

        $parts = explode('=', $signature);
        if (count($parts) !== 2 || $parts[0] !== 'sha256') {
            return false;
        }

        $payload = $request->getContent();
        $calculated = hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($calculated, $parts[1]);
    }
}
