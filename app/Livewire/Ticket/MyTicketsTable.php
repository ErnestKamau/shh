<?php

namespace App\Livewire\Ticket;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CRM\Complaint;
use App\Models\CRM\TicketCategory;
use App\Models\CRM\TicketStatus;
use App\Models\CRM\TicketPriority;
use App\Models\CRM\TicketChat;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

class MyTicketsTable extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';

    #[Url(except: '')]
    public $status = '';

    #[Url(except: '')]
    public $priority = '';

    #[Url(except: 15)]
    public $perPage = 15;

    // Unread message counts
    public $unreadCounts = [];
    public $totalUnread = 0;

    // Available filter options
    public $statuses = [];
    public $priorities = [];

    protected $queryString = [];

    public function mount(): void
    {
        $this->loadFilterOptions();
        $this->loadUnreadCounts();
    }

    /**
     * Load filter dropdown options
     */
    private function loadFilterOptions(): void
    {
        $this->statuses = TicketStatus::active()->orderBy('order')->orderBy('name')->get();
        $this->priorities = TicketPriority::active()->orderBy('order')->orderBy('name')->get();
    }

    /**
     * Load unread message counts
     */
    private function loadUnreadCounts(): void
    {
        $user = Auth::user();

        // Get all user ticket IDs
        $ticketIds = Complaint::forUser($user->id)->pluck('id')->toArray();

        if (empty($ticketIds)) {
            $this->unreadCounts = [];
            $this->totalUnread = 0;
            return;
        }

        // Calculate unread message counts for each ticket (only from developers/support staff)
        $this->unreadCounts = TicketChat::whereIn('ticket_id', $ticketIds)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->whereHas('user', function ($query) {
                $query->where('is_support_staff', 1);
            })
            ->selectRaw('ticket_id, COUNT(*) as unread_count')
            ->groupBy('ticket_id')
            ->pluck('unread_count', 'ticket_id')
            ->toArray();

        // Calculate total unread messages for sidebar badge (only from developers)
        $this->totalUnread = TicketChat::whereIn('ticket_id', $ticketIds)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->whereHas('user', function ($query) {
                $query->where('is_support_staff', 1);
            })
            ->count();
    }

    /**
     * Get tickets query with all filters applied
     */
    private function getTicketsQuery()
    {
        $user = Auth::user();

        $query = Complaint::forUser($user->id)
            ->with(['category', 'assignedUser', 'assignedDevelopers', 'ticketStatus', 'ticketPriority', 'chat']);

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('ticket_no', 'like', "%{$this->search}%")
                    ->orWhere('complaint_id', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->status) {
            $query->where('complaint_workflow', $this->status);
        }

        if ($this->priority) {
            $query->where('priority', $this->priority);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get paginated tickets
     */
    public function getTicketsProperty()
    {
        $limit = (int) ($this->perPage ?? 15);
        $tickets = $this->getTicketsQuery()->paginate($limit);
        $tickets->setPath(route('tickets.index'));

        // Reload unread counts after pagination
        $this->loadUnreadCounts();

        return $tickets;
    }

    /**
     * Reset filters
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->priority = '';
        $this->resetPage();
    }

    /**
     * Update search - reset page
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Update status filter - reset page
     */
    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Update priority filter - reset page
     */
    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    /**
     * Update per page - reset page
     */
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.ticket.my-tickets-table', [
            'tickets' => $this->tickets,
        ]);
    }
}

