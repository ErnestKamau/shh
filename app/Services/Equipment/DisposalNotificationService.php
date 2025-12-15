<?php

namespace App\Services\Equipment;

use App\Models\Equipments\EquipmentDisposal;
use App\Models\Equipments\EquipmentDisposalApproval;
use App\User;
use App\Jobs\Equipment\SendDisposalApprovalNotification;
use App\Jobs\Equipment\SendDisposalDecisionNotification;
use Illuminate\Support\Facades\Mail;

class DisposalNotificationService
{
    /**
     * Notify approver that approval is required
     *
     * @param EquipmentDisposal $disposal
     * @param User $approver
     * @return void
     */
    public function notifyApprovalRequired(EquipmentDisposal $disposal, User $approver): void
    {
        // Queue notification job
        SendDisposalApprovalNotification::dispatch($disposal, $approver);
    }

    /**
     * Notify requester of approval decision
     *
     * @param EquipmentDisposal $disposal
     * @param string $decision
     * @return void
     */
    public function notifyApprovalDecision(EquipmentDisposal $disposal, string $decision): void
    {
        // Queue notification job
        SendDisposalDecisionNotification::dispatch($disposal, $decision);
    }

    /**
     * Notify that decommissioning is required
     *
     * @param EquipmentDisposal $disposal
     * @return void
     */
    public function notifyDecommissioningRequired(EquipmentDisposal $disposal): void
    {
        // Notify the requester and equipment custodian
        $users = collect([
            $disposal->requester,
            // Add equipment custodian if different
        ])->filter()->unique('id');

        foreach ($users as $user) {
            // Send email notification
            $this->sendEmail($user, [
                'subject' => 'Decommissioning Required - ' . $disposal->equipment->name,
                'message' => "The disposal request for equipment {$disposal->equipment->name} has been approved. Decommissioning is now required.",
                'disposal_id' => $disposal->id,
                'equipment_name' => $disposal->equipment->name,
            ]);
        }
    }

    /**
     * Notify that disposal is complete
     *
     * @param EquipmentDisposal $disposal
     * @return void
     */
    public function notifyDisposalComplete(EquipmentDisposal $disposal): void
    {
        // Notify all stakeholders
        $users = collect([
            $disposal->requester,
            $disposal->executor,
            // Quality Manager, Lab Manager, etc.
        ])->filter()->unique('id');

        foreach ($users as $user) {
            $this->sendEmail($user, [
                'subject' => 'Disposal Completed - ' . $disposal->equipment->name,
                'message' => "The disposal of equipment {$disposal->equipment->name} has been completed.",
                'disposal_id' => $disposal->id,
                'equipment_name' => $disposal->equipment->name,
                'disposal_date' => $disposal->disposal_date ? $disposal->disposal_date->format('Y-m-d') : 'N/A',
            ]);
        }
    }

    /**
     * Send email notification
     *
     * @param User $user
     * @param array $data
     * @return void
     */
    protected function sendEmail(User $user, array $data): void
    {
        try {
            // Use Laravel Mail to send notification
            // This is a placeholder - you'd use your email template
            Mail::raw($data['message'], function ($message) use ($user, $data) {
                $message->to($user->email)
                        ->subject($data['subject']);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send disposal notification email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'data' => $data,
            ]);
        }
    }

    /**
     * Send SMS notification (if configured)
     *
     * @param User $user
     * @param string $message
     * @return void
     */
    protected function sendSMS(User $user, string $message): void
    {
        // Placeholder for SMS notification
        // Would integrate with SMS service provider
        \Log::info('SMS notification would be sent', [
            'user_id' => $user->id,
            'phone' => $user->phone ?? 'N/A',
            'message' => $message,
        ]);
    }
}

