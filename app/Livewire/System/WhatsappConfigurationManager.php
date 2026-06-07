<?php

namespace App\Livewire\System;

use App\Models\Messaging\TenantWhatsAppAccount;
use App\Models\Messaging\MessageTemplate;
use App\Models\Messaging\EventMessageMapping;
use App\Models\Messaging\MessageAudience;
use App\Models\Messaging\MessageCampaign;
use App\Models\Messaging\WhatsAppActivityLog;
use App\Models\Messaging\WhatsAppWebhookEvent;
use App\Models\CRM\CustomerContact;
use App\Services\Messaging\MetaWhatsAppAccountService;
use App\Services\Messaging\MetaTemplateService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class WhatsappConfigurationManager extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Navigation Tabs: account, templates, mappings, audiences, campaigns, logs
    public string $currentTab = 'account';

    // Account Form Fields
    public string $phone_number_id = '';
    public string $waba_id = '';
    public string $access_token = '';
    public string $webhook_verify_token = '';
    public string $sender_phone_number = '';
    public bool $isAccountConfigured = false;

    // Create Template Fields
    public string $tpl_name = '';
    public string $tpl_category = 'UTILITY';
    public string $tpl_language = 'en';
    public string $tpl_header_type = 'NONE';
    public string $tpl_header_content = '';
    public string $tpl_body_content = '';
    public string $tpl_footer_content = '';
    public array $tpl_buttons = []; // Quick reply format

    // Mapping Form Fields
    public string $map_event_code = '';
    public string $map_template_id = '';
    public bool $map_active = true;

    // Audience Form Fields
    public string $aud_name = '';
    public string $aud_country_id = '';
    public ?bool $aud_is_internal = null;

    // Campaign Form Fields
    public string $camp_name = '';
    public string $camp_audience_id = '';
    public string $camp_template_id = '';

    // Success/Error Feedback Messages
    public string $successMessage = '';
    public string $errorMessage = '';

    // Conversations/Chats Tab Fields
    public ?int $activeConversationId = null;
    public string $replyBody = '';

    public function mount(MetaWhatsAppAccountService $accountService): void
    {
        $tenantId = getUserCompany();
        $account = $accountService->getAccountForTenant($tenantId);

        if ($account) {
            $this->phone_number_id = $account->phone_number_id;
            $this->waba_id = $account->waba_id;
            $this->access_token = $account->access_token;
            $this->webhook_verify_token = $account->webhook_verify_token;
            $this->sender_phone_number = $account->sender_phone_number;
            $this->isAccountConfigured = true;
        } else {
            // Generate a secure default verify token
            $this->webhook_verify_token = 'meta_lims_' . bin2hex(random_bytes(8));
        }
    }

    public function setTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetFeedback();
    }

    private function resetFeedback(): void
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    /**
     * Save/Update WhatsApp Credentials
     */
    public function saveAccount(MetaWhatsAppAccountService $accountService): void
    {
        $this->resetFeedback();
        $this->validate([
            'phone_number_id' => 'required|string',
            'waba_id' => 'required|string',
            'access_token' => 'required|string',
            'webhook_verify_token' => 'required|string',
            'sender_phone_number' => 'required|string',
        ]);

        try {
            $tenantId = getUserCompany();
            $accountService->createOrUpdateAccount($tenantId, [
                'phone_number_id' => $this->phone_number_id,
                'waba_id' => $this->waba_id,
                'access_token' => $this->access_token,
                'webhook_verify_token' => $this->webhook_verify_token,
                'sender_phone_number' => $this->sender_phone_number,
                'status' => 'ACTIVE',
            ]);

            $this->isAccountConfigured = true;
            $this->successMessage = 'WhatsApp Integration settings saved successfully!';
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to save settings: ' . $e->getMessage();
        }
    }

    /**
     * Validate current account connection with Meta
     */
    public function validateConnection(\App\Services\Messaging\MetaWhatsAppProvider $provider): void
    {
        $this->resetFeedback();
        $tenantId = getUserCompany();
        $account = TenantWhatsAppAccount::where('tenant_id', $tenantId)->first();

        if (!$account) {
            $this->errorMessage = 'Configure your WhatsApp credentials first.';
            return;
        }

        $result = $provider->validateConnection($account);

        if ($result['success']) {
            $account->update([
                'status' => $result['status'] === 'APPROVED' ? 'ACTIVE' : $result['status'],
            ]);

            $this->successMessage = sprintf(
                "Connection verified successfully! Business: '%s', Status: %s, Quality Rating: %s, Verification: %s.",
                $result['verified_name'],
                $result['status'],
                $result['quality_rating'],
                $result['code_verification_status']
            );
        } else {
            $this->errorMessage = "Connection check failed: " . $result['error'];
        }
    }

    /**
     * Sync Templates from Meta Cloud API
     */
    public function syncTemplates(MetaTemplateService $templateService): void
    {
        $this->resetFeedback();
        $tenantId = getUserCompany();
        $account = TenantWhatsAppAccount::where('tenant_id', $tenantId)->first();

        if (!$account) {
            $this->errorMessage = 'Configure your WhatsApp credentials first.';
            return;
        }

        try {
            $result = $templateService->syncTemplates($account);
            if ($result['success']) {
                $this->successMessage = "Successfully synchronized {$result['count']} templates from Meta Business Manager!";
            } else {
                $this->errorMessage = "Meta Sync failed: " . $result['error'];
            }
        } catch (\Exception $e) {
            $this->errorMessage = "Template Sync exception: " . $e->getMessage();
        }
    }

    /**
     * Add Quick Reply Button in Template Form
     */
    public function addButton(): void
    {
        $this->tpl_buttons[] = ['type' => 'QUICK_REPLY', 'text' => ''];
    }

    /**
     * Remove Button
     */
    public function removeButton(int $index): void
    {
        unset($this->tpl_buttons[$index]);
        $this->tpl_buttons = array_values($this->tpl_buttons);
    }

    /**
     * Submit Template to Meta for Approval
     */
    public function submitTemplate(MetaTemplateService $templateService): void
    {
        $this->resetFeedback();
        $this->validate([
            'tpl_name' => 'required|string|regex:/^[a-z0-9_]+$/',
            'tpl_body_content' => 'required|string',
            'tpl_header_type' => 'required|in:NONE,TEXT,IMAGE,VIDEO,DOCUMENT',
            'tpl_header_content' => 'required_if:tpl_header_type,TEXT|nullable|string|max:60',
        ]);

        $tenantId = getUserCompany();
        $account = TenantWhatsAppAccount::where('tenant_id', $tenantId)->first();

        if (!$account) {
            $this->errorMessage = 'Configure your WhatsApp credentials first.';
            return;
        }

        try {
            // Clean buttons
            $cleanButtons = [];
            foreach ($this->tpl_buttons as $btn) {
                if (!empty($btn['text'])) {
                    $cleanButtons[] = [
                        'type' => 'QUICK_REPLY',
                        'text' => trim($btn['text'])
                    ];
                }
            }

            // Create template locally first in PENDING state
            $variables = $templateService->parseVariables($this->tpl_body_content);

            $template = MessageTemplate::create([
                'tenant_id' => $tenantId,
                'name' => $this->tpl_name,
                'category' => $this->tpl_category,
                'language' => $this->tpl_language,
                'header_type' => $this->tpl_header_type,
                'header_content' => $this->tpl_header_type === 'TEXT' ? $this->tpl_header_content : null,
                'body_content' => $this->tpl_body_content,
                'footer_content' => $this->tpl_footer_content ?: null,
                'buttons_json' => !empty($cleanButtons) ? $cleanButtons : null,
                'variables_json' => $variables,
                'status' => 'PENDING',
            ]);

            // Submit request to Meta
            $result = $templateService->submitTemplate($account, $template);

            if ($result['success']) {
                $this->successMessage = "Template created and submitted to Meta! Status: " . ($result['status'] ?? 'PENDING');
                $this->resetTemplateForm();
            } else {
                $template->delete(); // Rollback local draft
                $this->errorMessage = "Meta Submission Rejected: " . $result['error'];
            }

        } catch (\Exception $e) {
            $this->errorMessage = "Submission exception: " . $e->getMessage();
        }
    }

    private function resetTemplateForm(): void
    {
        $this->tpl_name = '';
        $this->tpl_category = 'UTILITY';
        $this->tpl_language = 'en';
        $this->tpl_header_type = 'NONE';
        $this->tpl_header_content = '';
        $this->tpl_body_content = '';
        $this->tpl_footer_content = '';
        $this->tpl_buttons = [];
    }

    /**
     * Map Event to Template
     */
    public function saveEventMapping(): void
    {
        $this->resetFeedback();
        $this->validate([
            'map_event_code' => 'required|string',
            'map_template_id' => 'required|exists:whatsapp_message_templates,id',
        ]);

        try {
            $tenantId = getUserCompany();

            // Deactivate any existing mapping for this event to avoid conflicts
            EventMessageMapping::where('tenant_id', $tenantId)
                ->where('event_code', $this->map_event_code)
                ->update(['active' => false]);

            EventMessageMapping::create([
                'tenant_id' => $tenantId,
                'event_code' => $this->map_event_code,
                'template_id' => $this->map_template_id,
                'active' => $this->map_active,
            ]);

            $this->successMessage = 'Event notification mapping saved successfully!';
            $this->map_event_code = '';
            $this->map_template_id = '';
            $this->map_active = true;
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to map event: ' . $e->getMessage();
        }
    }

    public function toggleMapping(int $mappingId): void
    {
        $tenantId = getUserCompany();
        $mapping = EventMessageMapping::where('id', $mappingId)->where('tenant_id', $tenantId)->first();
        if ($mapping) {
            $mapping->update(['active' => !$mapping->active]);
            $this->successMessage = 'Mapping status updated successfully!';
        }
    }

    /**
     * Create Target Audience
     */
    public function saveAudience(): void
    {
        $this->resetFeedback();
        $this->validate([
            'aud_name' => 'required|string',
        ]);

        try {
            $tenantId = getUserCompany();
            
            $rules = [];
            if ($this->aud_country_id) {
                $rules['country_id'] = $this->aud_country_id;
            }
            if ($this->aud_is_internal !== null) {
                $rules['is_internal'] = $this->aud_is_internal;
            }

            MessageAudience::create([
                'tenant_id' => $tenantId,
                'name' => $this->aud_name,
                'rules_json' => $rules,
            ]);

            $this->successMessage = 'Campaign audience created successfully!';
            $this->aud_name = '';
            $this->aud_country_id = '';
            $this->aud_is_internal = null;
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to save audience: ' . $e->getMessage();
        }
    }

    /**
     * Create Campaign
     */
    public function saveCampaign(): void
    {
        $this->resetFeedback();
        $this->validate([
            'camp_name' => 'required|string',
            'camp_audience_id' => 'required|exists:whatsapp_message_audiences,id',
            'camp_template_id' => 'required|exists:whatsapp_message_templates,id',
        ]);

        try {
            $tenantId = getUserCompany();

            MessageCampaign::create([
                'tenant_id' => $tenantId,
                'name' => $this->camp_name,
                'audience_id' => $this->camp_audience_id,
                'template_id' => $this->camp_template_id,
                'status' => 'DRAFT',
            ]);

            $this->successMessage = 'Campaign created successfully!';
            $this->camp_name = '';
            $this->camp_audience_id = '';
            $this->camp_template_id = '';
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to create campaign: ' . $e->getMessage();
        }
    }

    /**
     * Dispatch Campaign Job
     */
    public function triggerCampaign(int $campaignId): void
    {
        $this->resetFeedback();
        $tenantId = getUserCompany();
        $campaign = MessageCampaign::where('id', $campaignId)->where('tenant_id', $tenantId)->first();
        if ($campaign && $campaign->status === 'DRAFT') {
            try {
                \App\Jobs\Messaging\ProcessCampaignJob::dispatch($campaignId);
                $campaign->update(['status' => 'QUEUED']);
                $this->successMessage = 'Campaign dispatch queued successfully!';
            } catch (\Exception $e) {
                $this->errorMessage = 'Failed to launch campaign: ' . $e->getMessage();
            }
        }
    }

    /**
     * Retry failed Campaign
     */
    public function retryCampaign(int $campaignId): void
    {
        $this->resetFeedback();
        $tenantId = getUserCompany();
        $campaign = MessageCampaign::where('id', $campaignId)->where('tenant_id', $tenantId)->first();

        if ($campaign) {
            try {
                $campaign->update(['status' => 'DRAFT']);
                \App\Jobs\Messaging\ProcessCampaignJob::dispatch($campaignId);
                $campaign->update(['status' => 'QUEUED']);
                $this->successMessage = 'Campaign retry dispatched successfully!';
            } catch (\Exception $e) {
                $this->errorMessage = 'Failed to retry campaign: ' . $e->getMessage();
            }
        }
    }

    /**
     * Retry single failed message
     */
    public function retryMessage(int $messageId): void
    {
        $this->resetFeedback();
        $tenantId = getUserCompany();
        $message = \App\Models\Messaging\OutboundMessage::where('id', $messageId)->where('tenant_id', $tenantId)->first();

        if ($message && $message->status === 'FAILED') {
            try {
                $message->update([
                    'status' => 'queued',
                    'attempts' => 0,
                    'error' => null,
                ]);

                \App\Jobs\Messaging\SendWhatsAppMessageJob::dispatch($tenantId, $messageId);
                $this->successMessage = 'Outbound message retry queued successfully!';
            } catch (\Exception $e) {
                $this->errorMessage = 'Failed to retry message: ' . $e->getMessage();
            }
        }
    }

    /**
     * Send direct reply to active customer conversation.
     */
    public function sendChatMessage(MetaWhatsAppProvider $provider): void
    {
        $this->resetFeedback();
        $this->validate([
            'replyBody' => 'required|string|max:1000',
            'activeConversationId' => 'required|integer',
        ]);

        try {
            $tenantId = getUserCompany();
            $conversation = \App\Models\Messaging\WhatsAppConversation::where('id', $this->activeConversationId)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$conversation) {
                throw new \Exception("Active conversation not found.");
            }

            $account = \App\Models\Messaging\TenantWhatsAppAccount::where('tenant_id', $tenantId)
                ->where('active', true)
                ->first();

            if (!$account) {
                throw new \Exception("No active WhatsApp account configurations found.");
            }

            // Send direct message via Meta Graph API
            $result = $provider->sendDirectMessage($account, $conversation->phone_number, $this->replyBody);

            if ($result['success']) {
                // Save outbound message record
                \App\Models\Messaging\WhatsAppMessage::create([
                    'tenant_id' => $tenantId,
                    'conversation_id' => $conversation->id,
                    'direction' => 'outbound',
                    'message_type' => 'text',
                    'message_body' => $this->replyBody,
                    'wamid' => $result['message_id'],
                    'timestamp' => now(),
                ]);

                $conversation->update(['last_message_at' => now()]);
                $this->replyBody = '';
                $this->successMessage = 'Reply sent successfully!';
            } else {
                throw new \Exception($result['error'] ?? 'API delivery failed.');
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to send reply: ' . $e->getMessage();
        }
    }

    /**
     * Select active conversation chat.
     */
    public function selectConversation(int $id): void
    {
        $this->activeConversationId = $id;
        $this->resetFeedback();
    }

    public function render()
    {
        $tenantId = getUserCompany();

        return view('livewire.system.whatsapp-configuration-manager', [
            'templates' => MessageTemplate::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->get(),
            'mappings' => EventMessageMapping::with('template')->where('tenant_id', $tenantId)->get(),
            'audiences' => MessageAudience::where('tenant_id', $tenantId)->get(),
            'campaigns' => MessageCampaign::with(['template', 'audience'])->where('tenant_id', $tenantId)->get(),
            'countries' => \App\Country::all(), // Pre-existing system country list
            'availableEvents' => [
                'SAMPLE_REGISTERED' => 'Sample Registered / Received',
                'RESULT_READY' => 'Lab Result Ready / Certified',
                'INVOICE_GENERATED' => 'Invoice Issued',
                'SUPPLIER_RFQ' => 'Request For Quotation Issued',
            ],
            'activityLogs' => WhatsAppActivityLog::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->paginate(10, ['*'], 'logsPage'),
            'webhookEvents' => WhatsAppWebhookEvent::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->paginate(10, ['*'], 'webhookPage'),
            'outboundMessages' => \App\Models\Messaging\OutboundMessage::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->paginate(10, ['*'], 'msgPage'),
            'conversations' => \App\Models\Messaging\WhatsAppConversation::where('tenant_id', $tenantId)->orderBy('last_message_at', 'desc')->get(),
            'activeMessages' => $this->activeConversationId
                ? \App\Models\Messaging\WhatsAppMessage::where('conversation_id', $this->activeConversationId)->orderBy('timestamp', 'asc')->get()
                : collect(),
        ]);
    }
}
