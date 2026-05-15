<?php

namespace App\Livewire\Equipment\Assets;

use App\Models\Assets\AssetType;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AssetTypeManager extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 25;

    public bool $showModal = false;

    public ?string $editId = null;

    public string $asset_code = '';

    public string $description = '';

    public bool $is_active = true;

    public bool $showDeleteConfirmModal = false;

    public ?string $pendingDeleteAssetTypeId = null;

    /** @var array<string, mixed>|null */
    public ?array $pendingDeleteAssetTypePreview = null;

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openModal(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        $this->resetValidation();
        $this->resetForm();

        $type = AssetType::query()->findOrFail($id);
        $this->editId = $type->id;
        $this->asset_code = (string) $type->asset_code;
        $this->description = (string) $type->descripton;
        $this->is_active = (bool) $type->is_active;

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->asset_code = '';
        $this->description = '';
        $this->is_active = true;
    }

    public function save(): void
    {
        $this->validate($this->validationRules());

        if ($this->editId) {
            $type = AssetType::query()->findOrFail($this->editId);
            $type->update([
                'asset_code' => $this->asset_code,
                'descripton' => $this->description,
                'is_active' => $this->is_active,
            ]);
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => __('equipment.asset_type_updated_successfully'),
            ]);
        } else {
            AssetType::query()->create([
                'asset_code' => $this->asset_code,
                'descripton' => $this->description,
                'is_active' => $this->is_active,
            ]);
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => __('equipment.asset_type_created_successfully'),
            ]);
        }

        $this->closeModal();
    }

    public function openDeleteAssetTypeConfirmModal(string $id): void
    {
        $type = AssetType::query()
            ->withCount([
                'equipments',
                'equipments as active_equipments_count' => function ($query): void {
                    $query->where('active', true);
                },
            ])
            ->findOrFail($id);

        $this->pendingDeleteAssetTypeId = $id;
        $this->pendingDeleteAssetTypePreview = $this->previewFromAssetType($type);
        $this->showDeleteConfirmModal = true;
    }

    public function closeDeleteAssetTypeConfirmModal(): void
    {
        $this->showDeleteConfirmModal = false;
        $this->pendingDeleteAssetTypeId = null;
        $this->pendingDeleteAssetTypePreview = null;
    }

    public function confirmDeleteAssetType(): void
    {
        if ($this->pendingDeleteAssetTypeId === null) {
            return;
        }

        $typeId = $this->pendingDeleteAssetTypeId;

        try {
            $type = AssetType::query()
                ->withCount('equipments')
                ->findOrFail($typeId);

            if ($type->equipments_count > 0) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => __('equipment.asset_type_delete_has_equipment'),
                ]);

                return;
            }

            $type->delete();

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => __('equipment.asset_type_deleted_successfully'),
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => __('equipment.asset_type_delete_failed', ['error' => $e->getMessage()]),
            ]);
        } finally {
            $this->closeDeleteAssetTypeConfirmModal();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function previewFromAssetType(AssetType $type): array
    {
        return [
            'asset_code' => (string) ($type->asset_code ?? ''),
            'description' => (string) ($type->descripton ?? ''),
            'active_equipments_count' => (int) ($type->active_equipments_count ?? 0),
            'equipments_count' => (int) ($type->equipments_count ?? 0),
            'status' => $type->is_active ? __('equipment.active') : __('equipment.inactive'),
            'can_delete' => (int) ($type->equipments_count ?? 0) === 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationRules(): array
    {
        return [
            'asset_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('asset_types', 'asset_code')->ignore($this->editId),
            ],
            'description' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        $query = AssetType::query()->withCount([
            'equipments as active_equipments_count' => function ($query): void {
                $query->where('active', true);
            },
        ]);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search): void {
                $q->where('asset_code', 'like', '%' . $search . '%')
                    ->orWhere('descripton', 'like', '%' . $search . '%');
            });
        }

        return view('livewire.equipment.assets.asset-type-manager', [
            'types' => $query->orderByDesc('created_at')->paginate($this->perPage),
        ]);
    }
}
