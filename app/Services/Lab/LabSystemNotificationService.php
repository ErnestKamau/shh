<?php

namespace App\Services\Lab;

use App\Models\Lab\LabUserNotification;
use App\QuotationHeader;
use App\User;
use Illuminate\Support\Collection;

final class LabSystemNotificationService
{
    public const TYPE_QUOTATION_APPROVAL = 'quotation_approval';

    public const TYPE_QUOTATION_APPROVED_READY_TO_SEND = 'quotation_approved_ready_to_send';

    /**
     * @param  Collection<int, User>|iterable<User>  $users
     */
    public function notifyUsers(
        iterable $users,
        string $type,
        string $title,
        string $message,
        ?string $notifiableType = null,
        ?string $notifiableId = null,
        array $metadata = [],
    ): void {
        foreach ($users as $user) {
            if (! $user instanceof User || empty($user->id)) {
                continue;
            }

            LabUserNotification::query()->create([
                'user_id' => $user->id,
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId,
                'notification_type' => $type,
                'title' => $title,
                'message' => $message,
                'metadata' => $metadata,
                'is_read' => false,
                'read_at' => null,
            ]);
        }
    }

    public function notifyQuotationApprovalRequired(QuotationHeader $header, iterable $approvers, string $actorName): void
    {
        $header->loadMissing('labSections');
        $quoteNumber = (string) ($header->quote_number ?? '');
        $labSections = $header->labSections->pluck('name')->filter()->implode(', ');
        $title = $quoteNumber !== '' ? $quoteNumber : 'Quotation';
        if ($labSections !== '') {
            $title .= ' - '.$labSections;
        }

        $this->notifyUsers(
            $approvers,
            self::TYPE_QUOTATION_APPROVAL,
            $title,
            $actorName.' submitted quotation '.$quoteNumber.' for your approval.',
            QuotationHeader::class,
            (string) $header->id,
            [
                'quotation_header_id' => (string) $header->id,
                'quote_number' => $quoteNumber,
                'lab_sections' => $labSections,
                'sample_submission_request_id' => (string) ($header->sample_submission_request_id ?? ''),
            ],
        );
    }

    /**
     * Tell the requester that approval is done and they must send from LIMS (not from email).
     */
    public function notifyQuotationApprovedReadyToSend(QuotationHeader $header, User $requester, string $approverName): void
    {
        $header->loadMissing('labSections');
        $quoteNumber = (string) ($header->quote_number ?? '');
        $labSections = $header->labSections->pluck('name')->filter()->implode(', ');
        $title = $quoteNumber !== '' ? $quoteNumber : 'Quotation';
        if ($labSections !== '') {
            $title .= ' - '.$labSections;
        }

        $this->notifyUsers(
            [$requester],
            self::TYPE_QUOTATION_APPROVED_READY_TO_SEND,
            $title,
            $approverName.' approved quotation '.$quoteNumber.'. Log into LIMS to send it to the customer.',
            QuotationHeader::class,
            (string) $header->id,
            [
                'quotation_header_id' => (string) $header->id,
                'quote_number' => $quoteNumber,
                'lab_sections' => $labSections,
                'sample_submission_request_id' => (string) ($header->sample_submission_request_id ?? ''),
            ],
        );
    }

    /**
     * @return Collection<int, LabUserNotification>
     */
    public function unreadForUser(string $userId, int $limit = 20): Collection
    {
        return LabUserNotification::query()
            ->where('user_id', $userId)
            ->unread()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function unreadCountForUser(string $userId): int
    {
        return (int) LabUserNotification::query()
            ->where('user_id', $userId)
            ->unread()
            ->count();
    }

    public function markAsRead(string $notificationId, string $userId): void
    {
        $notification = LabUserNotification::query()
            ->where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        $notification?->markAsRead();
    }

    public function markAllReadForUser(string $userId): void
    {
        LabUserNotification::query()
            ->where('user_id', $userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
