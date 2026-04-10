<?php

namespace App\Livewire\Ticket;

use Livewire\Component;
use App\Models\CRM\Complaint;
use App\Models\CRM\TicketStatus;
use App\Models\CRM\TicketCategory;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TicketDashboard extends Component
{
    // Date filters
    public $startDate;
    public $endDate;

    public $stats = [];
    public $statusDistribution = [];
    public $ticketTrends = [];
    public $categoryDistribution = [];
    public $trendLabel = '';

    public $recentTickets = []; // Table data
    public $openTickets = [];
    public $inProgressTickets = [];
    public $resolvedTickets = [];

    // Unread counts
    public $unreadCounts = [];
    public $totalUnread = 0;

    protected $listeners = [
        'refreshTicketDashboard' => 'loadDashboardData',
    ];

    public function mount(): void
    {
        // Set default dates (current month)
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->loadDashboardData();

        // Load unread counts for all displayed tickets
        $this->loadUnreadCounts();
    }

    /**
     * Load unread message counts for all displayed tickets
     */
    private function loadUnreadCounts(): void
    {
        $user = Auth::user();

        // Collect all ticket IDs currently shown on dashboard
        $ticketIds = collect()
            ->concat($this->openTickets->pluck('id'))
            ->concat($this->inProgressTickets->pluck('id'))
            ->concat($this->recentTickets->pluck('id'))
            ->unique()
            ->toArray();

        if (empty($ticketIds)) {
            $this->unreadCounts = [];
            $this->totalUnread = 0;
            return;
        }

        // Calculate unread message counts for each ticket (only from developers/support staff)
        $this->unreadCounts = \App\Models\CRM\TicketChat::whereIn('ticket_id', $ticketIds)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->whereHas('user', function ($query) {
                $query->where('is_support_staff', 1);
            })
            ->selectRaw('ticket_id, COUNT(*) as unread_count')
            ->groupBy('ticket_id')
            ->pluck('unread_count', 'ticket_id')
            ->toArray();

        // Total unread for the user across all their tickets
        $allUserTicketIds = Complaint::forUser($user->id)->pluck('id')->toArray();
        $this->totalUnread = \App\Models\CRM\TicketChat::whereIn('ticket_id', $allUserTicketIds)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->whereHas('user', function ($query) {
                $query->where('is_support_staff', 1);
            })
            ->count();
    }

    public function updatedStartDate()
    {
        $this->loadDashboardData();
        $this->dispatch('chartsDataUpdated');
    }

    public function updatedEndDate()
    {
        $this->loadDashboardData();
        $this->dispatch('chartsDataUpdated');
    }

    public function setDateRange($range)
    {
        switch ($range) {
            case 'today':
                $this->startDate = Carbon::today()->format('Y-m-d');
                $this->endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->startDate = Carbon::yesterday()->format('Y-m-d');
                $this->endDate = Carbon::yesterday()->format('Y-m-d');
                break;
            case 'week':
                $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $this->startDate = Carbon::now()->startOfQuarter()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfYear()->format('Y-m-d');
                break;
        }
        $this->loadDashboardData();
        $this->dispatch('chartsDataUpdated');
    }

    /**
     * Get query for client user tickets only (tickets created by the user, not all client tickets)
     */
    private function getClientTicketsQuery()
    {
        $user = Auth::user();

        // For client users, only show tickets they created (by ID or name)
        // Don't include client_id filter to avoid showing other users' tickets
        return Complaint::where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('created_by', $user->name);
        });
    }

    /**
     * Load dashboard statistics
     */
    private function loadDashboardData(): void
    {
        $user = Auth::user();
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        // Set trend label
        $this->trendLabel = $start->format('M d') . ' - ' . $end->format('M d, Y');

        // Use client-specific query (only tickets created by this user)
        $baseQuery = $this->getClientTicketsQuery();

        // Overall statistics (all time, not filtered by date)
        $this->stats = [
            'total' => $this->getClientTicketsQuery()->count(),
            'draft' => $this->getClientTicketsQuery()->where('complaint_workflow', 0)->count(),
            'open' => $this->getClientTicketsQuery()->where('complaint_workflow', 1)->count(),
            'in_progress' => $this->getClientTicketsQuery()->where(function ($q) {
                $q->where('complaint_workflow', 2)->orWhere('complaint_workflow', 3);
            })->count(),
            'resolved' => $this->getClientTicketsQuery()->where('is_closed', true)->count(),
            'pending' => $this->getClientTicketsQuery()->where('complaint_workflow', 4)->count(),
            'categories' => TicketCategory::active()->count(),
            'archived' => Complaint::onlyTrashed()
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('created_by', $user->name);
                })
                ->count(),
        ];

        // Status distribution for chart (filtered by date range)
        $statuses = TicketStatus::active()->orderBy('order')->get();
        $this->statusDistribution = [];
        foreach ($statuses as $status) {
            $count = $this->getClientTicketsQuery()
                ->where('complaint_workflow', $status->workflow_value)
                ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                ->count();
            if ($count > 0) {
                $this->statusDistribution[] = [
                    'label' => $status->name,
                    'value' => $count,
                    'color' => $status->color ?? 'bg-secondary'
                ];
            }
        }

        // Ticket trends (daily)
        $this->ticketTrends = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dayCount = $this->getClientTicketsQuery()
                ->whereDate('created_at', $current->format('Y-m-d'))
                ->count();
            $this->ticketTrends[] = [
                'date' => $current->format('M d'),
                'count' => $dayCount
            ];
            $current->addDay();
        }

        // Category distribution (filtered by date range)
        $categories = TicketCategory::active()->get();
        $this->categoryDistribution = [];
        foreach ($categories as $category) {
            $count = $this->getClientTicketsQuery()
                ->where('ticket_category_id', $category->id)
                ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                ->count();
            if ($count > 0) {
                $this->categoryDistribution[] = [
                    'label' => $category->name,
                    'value' => $count
                ];
            }
        }

        // Recent tickets
        $this->recentTickets = $this->getClientTicketsQuery()
            ->with(['category', 'assignedUser', 'assignedDevelopers', 'ticketStatus', 'ticketPriority'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Open tickets
        $this->openTickets = $this->getClientTicketsQuery()
            ->where('complaint_workflow', 1)
            ->where('is_closed', false)
            ->with(['category', 'ticketStatus', 'ticketPriority'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // In progress tickets
        $this->inProgressTickets = $this->getClientTicketsQuery()
            ->whereIn('complaint_workflow', [2, 3])
            ->where('is_closed', false)
            ->with(['category', 'assignedUser', 'assignedDevelopers', 'ticketStatus', 'ticketPriority'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Resolved tickets
        $this->resolvedTickets = $this->getClientTicketsQuery()
            ->where('is_closed', true)
            ->with(['category', 'ticketStatus', 'ticketPriority'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.ticket.ticket-dashboard');
    }
}

