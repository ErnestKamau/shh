<?php

namespace App\Livewire\Personnel;

use App\Models\Lab\Qualification;
use Livewire\Component;
use Livewire\WithPagination;

class CertificationManager extends Component
{
    use WithPagination;

    public string $activeTab = 'active';
    public string $search = '';
    public int $perPage = 25;
    /** @var array<int, int> */
    public array $perPageOptions = [10, 25, 50, 100];
    public bool $showCertificationModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingCertificationId = null;
    public string $certificationName = '';
    public string $certificationDescription = '';
    public int $certificationStatus = 0;
    public string $message = '';
    public string $messageType = 'success';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['active', 'archived'], true) ? $tab : 'active';
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->editingCertificationId = null;
        $this->certificationName = '';
        $this->certificationDescription = '';
        $this->certificationStatus = 0;
        $this->showCertificationModal = true;
    }

    public function openEditModal(int $certificationId): void
    {
        $item = Qualification::query()->findOrFail($certificationId);
        $this->editingCertificationId = $item->id;
        $this->certificationName = (string) $item->name;
        $this->certificationDescription = (string) $item->description;
        $this->certificationStatus = (int) $item->status;
        $this->showCertificationModal = true;
    }

    public function closeCertificationModal(): void
    {
        $this->showCertificationModal = false;
    }

    public function saveCertification(): void
    {
        $this->validate([
            'certificationName' => 'required|string|max:255',
            'certificationDescription' => 'required|string|max:1000',
            'certificationStatus' => 'required|integer|in:0,1',
        ]);

        if ($this->editingCertificationId) {
            $item = Qualification::query()->findOrFail($this->editingCertificationId);
            $item->edited_by = (string) auth()->user()->name;
        } else {
            $item = new Qualification();
            $item->module_code = 1;
        }

        $item->name = $this->certificationName;
        $item->description = $this->certificationDescription;
        $item->status = $this->certificationStatus;
        $item->save();

        $this->showCertificationModal = false;
        $this->message = $this->editingCertificationId ? 'Certification updated successfully.' : 'Certification added successfully.';
        $this->messageType = 'success';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function openDeleteModal(int $certificationId): void
    {
        $item = Qualification::query()->findOrFail($certificationId);
        $this->editingCertificationId = $item->id;
        $this->certificationName = (string) $item->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteCertification(): void
    {
        $this->validate([
            'editingCertificationId' => 'required|integer',
        ]);

        $item = Qualification::query()->findOrFail((int) $this->editingCertificationId);
        $item->delete();

        $this->showDeleteModal = false;
        $this->message = 'Certification deleted successfully.';
        $this->messageType = 'success';
        $this->resetPage();
    }

    public function getCertificationsProperty()
    {
        $query = Qualification::query()
            ->where('module_code', 1)
            ->where('status', $this->activeTab === 'active' ? 0 : 1)
            ->orderBy('name');

        if ($this->search !== '') {
            $searchText = '%' . $this->search . '%';
            $query->where(function ($builder) use ($searchText): void {
                $builder->where('name', 'like', $searchText)
                    ->orWhere('description', 'like', $searchText)
                    ->orWhere('edited_by', 'like', $searchText);
            });
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.personnel.certification-manager');
    }
}
