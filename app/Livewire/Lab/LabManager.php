<?php

namespace App\Livewire\Lab;

use App\Lab;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LabManager extends Component
{
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
        'active' => true,
    ];

    public $search = '';

    public string $statusFilter = '1';

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

    public function getLabsProperty()
    {
        $query = Lab::query();

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter);
        }

        return $query->orderBy('name')->paginate($this->perPage);
    }

    protected function authorizeLabEdit(): void
    {
        abort_unless(Auth::user()?->can('laboratory.components.labs.edit') ?? false, 403);
    }

    public function showCreateLabModal(): void
    {
        $this->authorizeLabEdit();
        $this->resetLabForm();
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
                'active' => $this->labForm['active'] ? 1 : 0,
                'company_id' => getUserCompany(),
            ];

            if ($this->editingLab) {
                Lab::findOrFail($this->editingLab)->update($data);
                $this->message = 'Lab updated successfully!';
            } else {
                Lab::create($data);
                $this->message = 'Lab created successfully!';
            }

            DB::commit();

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
