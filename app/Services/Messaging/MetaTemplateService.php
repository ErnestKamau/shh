<?php

namespace App\Services\Messaging;

use App\Models\Messaging\MessageTemplate;
use App\Models\Messaging\TenantWhatsAppAccount;
use Illuminate\Support\Facades\Log;

class MetaTemplateService
{
    protected MetaWhatsAppProvider $provider;

    public function __construct(MetaWhatsAppProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Synchronize templates with Meta Cloud API for a specific tenant.
     */
    public function syncTemplates(TenantWhatsAppAccount $account): array
    {
        $result = $this->provider->syncTemplates($account);
        
        if (!$result['success']) {
            return $result;
        }

        $metaTemplates = $result['templates'] ?? [];
        $syncedIds = [];

        foreach ($metaTemplates as $metaTemplate) {
            $name = $metaTemplate['name'];
            $category = $metaTemplate['category'];
            $language = $metaTemplate['language'];
            $status = $metaTemplate['status'];
            $metaId = $metaTemplate['id'];

            // Extract contents from components
            $components = $metaTemplate['components'] ?? [];
            $headerType = 'NONE';
            $headerContent = null;
            $bodyContent = '';
            $footerContent = null;
            $buttons = [];

            foreach ($components as $component) {
                $type = $component['type'];
                if ($type === 'HEADER') {
                    $headerType = $component['format'] ?? 'TEXT';
                    $headerContent = $component['text'] ?? null;
                } elseif ($type === 'BODY') {
                    $bodyContent = $component['text'] ?? '';
                } elseif ($type === 'FOOTER') {
                    $footerContent = $component['text'] ?? null;
                } elseif ($type === 'BUTTONS') {
                    $buttons = $component['buttons'] ?? [];
                }
            }

            // Extract variables lists e.g., body parameters
            $variables = $this->parseVariables($bodyContent);

            $localTemplate = MessageTemplate::updateOrCreate(
                [
                    'tenant_id' => $account->tenant_id,
                    'name' => $name,
                    'language' => $language,
                ],
                [
                    'category' => $category,
                    'header_type' => $headerType,
                    'header_content' => $headerContent,
                    'body_content' => $bodyContent,
                    'footer_content' => $footerContent,
                    'buttons_json' => $buttons,
                    'variables_json' => $variables,
                    'meta_template_id' => $metaId,
                    'status' => $status,
                ]
            );

            $syncedIds[] = $localTemplate->id;
        }

        return [
            'success' => true,
            'count' => count($syncedIds),
        ];
    }

    /**
     * Submit a template to Meta API.
     */
    public function submitTemplate(TenantWhatsAppAccount $account, MessageTemplate $template): array
    {
        $result = $this->provider->submitTemplate($account, $template);
        
        if ($result['success']) {
            $template->update([
                'meta_template_id' => $result['meta_template_id'],
                'status' => $result['status'] ?? 'PENDING'
            ]);
        }
        
        return $result;
    }

    /**
     * Parse variable index numbers (e.g. {{1}}, {{2}}) from template body.
     */
    public function parseVariables(string $body): array
    {
        preg_match_all('/\{\{(\d+)\}\}/', $body, $matches);
        if (empty($matches[1])) {
            return [];
        }

        $uniqueMatches = array_unique($matches[1]);
        sort($uniqueMatches);

        $variables = [];
        foreach ($uniqueMatches as $index) {
            $variables[] = [
                'index' => (int) $index,
                'placeholder' => "Variable {$index}",
                'description' => "Value to map to position {$index}"
            ];
        }

        return $variables;
    }
}
