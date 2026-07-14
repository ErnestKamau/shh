<?php

namespace App\Livewire\Lab;

use App\Directorate;
use App\Lab;
use App\User;
use App\Zone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class LabPageManager extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public array $labForm = [
        'zone_id'        => '',
        'directorate_id' => '',
        'name'           => '',
        'code'           => '',
        'manager_id'     => '',
        'analyst_ids'    => [],
        'phone1'         => '',
        'email'          => '',
        'start_sample_no' => '',
        'active'         => true,
    ];

    public string $search           = '';
    public string $zoneFilter       = '';
    public string $directorateFilter = '';
    public string $statusFilter     = '1';
    public int    $perPage          = 25;
    public array  $perPageOptions   = [10, 25, 50, 100];

    public bool  $showLabModal        = false;
    public bool  $showDeleteModal     = false;
    public bool  $showAnalystDropdown = false;
    public bool  $showViewModal       = false;

    public ?string $editingLabId = null;
    public ?string $deleteId     = null;
    public array $deleteDetails = [];
    public array $viewLabData   = [];

    public string $analystSearch = '';
    public string $message       = '';
    public string $messageType   = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedZoneFilter(): void
    {
        $this->directorateFilter = '';
        $this->resetPage();
    }

    public function getZonesProperty(): Collection
    {
        $location = getCurrentUserLocation();
        if (!$location) {
            return collect();
        }

        return Zone::query()
            ->where('inventory_location_id', $location->id)
            ->orderBy('key')
            ->get(['id', 'key', 'value']);
    }

    public function getDirectoratesForFilterProperty(): Collection
    {
        $query = Directorate::query()->where('active', 1);

        if ($this->zoneFilter !== '') {
            $query->where('zone_id', $this->zoneFilter);
        }

        return $query->orderBy('name')->get(['id', 'name']);
    }

    public function getDirectoratesForLabProperty(): Collection
    {
        $query = Directorate::query()->with('labs')->where('active', 1);

        if ($this->labForm['zone_id'] !== '') {
            $query->where('zone_id', $this->labForm['zone_id']);
        }

        return $query->orderBy('name')->get();
    }

    public function getUsersProperty(): Collection
    {
        return User::query()
            ->where('company_id', getUserCompany())
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function getFilteredAnalystsProperty(): Collection
    {
        return $this->users->filter(function ($user) {
            if ($this->analystSearch === '') {
                return true;
            }

            return stripos($user->name, $this->analystSearch) !== false
                || stripos((string) $user->email, $this->analystSearch) !== false;
        })->values();
    }

    public function getAllLabsProperty()
    {
        $query = Lab::query()->with(['zone', 'directorate', 'manager']);

        if ($this->search !== '') {
            $query->where(function ($builder) {
                $builder->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone1', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->zoneFilter !== '') {
            $query->where('zone_id', $this->zoneFilter);
        }

        if ($this->directorateFilter !== '') {
            $query->where('directorate_id', $this->directorateFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('active', (int) $this->statusFilter);
        }

        return $query->orderBy('name')->paginate($this->perPage);
    }

    public function updatedLabFormZoneId(): void
    {
        $this->labForm['directorate_id'] = '';
        $this->resetValidation('labForm.directorate_id');
    }

    public function viewLab(string $labId): void
    {
        $lab = Lab::query()
            ->with(['zone', 'directorate', 'manager'])
            ->findOrFail($labId);

        $analystIds = $lab->analyst_ids ?? [];
        $analysts   = $this->users->whereIn('id', $analystIds)->values();

        $this->viewLabData = [
            'id'              => $lab->id,
            'name'            => $lab->name,
            'code'            => $lab->code,
            'zone'            => $lab->zone?->key ?? '—',
            'directorate'     => $lab->directorate?->name ?? '—',
            'phone1'          => $lab->phone1 ?? '—',
            'email'           => $lab->email ?? '—',
            'start_sample_no' => $lab->start_sample_no ?: $lab->code,
            'active'          => (bool) $lab->active,
            'manager'         => $lab->manager ? [
                'name'  => $lab->manager->name,
                'email' => $lab->manager->email ?? '—',
            ] : null,
            'analysts' => $analysts->map(fn ($u) => [
                'name'  => $u->name,
                'email' => $u->email ?? '—',
            ])->all(),
        ];

        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewLabData   = [];
    }

    public function showCreateLabModal(): void
    {
        $this->resetLabForm();
        $this->showLabModal = true;
    }

    public function showEditLabModal(string $labId): void
    {
        $lab = Lab::query()->findOrFail($labId);

        $this->labForm = [
            'zone_id'        => $lab->zone_id ? (string) $lab->zone_id : '',
            'directorate_id' => (string) $lab->directorate_id,
            'name'           => $lab->name,
            'code'           => $lab->code,
            'manager_id'     => $lab->manager_id ? (string) $lab->manager_id : '',
            'analyst_ids'    => collect($lab->analyst_ids ?? [])->map(fn ($id) => (string) $id)->values()->all(),
            'phone1'         => $lab->phone1 ?? '',
            'email'          => $lab->email ?? '',
            'start_sample_no' => $lab->start_sample_no ?? '',
            'active'         => (bool) $lab->active,
        ];

        $this->editingLabId = $labId;
        $this->showLabModal = true;
    }

    public function saveLab(): void
    {
        $validated = $this->validate([
            'labForm.zone_id'        => ['nullable', 'uuid', 'exists:zones,id'],
            'labForm.directorate_id' => ['required', 'uuid', 'exists:directorates,id'],
            'labForm.name'           => [
                'required', 'string', 'max:255',
                Rule::unique('labs', 'name')
                    ->where(fn ($q) => $q->where('directorate_id', $this->labForm['directorate_id']))
                    ->ignore($this->editingLabId),
            ],
            'labForm.code'           => [
                'required', 'string', 'max:100',
                Rule::unique('labs', 'code')
                    ->where(fn ($q) => $q->where('directorate_id', $this->labForm['directorate_id']))
                    ->ignore($this->editingLabId),
            ],
            'labForm.manager_id'     => ['nullable', 'uuid', 'exists:users,id'],
            'labForm.analyst_ids'    => ['nullable', 'array'],
            'labForm.analyst_ids.*'  => ['uuid', 'exists:users,id'],
            'labForm.phone1'         => ['required', 'string', 'max:100'],
            'labForm.email'          => ['required', 'email', 'max:255'],
            'labForm.start_sample_no' => ['nullable', 'string', 'max:100'],
            'labForm.active'         => ['boolean'],
        ]);

        $directorateId = $validated['labForm']['directorate_id'];
        $directorate   = Directorate::query()->withCount('labs')->findOrFail($directorateId);

        if (!$this->editingLabId && $directorate->labs_count >= 7) {
            $this->addError('labForm.directorate_id', 'Each directorate can only have seven labs.');
            return;
        }

        if ($this->editingLabId) {
            $currentLab = Lab::query()->findOrFail($this->editingLabId);
            if ($currentLab->directorate_id !== $directorateId) {
                $targetCount = Lab::query()->where('directorate_id', $directorateId)->count();
                if ($targetCount >= 7) {
                    $this->addError('labForm.directorate_id', 'The selected directorate already has seven labs.');
                    return;
                }
            }
        }

        $payload = [
            'zone_id'        => $this->labForm['zone_id'] !== '' ? $this->labForm['zone_id'] : null,
            'directorate_id' => $directorateId,
            'name'           => trim($validated['labForm']['name']),
            'code'           => trim($validated['labForm']['code']),
            'manager_id'     => $validated['labForm']['manager_id'] ?: null,
            'analyst_ids'    => collect($validated['labForm']['analyst_ids'] ?? [])->map(fn ($id) => (string) $id)->unique()->values()->all(),
            'phone1'         => trim($validated['labForm']['phone1']),
            'email'          => trim($validated['labForm']['email']),
            'start_sample_no' => trim($validated['labForm']['start_sample_no']) !== ''
                ? trim($validated['labForm']['start_sample_no'])
                : trim($validated['labForm']['code']),
            'active'         => !empty($validated['labForm']['active']),
            'company_id'     => getUserCompany(),
            'address'        => '',
            'location'       => '',
            'website'        => null,
            'fax'            => null,
            'phone2'         => null,
            'phone3'         => null,
            'is_external'    => 0,
        ];

        DB::transaction(function () use ($payload) {
            if ($this->editingLabId) {
                Lab::query()->findOrFail($this->editingLabId)->update($payload);
            } else {
                Lab::query()->create($payload);
            }
        });

        $this->message     = $this->editingLabId ? 'Lab updated successfully.' : 'Lab created successfully.';
        $this->messageType = 'success';
        $this->closeLabModal();
    }

    public function closeLabModal(): void
    {
        $this->showLabModal = false;
        $this->resetLabForm();
    }

    public function searchAnalysts(): void
    {
        $this->showAnalystDropdown = true;
    }

    public function toggleAnalystSelection(string $userId): void
    {
        $selected = array_map('strval', $this->labForm['analyst_ids'] ?? []);

        if (in_array($userId, $selected, true)) {
            $selected = array_values(array_filter($selected, fn (string $id): bool => $id !== $userId));
        } else {
            $selected[] = $userId;
            $selected   = array_values(array_unique($selected));
        }

        $this->labForm['analyst_ids'] = $selected;
        $this->analystSearch          = '';
        $this->showAnalystDropdown    = false;
    }

    public function removeAnalyst(string $userId): void
    {
        $selected = array_map('strval', $this->labForm['analyst_ids'] ?? []);
        $selected = array_values(array_filter($selected, fn (string $id): bool => $id !== $userId));
        $this->labForm['analyst_ids'] = $selected;
    }

    public function confirmDelete(string $id): void
    {
        $lab = Lab::query()->with('manager')->findOrFail($id);

        $this->deleteId      = $id;
        $this->deleteDetails = [
            'title'    => $lab->name,
            'subtitle' => $lab->code,
            'summary'  => $lab->manager?->name ?: 'No lab manager assigned',
        ];
        $this->showDeleteModal = true;
    }

    public function deleteRecord(): void
    {
        Lab::query()->findOrFail($this->deleteId)->delete();
        $this->message     = 'Lab deleted successfully.';
        $this->messageType = 'success';
        $this->closeDeleteModal();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId        = null;
        $this->deleteDetails   = [];
    }

    public function clearFilters(): void
    {
        $this->search           = '';
        $this->zoneFilter       = '';
        $this->directorateFilter = '';
        $this->statusFilter     = '1';
        $this->resetPage();
    }

    public function dismissMessage(): void
    {
        $this->message     = '';
        $this->messageType = '';
    }

    public function resetDropdownStates(): void
    {
        $this->showAnalystDropdown = false;
    }

    public function analystNames(array $analystIds): string
    {
        if ($analystIds === []) {
            return 'No analysts assigned';
        }

        $names = $this->users->whereIn('id', $analystIds)->pluck('name')->values()->all();

        return $names === [] ? 'No analysts assigned' : implode(', ', $names);
    }

    private function resetLabForm(): void
    {
        $this->labForm = [
            'zone_id'        => '',
            'directorate_id' => '',
            'name'           => '',
            'code'           => '',
            'manager_id'     => '',
            'analyst_ids'    => [],
            'phone1'         => '',
            'email'          => '',
            'start_sample_no' => '',
            'active'         => true,
        ];
        $this->analystSearch       = '';
        $this->showAnalystDropdown = false;
        $this->editingLabId        = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.lab.lab-page-manager');
    }
}
