<?php

namespace App\Services\Messaging;

use App\Models\Messaging\TenantWhatsAppAccount;
use App\Models\Messaging\MessageTemplate;

interface MessagingProviderInterface
{
    public function sendMessage(TenantWhatsAppAccount $account, string $recipient, array $payload): array;

    public function syncTemplates(TenantWhatsAppAccount $account): array;

    public function submitTemplate(TenantWhatsAppAccount $account, MessageTemplate $template): array;

    public function deleteTemplate(TenantWhatsAppAccount $account, string $templateName): array;

    public function sendDirectMessage(TenantWhatsAppAccount $account, string $recipient, string $body): array;

    public function uploadMedia(TenantWhatsAppAccount $account, string $filePath, string $mimeType): array;
}
