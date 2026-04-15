<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CRM\Complaint;
use App\Models\CRM\TicketChat;
use App\Services\SyncLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeveloperWebhookController extends Controller
{
    private SyncLogger $logger;

    public function __construct(SyncLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle incoming webhook from developer app
     * POST /api/webhooks/developer
     */
    public function handleWebhook(Request $request)
    {
        try {
            $event = $request->input('event');
            $data = $request->input('data');

            Log::info('Webhook received from developer', [
                'event' => $event,
                'ticket_no' => $data['polucon_ticket_no'] ?? null,
            ]);

            // Find ticket by polucon_ticket_no (include trashed for archive/unarchive)
            $ticket = Complaint::withTrashed()->where('ticket_no', $data['polucon_ticket_no'] ?? null)->first();

            if (!$ticket) {
                Log::warning('Webhook for unknown ticket', [
                    'polucon_ticket_no' => $data['polucon_ticket_no'] ?? null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not found',
                ], 404);
            }

            // Log the webhook receipt
            $syncLog = $this->logger->logSyncAttempt(
                $ticket->id,
                $event,
                'inbound',
                $data
            );

            // Handle different event types
            $handled = match ($event) {
                'ticket.status_changed' => $this->handleStatusUpdate($ticket, $data),
                'ticket.priority_changed' => $this->handlePriorityUpdate($ticket, $data),
                'ticket.assigned' => $this->handleAssignmentUpdate($ticket, $data),
                'ticket.chat_message' => $this->handleChatMessage($ticket, $data),
                'ticket.archived' => $this->handleArchived($ticket, $data),
                'ticket.sla_updated' => $this->handleSlaUpdate($ticket, $data),
                default => false,
            };

            if ($handled) {
                $this->logger->logSyncSuccess($syncLog, 200, $data);

                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully',
                ]);
            } else {
                $this->logger->logSyncFailure($syncLog, 400, 'Unknown event type: ' . $event);

                return response()->json([
                    'success' => false,
                    'message' => 'Unknown event type',
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }

    /**
     * Handle status update
     */
    private function handleStatusUpdate(Complaint $ticket, array $data): bool
    {
        if (!isset($data['status']['workflow_value'])) {
            return false;
        }

        $updates = [
            'complaint_workflow' => $data['status']['workflow_value'],
            'last_synced_at' => now(),
            'sync_failed' => false,
            'sync_error' => null,
        ];

        // Sync SLA tracking data if provided
        if (isset($data['sla'])) {
            $sla = $data['sla'];
            
            // Sync SLA level/status
            if (isset($sla['level'])) {
                $updates['sla_level'] = $sla['level'];
            }
            
            // Sync first response tracking
            $firstResponse = $sla['first_response'] ?? null;
            if ($firstResponse) {
                if (isset($firstResponse['responded_at'])) {
                    $updates['first_response_at'] = $firstResponse['responded_at'];
                }
                if (isset($firstResponse['status'])) {
                    $updates['first_response_sla_status'] = $firstResponse['status'];
                }
            }
            
            // Also handle flat SLA fields if they exist
            if (isset($sla['first_response_at'])) {
                $updates['first_response_at'] = $sla['first_response_at'];
            }
            if (isset($sla['first_response_status'])) {
                $updates['first_response_sla_status'] = $sla['first_response_status'];
            }
            
            // Sync resolution tracking
            $resolution = $sla['resolution'] ?? null;
            if ($resolution) {
                if (isset($resolution['resolved_at'])) {
                    $updates['resolved_time'] = $resolution['resolved_at'];
                }
                if (isset($resolution['status'])) {
                    $updates['resolution_sla_status'] = $resolution['status'];
                }
            }
            
            // Also handle flat resolution status field
            if (isset($sla['resolution_status'])) {
                $updates['resolution_sla_status'] = $sla['resolution_status'];
            }
        }

        // Update local ticket
        $ticket->update($updates);

        // Send notification to ticket owner
        if ($ticket->createdByUser) {
            try {
                $ticket->createdByUser->notify(
                    new \App\Notifications\TicketStatusUpdatedNotification(
                        $ticket,
                        $ticket->getOriginal('complaint_workflow'),
                        $data['status']['workflow_value']
                    )
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send status notification', [
                    'ticket_id' => $ticket->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Ticket status updated via webhook', [
            'ticket_id' => $ticket->id,
            'new_status' => $data['status']['name'] ?? 'Unknown',
        ]);

        return true;
    }

    /**
     * Handle priority update
     */
    private function handlePriorityUpdate(Complaint $ticket, array $data): bool
    {
        if (!isset($data['priority']['value'])) {
            return false;
        }

        $ticket->update([
            'priority' => $data['priority']['value'],
            'last_synced_at' => now(),
        ]);

        Log::info('Ticket priority updated via webhook', [
            'ticket_id' => $ticket->id,
            'new_priority' => $data['priority']['value'],
        ]);

        return true;
    }

    /**
     * Handle assignment update
     */
    private function handleAssignmentUpdate(Complaint $ticket, array $data): bool
    {
        if (!isset($data['assigned_to'])) {
            return false;
        }

        $assignedNames = collect($data['assigned_to'])->pluck('name')->implode(', ');
        $syncData = [];

        foreach ($data['assigned_to'] as $devData) {
            $email = $devData['email'] ?? null;
            if (!$email)
                continue;

            $user = \App\User::where('email', $email)->first();
            if ($user) {
                $syncData[$user->id] = [
                    'assigned_date' => $devData['assigned_date'] ?? now(),
                    'assigned_by' => null, // We don't have the creator's ID in this context easily
                    'tat_value' => $devData['tat_value'] ?? null,
                    'tat_unit' => $devData['tat_unit'] ?? null,
                ];
            }
        }

        if (!empty($syncData)) {
            $ticket->assignedDevelopers()->sync($syncData);
        }

        $ticket->update([
            'assigned_to' => $assignedNames,
            'last_synced_at' => now(),
        ]);

        Log::info('Ticket assignment updated via webhook', [
            'ticket_id' => $ticket->id,
            'assigned_to' => $assignedNames,
            'sync_count' => count($syncData),
        ]);

        return true;
    }

    /**
     * Handle chat message from developer
     */
    private function handleChatMessage(Complaint $ticket, array $data): bool
    {
        $message = $data['message'] ?? null;
        $externalId = $data['message_id'] ?? null;

        if (!$message && empty($data['attachments'])) {
            return false;
        }

        if (!$externalId) {
            return false;
        }

        // Check for duplicate message (Idempotency)
        $existing = TicketChat::where('ticket_id', $ticket->id)
            ->where('external_id', $externalId)
            ->first();

        if ($existing) {
            return true;
        }

        // Create local chat message
        $chatMessage = TicketChat::create([
            'ticket_id' => $ticket->id,
            'user_id' => null, // Message from external developer
            'external_id' => $externalId,
            'message' => $message ?? '',
        ]);

        // Process attachments
        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as $attachmentData) {
                try {
                    $fileUrl = $attachmentData['file_url'] ?? null;
                    if (!$fileUrl) {
                        continue;
                    }

                    // Download the file
                    $fileContent = file_get_contents($fileUrl);
                    if ($fileContent === false) {
                        Log::error('Failed to download attachment from developer', ['url' => $fileUrl]);
                        continue;
                    }

                    // Save locally
                    $fileName = $attachmentData['file_name'] ?? basename($fileUrl);
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $newFileName = uniqid('chat_', true) . '.' . $extension;
                    $savePath = 'tickets/chat/' . $newFileName;

                    \Storage::disk('public')->put($savePath, $fileContent);
                    $localPath = '/storage/' . $savePath;

                    // Create local record
                    \App\Models\CRM\TicketChatAttachment::create([
                        'chat_message_id' => $chatMessage->id,
                        'file_name' => $fileName,
                        'file_path' => $localPath,
                        'file_type' => $attachmentData['file_type'] ?? 'image',
                        'file_size' => $attachmentData['file_size'] ?? 0,
                        'mime_type' => $attachmentData['mime_type'] ?? 'application/octet-stream',
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error processing incoming attachment from developer', [
                        'error' => $e->getMessage(),
                        'attachment' => $attachmentData,
                    ]);
                }
            }
        }

        Log::info('Chat message created via webhook', [
            'ticket_id' => $ticket->id,
            'external_id' => $externalId,
            'attachments_count' => count($data['attachments'] ?? []),
        ]);

        return true;
    }

    /**
     * Handle ticket archived
     */
    private function handleArchived(Complaint $ticket, array $data): bool
    {
        $isArchived = $data['is_archived'] ?? true;

        $ticket->update([
            'is_archived' => $isArchived,
            'archived_at' => $isArchived ? ($data['archived_at'] ?? now()) : null,
            'last_synced_at' => now(),
        ]);

        if ($isArchived && !$ticket->trashed()) {
            $ticket->delete();
        } elseif (!$isArchived && $ticket->trashed()) {
            $ticket->restore();
        }

        Log::info('Ticket archived status updated via webhook', [
            'ticket_id' => $ticket->id,
            'is_archived' => $isArchived,
            'action' => $isArchived ? 'archived' : 'restored',
        ]);

        return true;
    }

    /**
     * Handle SLA update
     */
    private function handleSlaUpdate(Complaint $ticket, array $data): bool
    {
        if (!isset($data['sla'])) {
            return false;
        }

        $sla = $data['sla'];
        $updates = [
            'last_synced_at' => now(),
        ];

        // Sync SLA level/status
        if (isset($sla['level'])) {
            $updates['sla_level'] = $sla['level'];
        }

        // Sync first response tracking
        $firstResponse = $sla['first_response'] ?? null;
        if ($firstResponse) {
            if (isset($firstResponse['responded_at'])) {
                $updates['first_response_at'] = $firstResponse['responded_at'];
            }
            if (isset($firstResponse['status'])) {
                $updates['first_response_sla_status'] = $firstResponse['status'];
            }
        }

        // Also handle flat SLA fields if they exist
        if (isset($sla['first_response_at'])) {
            $updates['first_response_at'] = $sla['first_response_at'];
        }
        if (isset($sla['first_response_status'])) {
            $updates['first_response_sla_status'] = $sla['first_response_status'];
        }

        // Sync resolution tracking
        $resolution = $sla['resolution'] ?? null;
        if ($resolution) {
            if (isset($resolution['resolved_at'])) {
                $updates['resolved_time'] = $resolution['resolved_at'];
            }
            if (isset($resolution['status'])) {
                $updates['resolution_sla_status'] = $resolution['status'];
            }
        }

        // Also handle flat resolution status field
        if (isset($sla['resolution_status'])) {
            $updates['resolution_sla_status'] = $sla['resolution_status'];
        }

        $ticket->update($updates);

        Log::info('Ticket SLA updated via webhook', [
            'ticket_id' => $ticket->id,
            'sla_level' => $sla['level'] ?? null,
            'first_response_at' => $updates['first_response_at'] ?? null,
            'first_response_sla_status' => $updates['first_response_sla_status'] ?? null,
            'resolution_sla_status' => $updates['resolution_sla_status'] ?? null,
        ]);

        return true;
    }
}
