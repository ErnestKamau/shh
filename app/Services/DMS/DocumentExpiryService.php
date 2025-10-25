<?php

namespace App\Services\DMS;

use App\User;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentNotification;
use App\Models\DMS\DocumentExpiryNotificationSetting;
use App\Models\DMS\DocumentAuditLog;
use App\Notifications\DMS\DocumentExpiringNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class DocumentExpiryService
{
    /**
     * Check for expiring documents and send notifications
     *
     * @return array
     */
    public function checkAndNotifyExpiringDocuments(): array
    {
        $documentsChecked = 0;
        $notificationsSent = 0;
        $documentsExpired = 0;

        // Get all documents with expiry dates
        $documents = Document::whereNotNull('expiry_date')
            ->where('is_archived', false)
            ->get();

        $documentsChecked = $documents->count();

        foreach ($documents as $document) {
            // Check if expired
            if ($document->isExpired()) {
                $document->update(['is_expiring' => true]);
                $documentsExpired++;
                continue;
            }

            // Get notification settings for document owner and stakeholders
            $usersToNotify = $this->getUsersToNotify($document);

            foreach ($usersToNotify as $user) {
                $settings = DocumentExpiryNotificationSetting::getOrCreateForUser($user->id);

                // Check if notification is needed based on frequency
                if ($this->shouldSendNotification($document, $settings)) {
                    $this->sendExpiryNotification($document, $user, $settings);
                    $notificationsSent++;

                    // Update last notification sent time
                    $document->update([
                        'is_expiring' => true,
                        'last_expiry_notification_sent_at' => now(),
                    ]);
                }
            }
        }

        return [
            'documents_checked' => $documentsChecked,
            'notifications_sent' => $notificationsSent,
            'documents_expired' => $documentsExpired,
        ];
    }

    /**
     * Determine if notification should be sent based on frequency settings
     *
     * @param Document $document
     * @param DocumentExpiryNotificationSetting $settings
     * @return bool
     */
    protected function shouldSendNotification(
        Document $document,
        DocumentExpiryNotificationSetting $settings
    ): bool {
        // Check if within notification window
        $daysUntilExpiry = $document->expiry_date->diffInDays(now());
        
        if ($daysUntilExpiry > $settings->notification_frequency_days) {
            return false;
        }

        // Check if notification was already sent recently
        if ($document->last_expiry_notification_sent_at) {
            $daysSinceLastNotification = now()->diffInDays($document->last_expiry_notification_sent_at);
            
            if ($daysSinceLastNotification < 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get users who should be notified about document expiry
     *
     * @param Document $document
     * @return Collection
     */
    protected function getUsersToNotify(Document $document): Collection
    {
        $users = collect();

        // Add document owner
        if ($document->owner) {
            $users->push($document->owner);
        }

        // Add document creator if different from owner
        if ($document->creator && $document->creator_id !== $document->owner_id) {
            $users->push($document->creator);
        }

        // Add users with 'edit' permission on this document
        $permissions = $document->permissions()
            ->where('permission_type', 'edit')
            ->where('subject_type', User::class)
            ->with('subject')
            ->get();

        foreach ($permissions as $permission) {
            if ($permission->subject) {
                $users->push($permission->subject);
            }
        }

        return $users->unique('id');
    }

    /**
     * Send expiry notification to a user
     *
     * @param Document $document
     * @param User $user
     * @param DocumentExpiryNotificationSetting $settings
     * @return void
     */
    protected function sendExpiryNotification(
        Document $document,
        User $user,
        DocumentExpiryNotificationSetting $settings
    ): void {
        $daysUntilExpiry = $document->expiry_date->diffInDays(now());
        
        $message = $daysUntilExpiry === 0
            ? "Document '{$document->title}' expires today!"
            : "Document '{$document->title}' will expire in {$daysUntilExpiry} day(s).";

        // Create in-app notification if enabled
        if ($settings->app_notifications_enabled) {
            DocumentNotification::create([
                'user_id' => $user->id,
                'document_id' => $document->id,
                'notification_type' => 'expiry_warning',
                'title' => 'Document Expiring Soon',
                'message' => $message,
                'metadata' => [
                    'expiry_date' => $document->expiry_date->toDateString(),
                    'days_until_expiry' => $daysUntilExpiry,
                ],
            ]);
        }

        // Send email notification if enabled
        if ($settings->email_notifications_enabled) {
            Notification::send($user, new DocumentExpiringNotification($document, $daysUntilExpiry));
        }

        // Log the notification
        DocumentAuditLog::log(
            $document,
            'expiry_notification_sent',
            null,
            ['user_id' => $user->id, 'days_until_expiry' => $daysUntilExpiry],
            "Expiry notification sent to {$user->name}"
        );
    }

    /**
     * Get expiring documents for a user
     *
     * @param User $user
     * @param int $days
     * @return Collection
     */
    public function getExpiringDocumentsForUser(User $user, int $days = 30): Collection
    {
        $expiryDate = Carbon::now()->addDays($days);

        return Document::where(function($query) use ($user) {
            $query->where('owner_id', $user->id)
                  ->orWhere('created_by', $user->id);
        })
        ->whereNotNull('expiry_date')
        ->where('expiry_date', '<=', $expiryDate)
        ->where('is_archived', false)
        ->orderBy('expiry_date', 'asc')
        ->get();
    }

    /**
     * Update document expiry date
     *
     * @param Document $document
     * @param Carbon|null $expiryDate
     * @return bool
     */
    public function updateExpiryDate(Document $document, ?Carbon $expiryDate): bool
    {
        $oldDate = $document->expiry_date;

        $result = $document->update([
            'expiry_date' => $expiryDate,
            'is_expiring' => false,
            'last_expiry_notification_sent_at' => null,
        ]);

        if ($result) {
            DocumentAuditLog::log(
                $document,
                'updated',
                ['expiry_date' => $oldDate?->toDateString()],
                ['expiry_date' => $expiryDate?->toDateString()],
                'Expiry date updated'
            );
        }

        return $result;
    }
}

