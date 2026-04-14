<?php

namespace App\Livewire\Personnel;

use App\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;
use OwenIt\Auditing\Models\Audit;

class AuditLogManager extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 25;
    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];
    public bool $showAdvancedFilters = false;
    public string $eventFilter = '';
    public string $entityFilter = '';
    public string $ipFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $sortField = 'audits.created_at';
    public string $sortDirection = 'desc';
    public string $userFilter = '';
    /** @var array<int, array{id:int,name:string}> */
    public array $users = [];
    /** @var array<int, string> */
    public array $events = [];
    /** @var array<int, string> */
    public array $entities = [];
    public bool $showChangesModal = false;
    public ?int $selectedAuditId = null;
    /** @var array<int, string> */
    public array $changeColumns = [];
    /** @var array<string, mixed> */
    public array $newValues = [];
    /** @var array<string, mixed> */
    public array $oldValues = [];

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->users = User::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item): array => ['id' => (int) $item->id, 'name' => (string) $item->name])
            ->toArray();

        $this->events = Audit::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->filter()
            ->values()
            ->toArray();

        $this->entities = Audit::query()
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->filter()
            ->values()
            ->toArray();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingUserFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEventFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEntityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingIpFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function toggleAdvancedFilters(): void
    {
        $this->showAdvancedFilters = ! $this->showAdvancedFilters;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->userFilter = '';
        $this->eventFilter = '';
        $this->entityFilter = '';
        $this->ipFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->sortField = 'audits.created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $allowedFields = [
            'u.name',
            'u.email',
            'audits.event',
            'audits.auditable_type',
            'audits.auditable_id',
            'audits.ip_address',
            'audits.url',
            'audits.created_at',
        ];

        if (! in_array($field, $allowedFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function sortIcon(string $field): string
    {
        if ($this->sortField !== $field) {
            return 'mdi mdi-swap-vertical text-muted';
        }

        return $this->sortDirection === 'asc' ? 'mdi mdi-arrow-up' : 'mdi mdi-arrow-down';
    }

    public function openChangesModal(int $auditId): void
    {
        $audit = Audit::query()->findOrFail($auditId);
        $this->selectedAuditId = $audit->id;
        $this->newValues = is_array($audit->new_values) ? $audit->new_values : [];
        $this->oldValues = is_array($audit->old_values) ? $audit->old_values : [];
        $this->changeColumns = array_values(array_unique(array_merge(array_keys($this->newValues), array_keys($this->oldValues))));
        $this->showChangesModal = true;
    }

    public function closeChangesModal(): void
    {
        $this->showChangesModal = false;
    }

    public function getAuditsProperty(): LengthAwarePaginator
    {
        $query = Audit::query()
            ->leftJoin('users as u', 'u.id', '=', 'audits.user_id')
            ->selectRaw('audits.*, u.name as user_name, u.email as user_email');

        if ($this->search !== '') {
            $searchText = '%' . $this->search . '%';
            $query->where(function ($builder) use ($searchText): void {
                $builder->where('u.name', 'like', $searchText)
                    ->orWhere('u.email', 'like', $searchText)
                    ->orWhere('audits.event', 'like', $searchText)
                    ->orWhere('audits.auditable_type', 'like', $searchText)
                    ->orWhere('audits.auditable_id', 'like', $searchText)
                    ->orWhere('audits.ip_address', 'like', $searchText)
                    ->orWhere('audits.url', 'like', $searchText)
                    ->orWhere('audits.user_agent', 'like', $searchText)
                    ->orWhere('audits.tags', 'like', $searchText);
            });
        }

        if ($this->userFilter !== '') {
            $query->where('audits.user_id', (int) $this->userFilter);
        }

        if ($this->eventFilter !== '') {
            $query->where('audits.event', $this->eventFilter);
        }

        if ($this->entityFilter !== '') {
            $query->where('audits.auditable_type', $this->entityFilter);
        }

        if ($this->ipFilter !== '') {
            $query->where('audits.ip_address', 'like', '%' . $this->ipFilter . '%');
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('audits.created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('audits.created_at', '<=', $this->dateTo);
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.personnel.audit-log-manager');
    }
}
