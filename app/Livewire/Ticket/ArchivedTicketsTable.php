<?php

namespace App\Livewire\Ticket;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CRM\Complaint;
use App\Models\CRM\TicketCategory;
use App\Models\CRM\TicketPriority;
use App\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

class ArchivedTicketsTable extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';

    #[Url(except: 25)]
    public $perPage = 25;

    // Filters
    #[Url(except: [])]
    public $filters = [
        'category' => '',
        'assigned_to' => '',
        'priority' => '',
    ];

    // Available filter options
    public $categories = [];
    public $priorities = [];
    public $developers = [];

    protected $queryString = [];

    public function mount(): void
    {
        $this->loadFilterOptions();
    }

    /**
     * Load filter dropdown options
     */
    private function loadFilterOptions(): void
    {
        $this->categories = TicketCategory::active()->orderBy('name')->get();
        $this->priorities = TicketPriority::active()->orderBy('order')->orderBy('name')->get();
        $this->developers = User::where('is_support_staff', 1)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get tickets query with all filters applied
     */
    private function getTicketsQuery()
    {
        $user = Auth::user();

        $query = Complaint::onlyTrashed()
            ->with([
                'category',
                'assignedUser',
                'assignedDevelopers',
                'ticketStatus',
                'ticketPriority'
            ]);

        // If user doesn't have view_tickets permission, only show tickets assigned to them
        if ($user->is_support_staff == 1 && !$user->hasTicketPermission('view_tickets')) {
            $query->where(function ($q) use ($user) {
                // Check many-to-many assigned developers relationship
                $q->whereHas('assignedDevelopers', function ($subQuery) use ($user) {
                    $subQuery->where('users.id', $user->id);
                })
                    // Also check legacy assigned_to field
                    ->orWhere('assigned_to', $user->name);
            });
        }

        // Apply filters
        if ($this->filters['category']) {
            $query->where('ticket_category_id', $this->filters['category']);
        }

        if ($this->filters['assigned_to']) {
            if ($this->filters['assigned_to'] === 'me') {
                $query->where('assigned_to', Auth::user()->name);
            } elseif ($this->filters['assigned_to'] === 'unassigned') {
                $query->where(function ($q) {
                    $q->whereNull('assigned_to')->orWhere('assigned_to', '');
                });
            } else {
                $query->where('assigned_to', $this->filters['assigned_to']);
            }
        }

        if ($this->filters['priority']) {
            $query->where('priority', $this->filters['priority']);
        }

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('ticket_no', 'like', "%{$this->search}%")
                    ->orWhere('complaint_id', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhere('raised_by', 'like', "%{$this->search}%")
                    ->orWhere('created_by', 'like', "%{$this->search}%");
            });
        }

        // Order by deleted_at descending (most recently archived first)
        return $query->orderBy('deleted_at', 'desc');
    }

    /**
     * Get paginated tickets
     */
    public function getTicketsProperty()
    {
        $limit = (int) ($this->perPage ?? 25);
        $tickets = $this->getTicketsQuery()->paginate($limit);
        $tickets->setPath(route('tickets.deleted'));
        return $tickets;
    }

    /**
     * Reset filters
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->filters = [
            'category' => '',
            'assigned_to' => '',
            'priority' => '',
        ];
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
     * Update filters - reset page
     */
    public function updatingFilters(): void
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

    /**
     * Check if current user can view archived ticket actions
     */
    public function getIsDeveloperProperty()
    {
        $user = Auth::user();
        // Must be support staff AND have the permission
        return $user->is_support_staff == 1 && $user->hasTicketPermission('view_archived_actions');
    }

    public function render()
    {
        return view('livewire.ticket.archived-tickets-table', [
            'tickets' => $this->tickets,
            'isDeveloper' => $this->isDeveloper,
        ]);
    }
}
