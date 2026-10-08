<?php

namespace App\Livewire\Lab;

use App\Models\Lab\EquipmentUsageRequest;
use App\Models\Lab\LabUserNotification;
use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Models\Billing\Pricelist;
use App\Services\Lab\LabSystemNotificationService;
use Livewire\Component;

class SystemNotificationsBell extends Component
{
    public bool $open = false;

    protected $listeners = [
        'lab-notifications-updated' => '$refresh',
    ];

    public function markRead(string $notificationId): void
    {
        $userId = (string) (auth()->id() ?? '');
        if ($userId === '') {
            return;
        }

        app(LabSystemNotificationService::class)->markAsRead($notificationId, $userId);
    }

    public function markAllRead(): void
    {
        $userId = (string) (auth()->id() ?? '');
        if ($userId === '') {
            return;
        }

        app(LabSystemNotificationService::class)->markAllReadForUser($userId);
        $this->dispatch('imara-toast', [
            'type' => 'success',
            'title' => 'Notifications cleared',
            'message' => 'All notifications were marked as read.',
            'durationMs' => 7000,
        ]);
    }

    public function openNotification(string $notificationId): mixed
    {
        $userId = (string) (auth()->id() ?? '');
        if ($userId === '') {
            return null;
        }

        $notification = LabUserNotification::query()
            ->where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification === null) {
            return null;
        }

        $notification->markAsRead();
        $this->open = false;

        $notifiableType = (string) ($notification->notifiable_type ?? '');
        $notificationType = (string) ($notification->notification_type ?? '');

        if ($notifiableType === EquipmentUsageRequest::class) {
            return redirect()->route('lab.equipment-requests.index');
        }

        $metadata = is_array($notification->metadata) ? $notification->metadata : [];

        if ($notificationType === LabSystemNotificationService::TYPE_PRICELIST_IMPORT
            || $notifiableType === Pricelist::class
        ) {
            $pricelistId = trim((string) ($metadata['pricelist_id'] ?? ''));
            if ($pricelistId === '' && $notifiableType === Pricelist::class) {
                $pricelistId = trim((string) ($notification->notifiable_id ?? ''));
            }

            if ($pricelistId !== '' && Pricelist::query()->whereKey($pricelistId)->exists()) {
                return redirect()->route('show-pricelist', ['id' => $pricelistId]);
            }

            return redirect()->route('view-pricelists');
        }

        // Billing / quotation approval notifications must open the quotation workspace —
        // never the enquiry request view (quote may still be incomplete / not usable there).
        $isQuotationNotification = $notifiableType === QuotationHeader::class
            || in_array($notificationType, [
                LabSystemNotificationService::TYPE_QUOTATION_APPROVAL,
                LabSystemNotificationService::TYPE_QUOTATION_APPROVED_READY_TO_SEND,
            ], true);

        if ($isQuotationNotification) {
            $quotationId = trim((string) ($metadata['quotation_header_id'] ?? ''));
            if ($quotationId === '' && $notifiableType === QuotationHeader::class) {
                $quotationId = trim((string) ($notification->notifiable_id ?? ''));
            }

            $header = $quotationId !== ''
                ? QuotationHeader::query()->find($quotationId)
                : null;

            if ($header !== null) {
                $status = (string) ($header->status ?? '');
                if (in_array($status, ['Quote In Approval', 'Quote Complete'], true)) {
                    return redirect()->route('view_quotation_final', [
                        'id' => $header->id,
                        'stage' => $status,
                    ]);
                }

                return redirect()->route('add-qoute-details-view', [
                    'id' => $header->id,
                    'stage' => $status !== '' ? $status : 'Quote In Preparation',
                ]);
            }
        }

        $requestId = (string) ($metadata['sample_submission_request_id'] ?? '');
        if ($requestId !== '') {
            $enquiry = SampleSubmissionRequest::query()
                ->with('submissionFormInstance.submissionForm')
                ->find($requestId);

            $instance = $enquiry?->submissionFormInstance;
            $form = $instance?->submissionForm;
            if ($instance !== null && $form !== null) {
                return redirect()->route('submission-forms.instances.show', [
                    'submissionForm' => $form->id,
                    'instance' => $instance->id,
                ]);
            }
        }

        return null;
    }

    /**
     * Safe handler when client-side code probes $wire.toJSON during serialization
     * (e.g. JSON.stringify on an object holding the $wire proxy).
     */
    public function toJSON(mixed $value = null): array
    {
        return ['id' => $this->getId()];
    }

    public function render()
    {
        $userId = (string) (auth()->id() ?? '');
        $service = app(LabSystemNotificationService::class);

        $emptyGroups = [
            'today' => collect(),
            'week' => collect(),
            'earlier' => collect(),
        ];

        return view('livewire.lab.system-notifications-bell', [
            'unreadCount' => $userId !== '' ? $service->unreadCountForUser($userId) : 0,
            'attentionCount' => $userId !== '' ? $service->attentionCountForUser($userId) : 0,
            'shouldAlert' => $userId !== '' ? $service->shouldAlertUser($userId) : false,
            'notificationGroups' => $userId !== '' ? $service->recentGroupedForUser($userId, 40) : $emptyGroups,
        ]);
    }
}
