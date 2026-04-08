<?php

namespace App\Livewire\Personnel;

use App\User;
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
    public bool $showChangesModal = false;
    public ?int $selectedAuditId = null;
    /** @var array<int, string> */
    public array $changeColumns = [];
    /** @var array<string, mixed> */
    public array $newValues = [];
    /** @var array<string, mixed> */
    public array $oldValues = [];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
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

    public function getAuditsProperty()
    {
        $query = Audit::query()
            ->leftJoin('users as u', 'u.id', '=', 'audits.user_id')
            ->selectRaw('audits.*, u.name as user_name, u.email as user_email')
            ->whereIn('audits.user_id', User::query()->pluck('id')->toArray())
            ->orderByDesc('audits.created_at');

        if ($this->search !== '') {
            $searchText = '%' . $this->search . '%';
            $query->where(function ($builder) use ($searchText): void {
                $builder->where('u.name', 'like', $searchText)
                    ->orWhere('u.email', 'like', $searchText)
                    ->orWhere('audits.event', 'like', $searchText)
                    ->orWhere('audits.auditable_type', 'like', $searchText)
                    ->orWhere('audits.auditable_id', 'like', $searchText)
                    ->orWhere('audits.ip_address', 'like', $searchText)
                    ->orWhere('audits.url', 'like', $searchText);
            });
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.personnel.audit-log-manager');
    }
}
