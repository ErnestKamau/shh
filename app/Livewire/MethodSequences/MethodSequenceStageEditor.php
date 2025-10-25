<?php

namespace App\Livewire\MethodSequences;

use App\Models\MethodSequences\MethodSequenceVersion;
use App\Models\MethodSequences\MethodSequenceStage;
use App\Models\Equipments\Equipment;
use App\LabSubCategory;
use App\LabInventoryCategory;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class MethodSequenceStageEditor extends Component
{
    public MethodSequenceVersion $version;
    
    // Stage creation/editing
    public $showStageModal = false;
    public $editingStage = null;
    
    // Form fields
    public $stageName = '';
    public $stageDescription = '';
    public $stageOrder = 1;
    public $selectedEquipments = [];
    public $selectedMedias = [];
    public $selectedControls = [];
    public $isResultStage = false;
    public $failMoveNextStage = false;
    public $duration = '';
    public $safeDuration = '';
    public $isEndStage = false;
    public $isEndStageIfPass = false;

    // Searchable select fields
    public $equipmentSearch = '';
    public $mediaSearch = '';
    public $controlSearch = '';
    public $showEquipmentDropdown = false;
    public $showMediaDropdown = false;
    public $showControlDropdown = false;
    public $filteredEquipments;
    public $filteredMedias;
    public $filteredControls;

    // Messages
    public $message = '';
    public $messageType = '';

    protected function rules(): array
    {
        return [
            'stageName' => 'required|string|max:255',
            'stageDescription' => 'nullable|string',
            'stageOrder' => 'required|integer|min:1',
            'selectedEquipments' => 'nullable|array',
            'selectedMedias' => 'nullable|array',
            'selectedControls' => 'nullable|array',
            'isResultStage' => 'boolean',
            'failMoveNextStage' => 'boolean',
            'duration' => 'nullable|numeric|min:0',
            'safeDuration' => 'nullable|numeric|min:0|lte:duration',
            'isEndStage' => 'boolean',
            'isEndStageIfPass' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'safeDuration.lte' => 'Safe duration must be less than or equal to duration.',
        ];
    }

    public function mount(MethodSequenceVersion $version): void
    {
        $this->version = $version;
    }

    public function render()
    {
        $stages = $this->version->stages()->orderBy('order')->get();
        $equipments = Equipment::orderBy('name')->get();
        
        // Get all lab inventory categories to filter medias and controls
        $categories = LabInventoryCategory::orderBy('name')->get();
        
        // Get media and control lab sub categories
        // You'll need to know the specific category IDs for medias and controls
        // For now, I'll get all lab sub categories grouped by category
        $labSubCategories = LabSubCategory::with('category')
            ->where('active', 1)
            ->orderBy('name')
            ->get()
            ->groupBy('category_id');

        return view('livewire.method-sequences.method-sequence-stage-editor', [
            'stages' => $stages,
            'equipments' => $equipments,
            'categories' => $categories,
            'labSubCategories' => $labSubCategories,
        ]);
    }

    public function showCreateStageModal(): void
    {
        $this->resetForm();
        
        // Set order to next available
        $maxOrder = $this->version->stages()->max('order') ?? 0;
        $this->stageOrder = $maxOrder + 1;
        
        $this->showStageModal = true;
        $this->dispatch('modal-opened');
    }

    public function showEditStageModal(MethodSequenceStage $stage): void
    {
        $this->editingStage = $stage;
        $this->stageName = $stage->name;
        $this->stageDescription = $stage->description;
        $this->stageOrder = $stage->order;
        
        // Convert IDs to name arrays for display
        $this->selectedEquipments = [];
        $this->selectedMedias = [];
        $this->selectedControls = [];
        
        if ($stage->equipment_ids) {
            $equipments = Equipment::whereIn('id', $stage->equipment_ids)->get();
            $this->selectedEquipments = $equipments->map(function($equipment) {
                return ['id' => $equipment->id, 'name' => $equipment->name];
            })->toArray();
        }
        
        if ($stage->media_ids) {
            $medias = LabSubCategory::whereIn('id', $stage->media_ids)->get();
            $this->selectedMedias = $medias->map(function($media) {
                return ['id' => $media->id, 'name' => $media->name];
            })->toArray();
        }
        
        if ($stage->control_ids) {
            $controls = LabSubCategory::whereIn('id', $stage->control_ids)->get();
            $this->selectedControls = $controls->map(function($control) {
                return ['id' => $control->id, 'name' => $control->name];
            })->toArray();
        }
        
        $this->isResultStage = $stage->is_result_stage;
        $this->failMoveNextStage = $stage->fail_move_next_stage;
        $this->duration = $stage->duration;
        $this->safeDuration = $stage->move_to_next_stage_safe_duration;
        $this->isEndStage = $stage->is_end_stage ?? false;
        $this->isEndStageIfPass = $stage->is_end_stage_if_pass ?? false;
        $this->showStageModal = true;
        $this->dispatch('modal-opened');
    }

    public function saveStage(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Extract IDs from selected arrays
            $equipmentIds = collect($this->selectedEquipments)->pluck('id')->toArray();
            $mediaIds = collect($this->selectedMedias)->pluck('id')->toArray();
            $controlIds = collect($this->selectedControls)->pluck('id')->toArray();

            $data = [
                'method_sequence_version_id' => $this->version->id,
                'name' => $this->stageName,
                'description' => $this->stageDescription,
                'order' => $this->stageOrder,
                'equipment_ids' => empty($equipmentIds) ? null : $equipmentIds,
                'media_ids' => empty($mediaIds) ? null : $mediaIds,
                'control_ids' => empty($controlIds) ? null : $controlIds,
                'is_result_stage' => $this->isResultStage,
                'fail_move_next_stage' => $this->failMoveNextStage,
                'duration' => $this->duration ?: null,
                'move_to_next_stage_safe_duration' => $this->safeDuration ?: null,
                'is_end_stage' => $this->isEndStage,
                'is_end_stage_if_pass' => $this->isEndStageIfPass,
            ];

            if ($this->editingStage) {
                $this->editingStage->update($data);
                $message = 'Stage updated successfully!';
            } else {
                MethodSequenceStage::create($data);
                $message = 'Stage created successfully!';
            }

            DB::commit();

            $this->showStageModal = false;
            $this->resetForm();
            $this->setMessage($message, 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error saving stage: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteStage(MethodSequenceStage $stage): void
    {
        try {
            $stage->delete();
            $this->setMessage('Stage deleted successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error deleting stage: ' . $e->getMessage(), 'error');
        }
    }

    public function cloneStage(MethodSequenceStage $stage): void
    {
        try {
            DB::beginTransaction();

            $maxOrder = $this->version->stages()->max('order') ?? 0;

            $newStage = $stage->replicate();
            $newStage->name = $stage->name . ' (Copy)';
            $newStage->order = $maxOrder + 1;
            $newStage->save();

            DB::commit();

            $this->setMessage('Stage cloned successfully!', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error cloning stage: ' . $e->getMessage(), 'error');
        }
    }

    public function moveStageUp(MethodSequenceStage $stage): void
    {
        try {
            DB::beginTransaction();

            $stages = $this->version->stages()->orderBy('order')->get();
            $currentIndex = $stages->search(function ($s) use ($stage) {
                return $s->id === $stage->id;
            });

            if ($currentIndex > 0) {
                $previousStage = $stages[$currentIndex - 1];
                
                // Swap orders
                $tempOrder = $stage->order;
                $stage->order = $previousStage->order;
                $previousStage->order = $tempOrder;
                
                $stage->save();
                $previousStage->save();
            }

            DB::commit();
            $this->setMessage('Stage moved up successfully!', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error moving stage: ' . $e->getMessage(), 'error');
        }
    }

    public function moveStageDown(MethodSequenceStage $stage): void
    {
        try {
            DB::beginTransaction();

            $stages = $this->version->stages()->orderBy('order')->get();
            $currentIndex = $stages->search(function ($s) use ($stage) {
                return $s->id === $stage->id;
            });

            if ($currentIndex < $stages->count() - 1) {
                $nextStage = $stages[$currentIndex + 1];
                
                // Swap orders
                $tempOrder = $stage->order;
                $stage->order = $nextStage->order;
                $nextStage->order = $tempOrder;
                
                $stage->save();
                $nextStage->save();
            }

            DB::commit();
            $this->setMessage('Stage moved down successfully!', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error moving stage: ' . $e->getMessage(), 'error');
        }
    }

    public function updateStageOrder($stageIds): void
    {
        try {
            DB::beginTransaction();
            
            foreach ($stageIds as $index => $stageId) {
                MethodSequenceStage::where('id', $stageId)
                    ->where('method_sequence_version_id', $this->version->id)
                    ->update(['order' => $index + 1]);
            }
            
            DB::commit();
            $this->setMessage('Stages reordered successfully!', 'success');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error reordering stages: ' . $e->getMessage(), 'error');
        }
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function searchEquipments(): void
    {
        if (strlen($this->equipmentSearch) >= 2) {
            $this->filteredEquipments = Equipment::where('name', 'like', '%' . $this->equipmentSearch . '%')
                ->limit(10)
                ->get();
            $this->showEquipmentDropdown = true;
        } else {
            $this->showEquipmentDropdown = false;
            $this->filteredEquipments = collect();
        }
    }

    public function searchMedias(): void
    {
        if (strlen($this->mediaSearch) >= 2) {
            $this->filteredMedias = LabSubCategory::with('category')
                ->where('name', 'like', '%' . $this->mediaSearch . '%')
                ->whereHas('category', function($query) {
                    // Filter for media categories - you may need to adjust this based on your category structure
                    $query->whereIn('name', ['Media', 'Media Used', 'Culture Media']);
                })
                ->limit(10)
                ->get();
            $this->showMediaDropdown = true;
        } else {
            $this->showMediaDropdown = false;
            $this->filteredMedias = collect();
        }
    }

    public function searchControls(): void
    {
        if (strlen($this->controlSearch) >= 2) {
            $this->filteredControls = LabSubCategory::with('category')
                ->where('name', 'like', '%' . $this->controlSearch . '%')
                ->whereHas('category', function($query) {
                    // Filter for control categories - you may need to adjust this based on your category structure
                    $query->whereIn('name', ['Control', 'Controls', 'Quality Control']);
                })
                ->limit(10)
                ->get();
            $this->showControlDropdown = true;
        } else {
            $this->showControlDropdown = false;
            $this->filteredControls = collect();
        }
    }

    public function selectEquipment($id, $name): void
    {
        // Check if already selected
        $alreadySelected = collect($this->selectedEquipments)->contains('id', $id);
        if (!$alreadySelected) {
            $this->selectedEquipments[] = ['id' => $id, 'name' => $name];
        }
        $this->equipmentSearch = '';
        $this->showEquipmentDropdown = false;
        $this->filteredEquipments = collect();
    }

    public function selectMedia($id, $name): void
    {
        // Check if already selected
        $alreadySelected = collect($this->selectedMedias)->contains('id', $id);
        if (!$alreadySelected) {
            $this->selectedMedias[] = ['id' => $id, 'name' => $name];
        }
        $this->mediaSearch = '';
        $this->showMediaDropdown = false;
        $this->filteredMedias = collect();
    }

    public function selectControl($id, $name): void
    {
        // Check if already selected
        $alreadySelected = collect($this->selectedControls)->contains('id', $id);
        if (!$alreadySelected) {
            $this->selectedControls[] = ['id' => $id, 'name' => $name];
        }
        $this->controlSearch = '';
        $this->showControlDropdown = false;
        $this->filteredControls = collect();
    }

    public function removeEquipment($id): void
    {
        $this->selectedEquipments = collect($this->selectedEquipments)->reject(function($item) use ($id) {
            return $item['id'] == $id;
        })->values()->toArray();
    }

    public function removeMedia($id): void
    {
        $this->selectedMedias = collect($this->selectedMedias)->reject(function($item) use ($id) {
            return $item['id'] == $id;
        })->values()->toArray();
    }

    public function removeControl($id): void
    {
        $this->selectedControls = collect($this->selectedControls)->reject(function($item) use ($id) {
            return $item['id'] == $id;
        })->values()->toArray();
    }

    protected function resetForm(): void
    {
        $this->stageName = '';
        $this->stageDescription = '';
        $this->stageOrder = 1;
        $this->selectedEquipments = [];
        $this->selectedMedias = [];
        $this->selectedControls = [];
        $this->isResultStage = false;
        $this->failMoveNextStage = false;
        $this->duration = '';
        $this->safeDuration = '';
        $this->isEndStage = false;
        $this->isEndStageIfPass = false;
        $this->editingStage = null;
        
        // Reset searchable select fields
        $this->equipmentSearch = '';
        $this->mediaSearch = '';
        $this->controlSearch = '';
        $this->showEquipmentDropdown = false;
        $this->showMediaDropdown = false;
        $this->showControlDropdown = false;
        $this->filteredEquipments = collect();
        $this->filteredMedias = collect();
        $this->filteredControls = collect();
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}

