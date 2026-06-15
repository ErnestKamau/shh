<?php

namespace App\Jobs\Messaging;

use App\Models\Messaging\TenantWhatsAppAccount;
use App\Services\Messaging\MetaTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncTemplateStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle(MetaTemplateService $templateService): void
    {
        $accounts = TenantWhatsAppAccount::where('active', true)
            ->where('status', 'ACTIVE')
            ->get();

        Log::info("Starting SyncTemplateStatusJob for " . $accounts->count() . " active accounts.");

        foreach ($accounts as $account) {
            try {
                $result = $templateService->syncTemplates($account);
                if ($result['success']) {
                    Log::info("Successfully synced WhatsApp templates for tenant: {$account->tenant_id}. Synced count: {$result['count']}");
                } else {
                    Log::error("Failed syncing templates for tenant {$account->tenant_id}: " . ($result['error'] ?? 'Unknown Error'));
                }
            } catch (\Exception $e) {
                Log::error("SyncTemplateStatusJob Exception for tenant {$account->tenant_id}: " . $e->getMessage());
            }
        }
    }
}
