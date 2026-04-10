<?php

namespace App\Observers;

use App\Models\CRM\Complaint;
use App\Models\CRM\TicketChangeHistory;
use App\Models\CRM\TicketStatus;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketUpdatedNotification;
use App\Notifications\TicketEscalatedNotification;
use App\User;
use Illuminate\Support\Facades\Log;

class TicketObserver
{
    /**
     * Handle the Complaint "created" event.
     */
    public function created(Complaint $complaint): void
    {
        // Log creation
        TicketChangeHistory::create([
            'ticket_id' => $complaint->id,
            'user_id' => $this->getUserIdFromName($complaint->created_by),
            'change_type' => 'created',
            'field_name' => 'ticket',
            'old_value' => null,
            'new_value' => 'Ticket created',
            'created_at' => now(),
        ]);

        // Notify support staff (if ticket created from user portal)
        // Only notify developers who have the 'receive_ticket_notifications' permission
        if ($complaint->submitted_from === 'ticketing_system') {
            $supportStaff = User::where('is_support_staff', 1)
                ->where('active', 1)
                ->get();
            
            foreach ($supportStaff as $staff) {
                // Check if user has permission to receive ticket notifications
                if ($staff->hasTicketPermission('receive_ticket_notifications')) {
                    try {
                        $staff->notify(new TicketCreatedNotification($complaint));
                    } catch (\Exception $e) {
                        Log::error('Failed to send ticket created notification', [
                            'user_id' => $staff->id,
                            'ticket_id' => $complaint->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Handle the Complaint "updated" event.
     */
    public function updated(Complaint $complaint): void
    {
        $original = $complaint->getOriginal();
        $changes = $complaint->getChanges();

        // Track specific field changes
        $trackedFields = [
            'assigned_to' => 'assigned',
            'priority' => 'updated',
            'complaint_workflow' => 'status_changed',
            'escalated_to_user_id' => 'escalated',
        ];

        foreach ($changes as $field => $newValue) {
            if (in_array($field, array_keys($trackedFields))) {
                $oldValue = $original[$field] ?? null;
                $changeType = $trackedFields[$field];

                // Log change
                TicketChangeHistory::create([
                    'ticket_id' => $complaint->id,
                    'user_id' => auth()->id(),
                    'change_type' => $changeType,
                    'field_name' => $field,
                    'old_value' => $this->formatValue($oldValue),
                    'new_value' => $this->formatValue($newValue),
                    'created_at' => now(),
                ]);

                // Send notifications based on change type
                if ($field === 'assigned_to' && $newValue) {
                    $assignedUser = User::where('name', $newValue)->first();
                    if ($assignedUser) {
                        try {
                            $assignedUser->notify(new TicketAssignedNotification($complaint, auth()->user()));
                        } catch (\Exception $e) {
                            Log::error('Failed to send assignment notification', [
                                'user_id' => $assignedUser->id,
                                'ticket_id' => $complaint->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }

                if ($field === 'escalated_to_user_id' && $newValue) {
                    $escalatedUser = User::find($newValue);
                    if ($escalatedUser) {
                        try {
                            $escalatedUser->notify(new TicketEscalatedNotification(
                                $complaint,
                                $complaint->escalation_reason
                            ));
                        } catch (\Exception $e) {
                            Log::error('Failed to send escalation notification', [
                                'user_id' => $escalatedUser->id,
                                'ticket_id' => $complaint->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }

                if ($field === 'complaint_workflow') {
                    // Check if the new status is "Resolved" by querying the database
                    $resolvedStatus = TicketStatus::active()
                        ->where(function($query) {
                            $query->where('name', 'like', '%Resolved%')
                                  ->orWhere('name', 'like', '%Resolve%');
                        })
                        ->where('workflow_value', $newValue)
                        ->first();
                    
                    if ($resolvedStatus) {
                        // Ticket resolved - notify customer
                        $this->notifyCustomer($complaint, 'resolved');
                    }
                }
            }
        }
    }

    /**
     * Handle the Complaint "deleted" event.
     */
    public function deleted(Complaint $complaint): void
    {
        // Log deletion (soft delete)
        TicketChangeHistory::create([
            'ticket_id' => $complaint->id,
            'user_id' => auth()->id(),
            'change_type' => 'updated',
            'field_name' => 'deleted',
            'old_value' => 'Active',
            'new_value' => 'Deleted',
            'created_at' => now(),
        ]);
    }

    /**
     * Handle the Complaint "restored" event.
     */
    public function restored(Complaint $complaint): void
    {
        // Log restoration
        TicketChangeHistory::create([
            'ticket_id' => $complaint->id,
            'user_id' => auth()->id(),
            'change_type' => 'updated',
            'field_name' => 'deleted',
            'old_value' => 'Deleted',
            'new_value' => 'Restored',
            'created_at' => now(),
        ]);
    }

    /**
     * Get user ID from name
     */
    private function getUserIdFromName(?string $name): ?int
    {
        if (!$name) {
            return null;
        }

        $user = User::where('name', $name)->first();
        return $user ? $user->id : null;
    }

    /**
     * Format value for history
     */
    private function formatValue($value): ?string
    {
        if ($value === null) {
            return 'N/A';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }

    /**
     * Notify customer about ticket update
     */
    private function notifyCustomer(Complaint $complaint, string $type): void
    {
        // Get customer email (similar to DeveloperTicketController)
        $customerEmail = null;
        
        if ($complaint->customer) {
            $customer = \App\Models\CRM\CRMCustomer::where('name', $complaint->customer)->first();
            if ($customer) {
                $contact = \App\Models\CRM\CustomerContact::where('crm_customer_id', $customer->id)
                    ->where('active', 1)
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->first();
                if ($contact) {
                    $customerEmail = $contact->email;
                }
            }
        }

        if (!$customerEmail && $complaint->created_by) {
            $user = User::where('name', $complaint->created_by)->first();
            if ($user && $user->email) {
                $customerEmail = $user->email;
            }
        }

        if ($customerEmail) {
            try {
                if ($type === 'resolved') {
                    $ticketLink = route('tickets.show', $complaint->id);
                    $subject = '[' . config('app.name') . '] Ticket Resolved: ' . $complaint->ticket_no;
                    
                    $body = "Hi,<br><br>
                            Your ticket <strong>{$complaint->ticket_no}</strong> has been resolved!<br><br>";
                    
                    if ($complaint->resolution) {
                        $body .= "<strong>Resolution:</strong><br>{$complaint->resolution}<br><br>";
                    }
                    
                    $body .= "<a href='{$ticketLink}'>View Ticket</a><br><br>
                            Thank you for using our Help Desk!";
                    
                    notify_user($body, $customerEmail, $subject);
                }
            } catch (\Exception $e) {
                Log::error('Failed to notify customer', [
                    'ticket_id' => $complaint->id,
                    'email' => $customerEmail,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
