<?php

namespace App\Livewire\Directorate;

use App\Directorate;
use App\Lab;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class DirectorateManager extends Component
{
    public array $directorateForm = [
        'name' => '',
        'code' => '',
        'head_id' => '',
        'active' => true,
    ];

    public array $labForm = [
        'directorate_id' => '',
        'name' => '',
        'code' => '',
        'manager_id' => '',
        'analyst_ids' => [],
        'phone1' => '',
        'email' => '',
        'start_sample_no' => '',
        'active' => true,
    ];

    public string $directorateSearch = '';
    public string $labSearch = '';
    public string $analystSearch = '';
    public string $statusFilter = '1';
    public ?int $selectedDirectorateId = null;
    public bool $showDirectorateModal = false;
    public bool $showLabModal = false;
    public bool $showDeleteModal = false;
    public bool $showAnalystDropdown = false;
    public ?int $editingDirectorateId = null;
    public ?int $editingLabId = null;
    public ?int $deleteId = null;
    public string $deleteType = '';
    public array $deleteDetails = [];
    public string $message = '';
    public string $messageType = '';

    public function mount(): void
    {
        $this->selectedDirectorateId = Directorate::query()->orderBy('name')->value('id');
    }

    public function getUsersProperty(): Collection
    {
        return User::query()
            ->where('company_id', getUserCompany())
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function getDirectoratesProperty(): Collection
    {
        $query = Directorate::query()->with(['head', 'labs']);

        if ($this->directorateSearch !== '') {
            $query->where(function ($builder) {
                $builder->where('name', 'like', '%' . $this->directorateSearch . '%')
                    ->orWhere('code', 'like', '%' . $this->directorateSearch . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('active', (int) $this->statusFilter);
        }

        return $query->orderBy('name')->get();
    }

    public function getSelectedDirectorateProperty(): ?Directorate
    {
        if (!$this->selectedDirectorateId) {
            return null;
        }

        return Directorate::query()
            ->with(['head', 'labs.manager'])
            ->find($this->selectedDirectorateId);
    }

    public function getLabsProperty(): Collection
    {
        if (!$this->selectedDirectorateId) {
            return collect();
        }

        $query = Lab::query()
            ->with('manager')
            ->where('directorate_id', $this->selectedDirectorateId);

        if ($this->labSearch !== '') {
            $query->where(function ($builder) {
                $builder->where('name', 'like', '%' . $this->labSearch . '%')
                    ->orWhere('code', 'like', '%' . $this->labSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->labSearch . '%')
                    ->orWhere('phone1', 'like', '%' . $this->labSearch . '%');
            });
        }

        return $query->orderBy('name')->get();
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

    public function selectDirectorate(int $directorateId): void
    {
        $this->selectedDirectorateId = $directorateId;
        $this->labSearch = '';
    }

    public function showCreateDirectorateModal(): void
    {
        $this->resetDirectorateForm();
        $this->showDirectorateModal = true;
    }

    public function showEditDirectorateModal(int $directorateId): void
    {
        $directorate = Directorate::query()->findOrFail($directorateId);

        $this->directorateForm = [
            'name' => $directorate->name,
            'code' => $directorate->code,
            'head_id' => $directorate->head_id ? (string) $directorate->head_id : '',
            'active' => (bool) $directorate->active,
        ];

        $this->editingDirectorateId = $directorateId;
        $this->showDirectorateModal = true;
    }

    public function saveDirectorate(): void
    {
        $validated = $this->validate([
            'directorateForm.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('directorates', 'name')->ignore($this->editingDirectorateId),
            ],
            'directorateForm.code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('directorates', 'code')->ignore($this->editingDirectorateId),
            ],
            'directorateForm.head_id' => ['nullable', 'integer', 'exists:users,id'],
            'directorateForm.active' => ['boolean'],
        ]);

        $payload = [
            'name' => trim($validated['directorateForm']['name']),
            'code' => trim($validated['directorateForm']['code']),
            'head_id' => $validated['directorateForm']['head_id'] ?: null,
            'active' => !empty($validated['directorateForm']['active']),
        ];

        $directorate = DB::transaction(function () use ($payload) {
            if ($this->editingDirectorateId) {
                $directorate = Directorate::query()->findOrFail($this->editingDirectorateId);
                $directorate->update($payload);

                return $directorate;
            }

            return Directorate::query()->create($payload);
        });

        $this->selectedDirectorateId = $directorate->id;
        $this->message = $this->editingDirectorateId ? 'Directorate updated successfully.' : 'Directorate created successfully.';
        $this->messageType = 'success';
        $this->closeDirectorateModal();
    }

    public function closeDirectorateModal(): void
    {
        $this->showDirectorateModal = false;
        $this->resetDirectorateForm();
    }

    public function showCreateLabModal(): void
    {
        if (!$this->selectedDirectorateId) {
            $this->message = 'Select a directorate before adding labs.';
            $this->messageType = 'error';

            return;
        }

        $this->resetLabForm();
        $this->labForm['directorate_id'] = (string) $this->selectedDirectorateId;
        $this->showLabModal = true;
    }

    public function showEditLabModal(int $labId): void
    {
        $lab = Lab::query()->findOrFail($labId);

        $this->labForm = [
            'directorate_id' => (string) $lab->directorate_id,
            'name' => $lab->name,
            'code' => $lab->code,
            'manager_id' => $lab->manager_id ? (string) $lab->manager_id : '',
            'analyst_ids' => collect($lab->analyst_ids ?? [])->map(fn ($id) => (string) $id)->values()->all(),
            'phone1' => $lab->phone1 ?? '',
            'email' => $lab->email ?? '',
            'start_sample_no' => $lab->start_sample_no ?? '',
            'active' => (bool) $lab->active,
        ];

        $this->editingLabId = $labId;
        $this->selectedDirectorateId = $lab->directorate_id;
        $this->showLabModal = true;
    }

    public function saveLab(): void
    {
        $validated = $this->validate([
            'labForm.directorate_id' => ['required', 'integer', 'exists:directorates,id'],
            'labForm.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('labs', 'name')
                    ->where(fn ($query) => $query->where('directorate_id', $this->labForm['directorate_id']))
                    ->ignore($this->editingLabId),
            ],
            'labForm.code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('labs', 'code')
                    ->where(fn ($query) => $query->where('directorate_id', $this->labForm['directorate_id']))
                    ->ignore($this->editingLabId),
            ],
            'labForm.manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'labForm.analyst_ids' => ['nullable', 'array'],
            'labForm.analyst_ids.*' => ['integer', 'exists:users,id'],
            'labForm.phone1' => ['required', 'string', 'max:100'],
            'labForm.email' => ['required', 'email', 'max:255'],
            'labForm.start_sample_no' => ['nullable', 'string', 'max:100'],
            'labForm.active' => ['boolean'],
        ]);

        $directorateId = (int) $validated['labForm']['directorate_id'];
        $directorate = Directorate::query()->withCount('labs')->findOrFail($directorateId);

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
            'directorate_id' => $directorateId,
            'name' => trim($validated['labForm']['name']),
            'code' => trim($validated['labForm']['code']),
            'manager_id' => $validated['labForm']['manager_id'] ?: null,
            'analyst_ids' => collect($validated['labForm']['analyst_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values()->all(),
            'phone1' => trim($validated['labForm']['phone1']),
            'email' => trim($validated['labForm']['email']),
            'start_sample_no' => trim($validated['labForm']['start_sample_no']) !== ''
                ? trim($validated['labForm']['start_sample_no'])
                : trim($validated['labForm']['code']),
            'active' => !empty($validated['labForm']['active']),
            'company_id' => getUserCompany(),
            'address' => '',
            'location' => '',
            'website' => null,
            'fax' => null,
            'phone2' => null,
            'phone3' => null,
            'is_external' => 0,
        ];

        $lab = DB::transaction(function () use ($payload) {
            if ($this->editingLabId) {
                $lab = Lab::query()->findOrFail($this->editingLabId);
                $lab->update($payload);

                return $lab;
            }

            return Lab::query()->create($payload);
        });

        $this->selectedDirectorateId = $lab->directorate_id;
        $this->message = $this->editingLabId ? 'Lab updated successfully.' : 'Lab created successfully.';
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

    public function toggleAnalystSelection(int $userId): void
    {
        $selected = array_map('intval', $this->labForm['analyst_ids'] ?? []);

        if (in_array($userId, $selected, true)) {
            $selected = array_values(array_filter($selected, fn (int $id): bool => $id !== $userId));
        } else {
            $selected[] = $userId;
            $selected = array_values(array_unique($selected));
        }

        $this->labForm['analyst_ids'] = array_map('strval', $selected);
        $this->analystSearch = '';
        $this->showAnalystDropdown = false;
    }

    public function removeAnalyst(int $userId): void
    {
        $selected = array_map('intval', $this->labForm['analyst_ids'] ?? []);
        $selected = array_values(array_filter($selected, fn (int $id): bool => $id !== $userId));
        $this->labForm['analyst_ids'] = array_map('strval', $selected);
    }

    public function confirmDelete(string $type, int $id): void
    {
        $this->deleteType = $type;
        $this->deleteId = $id;

        if ($type === 'directorate') {
            $directorate = Directorate::query()->withCount('labs')->findOrFail($id);
            $this->deleteDetails = [
                'title' => $directorate->name,
                'subtitle' => $directorate->code,
                'summary' => $directorate->labs_count . ' lab(s) linked',
            ];
        } else {
            $lab = Lab::query()->with('manager')->findOrFail($id);
            $this->deleteDetails = [
                'title' => $lab->name,
                'subtitle' => $lab->code,
                'summary' => $lab->manager?->name ?: 'No lab manager assigned',
            ];
        }

        $this->showDeleteModal = true;
    }

    public function deleteRecord(): void
    {
        if ($this->deleteType === 'directorate') {
            $directorate = Directorate::query()->withCount('labs')->findOrFail($this->deleteId);

            if ($directorate->labs_count > 0) {
                $this->message = 'Delete the labs in this directorate first.';
                $this->messageType = 'error';
                $this->closeDeleteModal();

                return;
            }

            $directorate->delete();

            if ($this->selectedDirectorateId === $directorate->id) {
                $this->selectedDirectorateId = Directorate::query()->orderBy('name')->value('id');
            }

            $this->message = 'Directorate deleted successfully.';
        } else {
            $lab = Lab::query()->findOrFail($this->deleteId);
            $lab->delete();
            $this->message = 'Lab deleted successfully.';
        }

        $this->messageType = 'success';
        $this->closeDeleteModal();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteType = '';
        $this->deleteDetails = [];
    }

    public function clearFilters(): void
    {
        $this->directorateSearch = '';
        $this->labSearch = '';
        $this->statusFilter = '1';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
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

        $names = $this->users
            ->whereIn('id', $analystIds)
            ->pluck('name')
            ->values()
            ->all();

        return $names === [] ? 'No analysts assigned' : implode(', ', $names);
    }

    private function resetDirectorateForm(): void
    {
        $this->directorateForm = [
            'name' => '',
            'code' => '',
            'head_id' => '',
            'active' => true,
        ];
        $this->editingDirectorateId = null;
        $this->resetValidation();
    }

    private function resetLabForm(): void
    {
        $this->labForm = [
            'directorate_id' => $this->selectedDirectorateId ? (string) $this->selectedDirectorateId : '',
            'name' => '',
            'code' => '',
            'manager_id' => '',
            'analyst_ids' => [],
            'phone1' => '',
            'email' => '',
            'start_sample_no' => '',
            'active' => true,
        ];
        $this->analystSearch = '';
        $this->showAnalystDropdown = false;
        $this->editingLabId = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.directorate.directorate-manager');
    }
}
