<?php

namespace App\Livewire\Ticket;

use App\Models\CRM\Complaint;
use App\Services\DeveloperSyncService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class SyncStatusIndicator extends Component
{
    public $ticketId;
    public $ticket;
    public $syncStatus;
    public $lastSyncedAt;
    public $syncError;
    public $retrying = false;

    protected $listeners = ['refreshSyncStatus' => '$refresh'];

    public function mount($ticketId)
    {
        $this->ticketId = $ticketId;
        $this->loadTicket();
    }

    public function loadTicket()
    {
        $this->ticket = Complaint::find($this->ticketId);

        if ($this->ticket) {
            $this->syncStatus = $this->getSyncStatus();
            $this->lastSyncedAt = $this->ticket->last_synced_at;
            $this->syncError = $this->ticket->sync_error;
        }
    }

    public function getSyncStatus()
    {
        if (!$this->ticket->developer_ticket_id) {
            return 'not_synced';
        }

        if ($this->ticket->sync_failed) {
            return 'failed';
        }

        if ($this->ticket->last_synced_at && $this->ticket->updated_at > $this->ticket->last_synced_at) {
            return 'pending';
        }

        return 'synced';
    }

    public function retrySync()
    {
        if (!config('developer_sync.enabled')) {
            session()->flash('error', 'Sync is disabled in configuration.');
            return;
        }

        $this->retrying = true;

        try {
            $syncService = app(DeveloperSyncService::class);

            if (!$this->ticket->developer_ticket_id) {
                // Initial sync
                $result = $syncService->syncNewTicket($this->ticket);
            } else {
                // Poll for updates
                $result = $syncService->pollTicketUpdates($this->ticket);
            }

            if ($result) {
                session()->flash('success', 'Ticket synced successfully!');
                $this->loadTicket();
            } else {
                session()->flash('error', 'Sync failed. Please check the logs.');
            }
        } catch (\Exception $e) {
            Log::error('Manual sync retry failed', [
                'ticket_id' => $this->ticketId,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Sync failed: ' . $e->getMessage());
        } finally {
            $this->retrying = false;
        }
    }

    public function render()
    {
        return view('livewire.ticket.sync-status-indicator');
    }
}
