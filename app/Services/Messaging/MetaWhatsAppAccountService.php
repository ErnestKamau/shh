<?php

namespace App\Services\Messaging;

use App\Models\Messaging\TenantWhatsAppAccount;

class MetaWhatsAppAccountService
{
    /**
     * Get tenant WhatsApp account configuration.
     */
    public function getAccountForTenant(string $tenantId): ?TenantWhatsAppAccount
    {
        return TenantWhatsAppAccount::where('tenant_id', $tenantId)->first();
    }

    /**
     * Store or update tenant WhatsApp account credentials.
     */
    public function createOrUpdateAccount(string $tenantId, array $data): TenantWhatsAppAccount
    {
        return TenantWhatsAppAccount::updateOrCreate(
            ['tenant_id' => $tenantId],
            array_merge($data, ['active' => true])
        );
    }
}
