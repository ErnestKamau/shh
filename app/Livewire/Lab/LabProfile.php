<?php

namespace App\Livewire\Lab;

use App\Lab;
use App\LabDecontaminationArea;
use App\LabSection;
use App\Livewire\Lab\Concerns\InteractsWithLabSections;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class LabProfile extends Component
{
    use InteractsWithLabSections;

    public string $labId;

    public Lab $lab;

    public string $message = '';

    public string $messageType = '';

    public bool $showDeleteModal = false;

    public ?string $deleteId = null;

    public array $deleteDetails = [];

    public string $deleteType = 'lab_section';

    public array $decontaminationForm = [
        'name' => '',
        'lab_section_id' => '',
        'active' => true,
    ];

    public bool $showDecontaminationModal = false;

    public ?string $editingDecontaminationAreaId = null;

    public string $activeProfileTab = 'sections';

    public string $sectionSearch = '';

    public function mount(string $labId): void
    {
        $this->requiresExpandedLabForSections = false;
        $this->labId = $labId;
        $this->loadLab();
        $this->activeLabSectionLabId = $labId;
        $this->initializeLabSectionForm($labId);
    }

    public function loadLab(): void
    {
        $this->lab = Lab::query()
            ->with([
                'zone',
                'directorate',
                'labSections' => function ($query): void {
                    $query->with(['equipment', 'reportingUnit'])->orderBy('name');
                },
                'decontaminationAreas' => function ($query): void {
                    $query->with('labSection')->orderBy('name');
                },
            ])
            ->findOrFail($this->labId);
    }

    protected function afterLabSectionMutated(string $labId): void
    {
        if ($labId === $this->labId) {
            $this->loadLab();
        }
    }

    public function showCreateDecontaminationModal(): void
    {
        $this->authorizeLabEdit();
        $this->editingDecontaminationAreaId = null;
        $this->decontaminationForm = [
            'name' => '',
            'lab_section_id' => '',
            'active' => true,
        ];
        $this->resetValidation();
        $this->showDecontaminationModal = true;
    }

    public function showEditDecontaminationModal(string $areaId): void
    {
        $this->authorizeLabEdit();

        $area = LabDecontaminationArea::query()
            ->where('lab_id', $this->labId)
            ->findOrFail($areaId);

        $this->editingDecontaminationAreaId = $areaId;
        $this->decontaminationForm = [
            'name' => $area->name,
            'lab_section_id' => $area->lab_section_id,
            'active' => (bool) $area->active,
        ];
        $this->resetValidation();
        $this->showDecontaminationModal = true;
    }

    public function closeDecontaminationModal(): void
    {
        $this->showDecontaminationModal = false;
        $this->editingDecontaminationAreaId = null;
        $this->resetValidation();
    }

    public function saveDecontaminationArea(): void
    {
        $this->authorizeLabEdit();

        $this->validate([
            'decontaminationForm.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lab_decontamination_areas', 'name')
                    ->where(fn ($query) => $query->where('lab_id', $this->labId))
                    ->ignore($this->editingDecontaminationAreaId),
            ],
            'decontaminationForm.lab_section_id' => [
                'required',
                'uuid',
                Rule::exists('lab_sections', 'id')->where(fn ($query) => $query->where('lab_id', $this->labId)),
            ],
            'decontaminationForm.active' => ['boolean'],
        ]);

        $payload = [
            'lab_id' => $this->labId,
            'lab_section_id' => $this->decontaminationForm['lab_section_id'],
            'name' => trim($this->decontaminationForm['name']),
            'active' => (bool) ($this->decontaminationForm['active'] ?? true),
            'company_id' => getUserCompany(),
        ];

        try {
            if ($this->editingDecontaminationAreaId) {
                LabDecontaminationArea::query()
                    ->where('lab_id', $this->labId)
                    ->findOrFail($this->editingDecontaminationAreaId)
                    ->update($payload);
                $this->message = 'Decontamination area updated successfully!';
            } else {
                LabDecontaminationArea::create($payload);
                $this->message = 'Decontamination area created successfully!';
            }

            $this->messageType = 'success';
            $this->closeDecontaminationModal();
            $this->loadLab();
        } catch (\Exception $e) {
            $this->message = 'Error saving decontamination area: '.$e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function confirmDeleteDecontaminationArea(string $areaId): void
    {
        $this->authorizeLabEdit();

        $area = LabDecontaminationArea::query()
            ->with('labSection')
            ->where('lab_id', $this->labId)
            ->findOrFail($areaId);

        $this->deleteType = 'decontamination_area';
        $this->deleteId = $areaId;
        $this->deleteDetails = [
            'name' => $area->name,
            'lab_section' => $area->labSection
                ? $area->labSection->code.' — '.$area->labSection->name
                : '—',
            'active' => $area->active ? 'Active' : 'Inactive',
        ];
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteType = 'lab_section';
        $this->deleteDetails = [];
    }

    public function confirmDelete(): void
    {
        $this->authorizeLabEdit();

        try {
            if ($this->deleteType === 'decontamination_area') {
                LabDecontaminationArea::query()
                    ->where('lab_id', $this->labId)
                    ->findOrFail($this->deleteId)
                    ->delete();
                $this->message = 'Decontamination area deleted successfully!';
            } elseif ($this->deleteType === 'lab_section') {
                $section = LabSection::query()
                    ->where('lab_id', $this->labId)
                    ->findOrFail($this->deleteId);

                if ($section->decontaminationAreas()->exists()) {
                    throw new \RuntimeException('This section still has decontamination areas assigned.');
                }

                $labId = (string) $section->lab_id;
                $section->delete();

                if ($this->editingLabSection === $this->deleteId) {
                    $this->editingLabSection = null;
                    $this->closeLabSectionModal();
                    $this->initializeLabSectionForm($labId);
                }

                $this->message = 'Lab section deleted successfully!';
                $this->afterLabSectionMutated($labId);
            }

            $this->messageType = 'success';
            $this->closeDeleteModal();
            $this->loadLab();
        } catch (\Exception $e) {
            $this->message = 'Error deleting record: '.$e->getMessage();
            $this->messageType = 'error';
            $this->closeDeleteModal();
        }
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function setActiveProfileTab(string $tab): void
    {
        if (! in_array($tab, ['sections', 'decontamination'], true)) {
            return;
        }

        $this->activeProfileTab = $tab;
    }

    public function getFilteredLabSectionsProperty(): Collection
    {
        $search = strtolower(trim($this->sectionSearch));

        return $this->lab->labSections->filter(function ($section) use ($search): bool {
            if ($search === '') {
                return true;
            }

            $haystacks = [
                $section->name,
                $section->code,
                $section->description,
                $section->result_nature,
                $section->equipment?->name,
                $section->reportingUnit?->name,
            ];

            foreach ($haystacks as $value) {
                if ($value !== null && str_contains(strtolower((string) $value), $search)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    public function render()
    {
        return view('livewire.lab.lab-profile');
    }
}
