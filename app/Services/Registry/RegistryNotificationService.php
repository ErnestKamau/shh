<?php

namespace App\Services\Registry;

use App\Models\Registry\RegistryRequest;
use App\Notifications\Registry\RegistryApprovalRequiredNotification;
use App\Notifications\Registry\RegistryRequestAssignedNotification;
use App\Notifications\Registry\RegistryRequestCompletedNotification;
use App\Notifications\Registry\RegistryRequestCreatedNotification;
use App\Notifications\Registry\RegistryRequestRejectedNotification;
use App\Models\Auth\Role;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class RegistryNotificationService
{
    public function notifyCreated(RegistryRequest $request): void
    {
        $users = $this->adminRecipients();
        Notification::send($users, new RegistryRequestCreatedNotification($request));
    }

    public function notifyApprovalRequired(RegistryRequest $request): void
    {
        $users = app(RegistryRoutingService::class)->resolveApproversForRequest($request)
            ->pluck('user')
            ->filter();

        if ($users->isEmpty()) {
            $users = $this->adminRecipients();
        }

        Notification::send($users, new RegistryApprovalRequiredNotification($request));
    }

    public function notifyAssigned(RegistryRequest $request): void
    {
        if ($request->assignee === null) {
            return;
        }

        $request->assignee->notify(new RegistryRequestAssignedNotification($request));
    }

    public function notifyRejected(RegistryRequest $request): void
    {
        if ($request->submitter !== null) {
            $request->submitter->notify(new RegistryRequestRejectedNotification($request));
        }
    }

    public function notifyCompleted(RegistryRequest $request): void
    {
        $recipients = collect([$request->submitter, $request->assignee])->filter();
        Notification::send($recipients, new RegistryRequestCompletedNotification($request));
    }

    protected function adminRecipients(): Collection
    {
        $adminRoleNames = Role::query()
            ->where('guard_name', 'web')
            ->whereRaw('LOWER(name) in (?, ?, ?, ?, ?)', [
                'admin',
                'super admin',
                'super-admin',
                'system admin',
                'system-admin',
            ])
            ->pluck('name');

        if ($adminRoleNames->isEmpty()) {
            return collect();
        }

        return User::role($adminRoleNames->all())
            ->where('active', 1)
            ->limit(5)
            ->get();
    }
}
