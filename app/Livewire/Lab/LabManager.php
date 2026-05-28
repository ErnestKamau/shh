<?php

namespace App\Livewire\Lab;

use App\Directorate;
use App\Lab;
use App\LabSection;
use App\Livewire\Lab\Concerns\InteractsWithLabSections;
use App\Zone;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LabManager extends Component
{
    use InteractsWithLabSections;
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

    public $zoneFilter = '';

    public $directorateFilter = '';

    public string $zoneFilterSearch = '';

    public string $directorateFilterSearch = '';

    public bool $showZoneFilterDropdown = false;

    public bool $showDirectorateFilterDropdown = false;

    public $showLabModal = false;

    public $showDeleteModal = false;

    public $deleteId = null;

    public $deleteDetails = [];

    public string $deleteType = 'lab';

    public $editingLab = null;

    public $message = '';

    public $messageType = '';

    public $perPage = 10;

    public $perPageOptions = [10, 25, 50, 100];

    public array $expandedLabs = [];

    public function mount(): void
    {
        //
    }

    public function getLabsProperty()
    {
        $query = Lab::query()->with([
            'zone',
            'directorate',
            'labSections' => function ($q): void {
                $q->with(['equipment', 'reportingUnit'])->orderBy('name');
            },
        ]);

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->zoneFilter !== '') {
            $query->where('zone_id', $this->zoneFilter);
        }

        if ($this->directorateFilter !== '') {
            $query->where('directorate_id', $this->directorateFilter);
        }

        return $query->orderBy('name')->paginate($this->perPage);
    }

    public function toggleLabRow(string $labId): void
    {
        if (in_array($labId, $this->expandedLabs, true)) {
            $this->expandedLabs = array_values(array_filter(
                $this->expandedLabs,
                fn ($id) => $id !== $labId
            ));

            return;
        }

        $this->expandedLabs[] = $labId;
        $this->initializeLabSectionForm($labId);
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

        $this->deleteType = 'lab';
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
            if ($this->deleteType === 'lab_section') {
                $section = LabSection::findOrFail($this->deleteId);
                $labId = (string) $section->lab_id;

                if ($section->decontaminationAreas()->exists()) {
                    throw new \RuntimeException('Remove decontamination areas linked to this section before deleting it.');
                }

                $section->delete();

                if ($this->editingLabSection === $this->deleteId) {
                    $this->editingLabSection = null;
                    $this->closeLabSectionModal();
                    $this->initializeLabSectionForm($labId);
                }

                $this->message = 'Lab section deleted successfully!';
                $this->afterLabSectionMutated($labId);
            } else {
                Lab::findOrFail($this->deleteId)->delete();
                $this->message = 'Lab deleted successfully!';
            }

            $this->messageType = 'success';
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->message = 'Error deleting '.str_replace('_', ' ', $this->deleteType).': '.$e->getMessage();
            $this->messageType = 'error';
            $this->closeDeleteModal();
        }
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteType = 'lab';
        $this->deleteDetails = [];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->zoneFilter = '';
        $this->directorateFilter = '';
        $this->zoneFilterSearch = '';
        $this->directorateFilterSearch = '';
        $this->showZoneFilterDropdown = false;
        $this->showDirectorateFilterDropdown = false;
        $this->resetPage();
    }

    public function selectZoneFilter(string $zoneId): void
    {
        $this->zoneFilter = $zoneId;
        $this->zoneFilterSearch = '';
        $this->showZoneFilterDropdown = false;

        if ($this->directorateFilter !== '' && ! Directorate::whereKey($this->directorateFilter)->where('zone_id', $zoneId)->exists()) {
            $this->directorateFilter = '';
            $this->directorateFilterSearch = '';
        }

        $this->resetPage();
    }

    public function clearZoneFilter(): void
    {
        $this->zoneFilter = '';
        $this->zoneFilterSearch = '';
        $this->showZoneFilterDropdown = false;
        $this->resetPage();
    }

    public function selectDirectorateFilter(string $directorateId): void
    {
        $this->directorateFilter = $directorateId;
        $this->directorateFilterSearch = '';
        $this->showDirectorateFilterDropdown = false;
        $this->resetPage();
    }

    public function clearDirectorateFilter(): void
    {
        $this->directorateFilter = '';
        $this->directorateFilterSearch = '';
        $this->showDirectorateFilterDropdown = false;
        $this->resetPage();
    }

    public function getFilteredZonesProperty()
    {
        $search = trim($this->zoneFilterSearch);

        return Zone::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('key', 'like', '%'.$search.'%')
                        ->orWhere('value', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('key')
            ->limit(50)
            ->get();
    }

    public function getFilteredDirectoratesProperty()
    {
        $search = trim($this->directorateFilterSearch);

        return Directorate::query()
            ->when($this->zoneFilter !== '', function ($query): void {
                $query->where('zone_id', $this->zoneFilter);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    public function getSelectedZoneFilterProperty(): ?Zone
    {
        if ($this->zoneFilter === '') {
            return null;
        }

        return Zone::find($this->zoneFilter);
    }

    public function getSelectedDirectorateFilterProperty(): ?Directorate
    {
        if ($this->directorateFilter === '') {
            return null;
        }

        return Directorate::find($this->directorateFilter);
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

    public function updatingZoneFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDirectorateFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.lab.lab-manager');
    }
}
