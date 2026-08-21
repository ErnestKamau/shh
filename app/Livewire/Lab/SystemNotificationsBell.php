<?php

namespace App\Livewire\Lab;

use App\Models\Lab\LabUserNotification;
use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Lab\LabSystemNotificationService;
use Livewire\Component;

class SystemNotificationsBell extends Component
{
    public bool $open = false;

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

        $metadata = is_array($notification->metadata) ? $notification->metadata : [];
        $requestId = (string) ($metadata['sample_submission_request_id'] ?? '');
        if ($requestId === '' && (string) ($notification->notifiable_type ?? '') === QuotationHeader::class) {
            $header = QuotationHeader::query()->find($notification->notifiable_id);
            $requestId = (string) ($header?->sample_submission_request_id ?? '');
        }

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

    public function render()
    {
        $userId = (string) (auth()->id() ?? '');
        $service = app(LabSystemNotificationService::class);

        return view('livewire.lab.system-notifications-bell', [
            'unreadCount' => $userId !== '' ? $service->unreadCountForUser($userId) : 0,
            'notifications' => $userId !== '' ? $service->unreadForUser($userId, 12) : collect(),
        ]);
    }
}
