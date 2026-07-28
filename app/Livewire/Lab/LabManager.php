<?php

namespace App\Livewire\Lab;

use App\Lab;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LabManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $labForm = [
        'name' => '',
        'code' => '',
        'start_sample_no' => '',
        'address' => '',
        'location' => '',
        'website' => '',
        'fax' => '',
        'email' => '',
        'phone1' => '',
        'phone2' => '',
        'phone3' => '',
        'is_external' => false,
        'is_default' => false,
        'active' => true,
    ];

    public $search = '';

    public string $statusFilter = '1';

    public string $activeTab = 'internal';

    public $showLabModal = false;

    public $showDeleteModal = false;

    public $deleteId = null;

    public $deleteDetails = [];

    public $editingLab = null;

    public $message = '';

    public $messageType = '';

    public $perPage = 10;

    public $perPageOptions = [10, 25, 50, 100];

    public function mount(): void
    {
        //
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['internal', 'external'], true) ? $tab : 'internal';
        $this->resetPage();
    }

    public function getLabsProperty()
    {
        $query = Lab::query()
            ->where('is_external', $this->activeTab === 'external' ? 1 : 0);

        if (! empty($this->search)) {
            $this->applyCaseInsensitiveSearch($query, ['name', 'code', 'email'], (string) $this->search);
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter);
        }

        return $query->orderBy('name')->paginate($this->perPage);
    }

    public function getTabCountsProperty(): array
    {
        $base = Lab::query();

        if (! empty($this->search)) {
            $this->applyCaseInsensitiveSearch($base, ['name', 'code', 'email'], (string) $this->search);
        }

        if ($this->statusFilter !== '') {
            $base->where('active', $this->statusFilter);
        }

        return [
            'internal' => (clone $base)->where('is_external', 0)->count(),
            'external' => (clone $base)->where('is_external', 1)->count(),
        ];
    }

    protected function authorizeLabEdit(): void
    {
        abort_unless(Auth::user()?->can('laboratory.components.labs.edit') ?? false, 403);
    }

    public function showCreateLabModal(): void
    {
        $this->authorizeLabEdit();
        $this->resetLabForm();
        $this->labForm['is_external'] = $this->activeTab === 'external';
        $this->editingLab = null;
        $this->showLabModal = true;
    }

    public function showEditLabModal($id): void
    {
        $this->authorizeLabEdit();

        $lab = Lab::findOrFail($id);

        $this->labForm = [
            'name' => $lab->name,
            'code' => $lab->code,
            'start_sample_no' => $lab->start_sample_no,
            'address' => $lab->address,
            'location' => $lab->location,
            'website' => $lab->website,
            'fax' => $lab->fax,
            'email' => $lab->email,
            'phone1' => $lab->phone1,
            'phone2' => $lab->phone2,
            'phone3' => $lab->phone3,
            'is_external' => (bool) $lab->is_external,
            'is_default' => (bool) ($lab->is_default ?? false),
            'active' => (bool) $lab->active,
        ];

        $this->editingLab = $id;
        $this->showLabModal = true;
    }

    public function closeLabModal(): void
    {
        $this->showLabModal = false;
        $this->resetLabForm();
    }

    public function resetLabForm(): void
    {
        $this->labForm = [
            'name' => '',
            'code' => '',
            'start_sample_no' => '',
            'address' => '',
            'location' => '',
            'website' => '',
            'fax' => '',
            'email' => '',
            'phone1' => '',
            'phone2' => '',
            'phone3' => '',
            'is_external' => false,
            'is_default' => false,
            'active' => true,
        ];
        $this->editingLab = null;
        $this->resetValidation();
    }

    public function saveLab(): void
    {
        $this->authorizeLabEdit();

        $this->validate([
            'labForm.name' => 'required|string|max:255',
            'labForm.code' => 'required|string|max:255',
            'labForm.start_sample_no' => 'required|string|max:255',
            'labForm.address' => 'required|string',
            'labForm.location' => 'required|string|max:255',
            'labForm.email' => 'required|email|max:255',
            'labForm.phone1' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->labForm['name'],
                'code' => $this->labForm['code'],
                'start_sample_no' => $this->labForm['start_sample_no'],
                'address' => $this->labForm['address'],
                'location' => $this->labForm['location'],
                'website' => $this->labForm['website'],
                'fax' => $this->labForm['fax'],
                'email' => $this->labForm['email'],
                'phone1' => $this->labForm['phone1'],
                'phone2' => $this->labForm['phone2'],
                'phone3' => $this->labForm['phone3'],
                'is_external' => $this->labForm['is_external'] ? 1 : 0,
                'is_default' => ! empty($this->labForm['is_default']) ? 1 : 0,
                'active' => $this->labForm['active'] ? 1 : 0,
                'company_id' => getUserCompany(),
            ];

            if ($this->editingLab) {
                $lab = Lab::findOrFail($this->editingLab);
                $lab->update($data);
                $savedLabId = (string) $lab->id;
                $this->message = 'Lab updated successfully!';
            } else {
                $lab = Lab::create($data);
                $savedLabId = (string) $lab->id;
                $this->message = 'Lab created successfully!';
            }

            if (! empty($data['is_default'])) {
                Lab::synchronizeDefaultFlag($savedLabId);
            } elseif ($this->editingLab) {
                // Explicitly cleared: ensure this lab is not left as default.
                Lab::query()->whereKey($savedLabId)->update(['is_default' => false]);
            }

            DB::commit();

            $this->activeTab = ! empty($data['is_external']) ? 'external' : 'internal';
            $this->resetPage();
            $this->messageType = 'success';
            $this->closeLabModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error saving lab: '.$e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function confirmDeleteLab($id): void
    {
        $this->authorizeLabEdit();

        $lab = Lab::findOrFail($id);

        $this->deleteId = $id;
        $this->deleteDetails = [
            'name' => $lab->name,
            'code' => $lab->code,
            'start_sample_no' => $lab->start_sample_no,
            'address' => $lab->address,
            'email' => $lab->email,
            'phone1' => $lab->phone1,
            'is_external' => $lab->is_external ? 'External' : 'Internal',
            'active' => $lab->active ? 'Active' : 'Inactive',
        ];

        $this->showDeleteModal = true;
    }

    public function deleteLab(): void
    {
        $this->authorizeLabEdit();

        try {
            Lab::findOrFail($this->deleteId)->delete();
            $this->message = 'Lab deleted successfully!';
            $this->messageType = 'success';
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->message = 'Error deleting lab: '.$e->getMessage();
            $this->messageType = 'error';
            $this->closeDeleteModal();
        }
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteDetails = [];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function updatingActiveTab(): void
    {
        $this->resetPage();
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.lab.lab-manager');
    }
}
