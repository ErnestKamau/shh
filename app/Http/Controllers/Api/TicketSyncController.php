<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CRM\Complaint;
use App\Models\CRM\TicketCategory;
use App\Models\CRM\Complaintattachment;
use App\Models\CRM\TicketChangeHistory;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TicketSyncController extends Controller
{
    /**
     * Sync ticket from external system (jasiri.imaralims.com)
     * POST /api/tickets/sync
     */
    public function sync(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'ticket_category_id' => 'required|exists:ticket_categories,id',
                'description' => 'required|string|max:5000',
                'customer' => 'nullable|string|max:255',
                'customer_email' => 'nullable|email|max:255',
                'created_by' => 'nullable|string|max:255',
                'priority' => 'nullable|in:high,medium,low',
                'screenshots' => 'nullable|array|max:3',
                'screenshots.*' => 'nullable|string', // Base64 encoded images
                'files' => 'nullable|array|max:3',
                'files.*' => 'nullable|string', // Base64 encoded files
            ]);

            // Generate unique ticket number
            $ticketNo = $this->generateTicketNumber();
            $complaintId = 'COMP' . str_pad(Complaint::max('id') + 1, 6, '0', STR_PAD_LEFT);

            // Find or create user by email/name
            $user = null;
            if ($request->has('customer_email')) {
                $user = User::where('email', $request->customer_email)->first();
            }
            if (!$user && $request->has('created_by')) {
                $user = User::where('name', $request->created_by)->first();
            }

            // Create ticket
            $ticket = new Complaint();
            $ticket->complaint_id = $complaintId;
            $ticket->ticket_no = $ticketNo;
            $ticket->description = $validated['description'];
            $ticket->priority = $validated['priority'] ?? 'medium';
            $ticket->type = 'Support Ticket';
            $ticket->raised_by = $validated['customer'] ?? ($user->name ?? 'API User');
            $ticket->received_from = $validated['customer'] ?? ($user->name ?? 'API User');
            $ticket->registered_by = $validated['created_by'] ?? ($user->name ?? 'API');
            $ticket->date = now();
            $ticket->complaint_workflow = 1; // Open Complaints
            $ticket->time_created = now();
            $ticket->created_by = $validated['created_by'] ?? ($user->name ?? 'API');
            $ticket->submitted_from = 'jasiri_lims';
            $ticket->ticket_category_id = $validated['ticket_category_id'];
            $ticket->ticket_status = 'Open';
            $ticket->is_closed = false;
            $ticket->save();

            // Handle file uploads (base64)
            if (!empty($validated['screenshots'])) {
                $this->handleBase64Files($validated['screenshots'], $ticket, 'screenshot');
            }

            if (!empty($validated['files'])) {
                $this->handleBase64Files($validated['files'], $ticket, 'document');
            }

            // Log change history
            TicketChangeHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id ?? null,
                'change_type' => 'created',
                'field_name' => 'ticket',
                'old_value' => null,
                'new_value' => 'Ticket created via API sync',
                'created_at' => now(),
            ]);

            // Chain of custody
            $chain = new Chain_of_Custody_Complaint();
            $chain->complaint_id = $ticket->id;
            $chain->action = "Ticket Created via API Sync";
            $chain->action_taker_id = $user->id ?? null;
            $chain->workflow_stage = 1;
            $chain->comments = "Synced from jasiri.imaralims.com";
            $chain->move_out_date = getTodayDate();
            $chain->save();

            Log::info('Ticket synced via API', [
                'ticket_id' => $ticket->id,
                'ticket_no' => $ticketNo,
                'source' => 'jasiri_lims'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket created successfully',
                'data' => [
                    'ticket_id' => $ticket->id,
                    'ticket_no' => $ticketNo,
                    'complaint_id' => $complaintId,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Ticket sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique ticket number
     */
    private function generateTicketNumber(): string
    {
        do {
            $ticketNo = 'TKT' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (Complaint::where('ticket_no', $ticketNo)->exists());

        return $ticketNo;
    }

    /**
     * Handle base64 encoded file uploads
     */
    private function handleBase64Files(array $files, Complaint $ticket, string $type): void
    {
        foreach ($files as $index => $fileData) {
            if (empty($fileData)) {
                continue;
            }

            try {
                // Decode base64
                if (strpos($fileData, ',') !== false) {
                    list($mimeType, $fileData) = explode(',', $fileData, 2);
                } else {
                    $mimeType = 'application/octet-stream';
                }

                $decoded = base64_decode($fileData, true);
                if ($decoded === false) {
                    Log::warning('Failed to decode base64 file', ['index' => $index]);
                    continue;
                }

                // Determine file extension
                $extension = $this->getExtensionFromMime($mimeType);
                if (!$extension) {
                    $extension = $type === 'screenshot' ? 'png' : 'pdf';
                }

                // Generate filename
                $filename = 'ticket_' . $ticket->id . '_' . $type . '_' . time() . '_' . $index . '.' . $extension;
                $path = 'tickets/' . ($type === 'screenshot' ? 'screenshots' : 'documents') . '/' . $filename;

                // Store file
                Storage::disk('public')->put($path, $decoded);

                // Create attachment record
                Complaintattachment::create([
                    'complaint_id' => $ticket->id,
                    'title' => $filename,
                    'type' => $type,
                    'file_path' => Storage::url($path),
                    'posted_by' => $ticket->created_by ?? 'API',
                    'description' => ucfirst($type) . ' for ticket ' . $ticket->ticket_no,
                    'file_type' => $mimeType,
                    'file_size' => strlen($decoded),
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to process base64 file', [
                    'error' => $e->getMessage(),
                    'index' => $index
                ]);
            }
        }
    }

    /**
     * Get file extension from MIME type
     */
    private function getExtensionFromMime(string $mimeType): ?string
    {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'text/plain' => 'txt',
        ];

        return $mimeMap[$mimeType] ?? null;
    }
}
