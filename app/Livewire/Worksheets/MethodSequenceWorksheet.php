<?php

namespace App\Livewire\Worksheets;

use App\CapturedResult;
use App\Models\Equipments\Equipment;
use App\Models\MethodSequences\MethodSequence;
use App\Models\Worksheets\MethodSequenceRun;
use App\Models\Worksheets\MethodSequenceRunSample;
use App\Models\Worksheets\MethodSequenceRunStageData;
use App\Models\Worksheets\MethodSequenceStageEquipmentUsage;
use App\Models\Worksheets\MethodSequenceStageMediaUsage;
use App\Models\Worksheets\MethodSequenceStageControlUsage;
use App\Models\Worksheets\MethodSequenceStageControlResult;
use App\Models\Worksheets\MethodSequenceStageSampleResult;
use App\LabSubCategory;
use App\SampleHeader;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MethodSequenceWorksheet extends Component
{
    public SampleHeader $batch;
    public MethodSequence $methodSequence;
    public $runs;
    public $selectedRunId = null;
    public $showCreateRunModal = false;
    public $showStageModal = false;
    
    // Create Run Form
    public $newRunName = '';
    public $selectedSamples = [];
    public $availableSamples = [];
    public $selectedAnalystId = null;
    public $runDate = '';
    
    // Stage Data Form
    public $editingStageData = null;
    public $stageFormData = [];
    
    // Collapsible UI
    public $expandedRunIds = [];
    public $expandedStageIds = [];
    
    // Searchable dropdowns
    public $equipmentSearch = '';
    public $mediaSearch = '';
    public $controlSearch = '';
    public $showEquipmentDropdown = false;
    public $showMediaDropdown = false;
    public $showControlDropdown = false;
    public $filteredEquipments = [];
    public $filteredMedias = [];
    public $filteredControls = [];
    
    // Messages
    public $message = '';
    public $messageType = '';
    
    // Analysts
    public $analysts = [];
    
    // Edit Run Modal
    public $showEditRunModal = false;
    public $editingRunId = null;
    public $editRunName = '';
    public $editRunAnalystId = null;
    public $editRunDate = '';

    public function mount(SampleHeader $batch, MethodSequence $methodSequence): void
    {
        $this->batch = $batch;
        $this->methodSequence = $methodSequence;
        $this->loadData();
    }

    public function loadData(): void
    {
        // Load existing runs for this batch and method sequence
        $this->runs = MethodSequenceRun::where('sample_header_id', $this->batch->id)
            ->where('method_sequence_id', $this->methodSequence->id)
            ->with(['samples.capturedResult.sample', 'currentStage', 'stageData.stage'])
            ->orderBy('run_number')
            ->get();

        // Load available samples (captured results not yet in any run)
        /** @var array<int> $runIds */
        $runIds = $this->runs->pluck('id')->toArray();
        /** @var array<int> $usedCapturedIds */
        $usedCapturedIds = [];
        
        if (count($runIds) > 0) {
            $usedCapturedIds = MethodSequenceRunSample::whereIn('run_id', $runIds)
                ->pluck('captured_result_id')
                ->toArray();
        }

        $query = CapturedResult::where('sample_header_id', $this->batch->id)
            ->where('method_sequence_id', $this->methodSequence->id)
            ->with('sample');
            
        if (count($usedCapturedIds) > 0) {
            $query->whereNotIn('id', $usedCapturedIds);
        }
        
        $this->availableSamples = $query->get();

        // Select first run if available
        /** @var MethodSequenceRun|null $firstRun */
        $firstRun = $this->runs->first();
        if ($firstRun && !$this->selectedRunId) {
            $this->selectedRunId = $firstRun->id;
        }
    }

    public function openCreateRunModal(): void
    {
        $this->showCreateRunModal = true;
        $this->newRunName = '';
        $this->selectedSamples = [];
    }

    public function createRun(): void
    {
        $this->validate([
            'newRunName' => 'required|string|max:255',
            'selectedSamples' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Determine run number
            $lastRun = MethodSequenceRun::where('sample_header_id', $this->batch->id)
                ->where('method_sequence_id', $this->methodSequence->id)
                ->max('run_number');
            
            $runNumber = ($lastRun ?? 0) + 1;

            // Create run
            $run = MethodSequenceRun::create([
                'sample_header_id' => $this->batch->id,
                'method_sequence_id' => $this->methodSequence->id,
                'analyst_id' => $this->selectedAnalystId,
                'run_date' => $this->runDate ?: now()->toDateString(),
                'run_number' => $runNumber,
                'run_name' => $this->newRunName,
                'started_by_user_id' => Auth::id(),
                'started_at' => now(),
                'status' => 'in_progress',
            ]);

            // Add samples to run
            foreach ($this->selectedSamples as $capturedId) {
                $captured = CapturedResult::find($capturedId);
                if ($captured) {
                    MethodSequenceRunSample::create([
                        'run_id' => $run->id,
                        'captured_result_id' => $captured->id,
                        'sample_detail_id' => $captured->sample_detail_id,
                        'sample_header_id' => $captured->sample_header_id,
                    ]);
                }
            }

            // Create stage data for all stages
            $stages = $this->methodSequence->activeVersion->stages()->orderBy('order')->get();
            foreach ($stages as $stage) {
                MethodSequenceRunStageData::create([
                    'run_id' => $run->id,
                    'stage_id' => $stage->id,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            $this->showCreateRunModal = false;
            $this->setMessage('Run created successfully!', 'success');
            $this->loadData();
            $this->selectedRunId = $run->id;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error creating run: ' . $e->getMessage(), 'error');
        }
    }

    public function selectRun(int $runId): void
    {
        $this->selectedRunId = $runId;
    }

    public function openStageModal(int $stageDataId): void
    {
        $this->editingStageData = MethodSequenceRunStageData::with(['stage', 'equipmentUsage', 'mediaUsage', 'controlUsage'])->find($stageDataId);
        
        if ($this->editingStageData) {
            $this->stageFormData = [
                'date_in' => $this->editingStageData->date_in?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'time_in' => $this->editingStageData->time_in?->format('H:i') ?? now()->format('H:i'),
                'started_by_user_id' => $this->editingStageData->started_by_user_id ?? Auth::id(),
                'date_out' => $this->editingStageData->date_out?->format('Y-m-d') ?? '',
                'time_out' => $this->editingStageData->time_out?->format('H:i') ?? '',
                'completed_by_user_id' => $this->editingStageData->completed_by_user_id,
                'equipment' => $this->editingStageData->equipmentUsage->pluck('equipment_id')->toArray(),
                'media' => $this->editingStageData->mediaUsage->map(fn($m) => [
                    'id' => $m->id,
                    'media_id' => $m->media_id,
                    'volume' => $m->volume,
                    'unit' => $m->unit,
                ])->toArray(),
                'controls' => $this->editingStageData->controlUsage->map(fn($c) => [
                    'id' => $c->id,
                    'control_id' => $c->control_id,
                    'volume' => $c->volume,
                    'unit' => $c->unit,
                ])->toArray(),
            ];
            
            $this->showStageModal = true;
        }
    }

    public function saveStageData(): void
    {
        try {
            DB::beginTransaction();

            if (!$this->editingStageData) {
                throw new \Exception('No stage data selected');
            }

            // Update stage data
            $this->editingStageData->update([
                'date_in' => $this->stageFormData['date_in'] ?: null,
                'time_in' => $this->stageFormData['time_in'] ?: null,
                'started_by_user_id' => $this->stageFormData['started_by_user_id'] ?: null,
                'date_out' => $this->stageFormData['date_out'] ?: null,
                'time_out' => $this->stageFormData['time_out'] ?: null,
                'completed_by_user_id' => $this->stageFormData['completed_by_user_id'] ?: null,
                'status' => $this->stageFormData['date_out'] ? 'completed' : 'in_progress',
            ]);

            // Update equipment usage
            $this->editingStageData->equipmentUsage()->delete();
            if (isset($this->stageFormData['equipment'])) {
                foreach ($this->stageFormData['equipment'] as $equipmentId) {
                    $equipment = Equipment::find($equipmentId);
                    if ($equipment) {
                        MethodSequenceStageEquipmentUsage::create([
                            'run_stage_data_id' => $this->editingStageData->id,
                            'equipment_id' => $equipment->id,
                            'equipment_name' => $equipment->name,
                        ]);
                    }
                }
            }

            // Update media usage
            $this->editingStageData->mediaUsage()->delete();
            if (isset($this->stageFormData['media'])) {
                foreach ($this->stageFormData['media'] as $media) {
                    if ($media['media_id']) {
                        $mediaItem = LabSubCategory::find($media['media_id']);
                        MethodSequenceStageMediaUsage::create([
                            'run_stage_data_id' => $this->editingStageData->id,
                            'media_id' => $media['media_id'],
                            'media_name' => $mediaItem->name ?? '',
                            'volume' => $media['volume'] ?? null,
                            'unit' => $media['unit'] ?? null,
                        ]);
                    }
                }
            }

            // Update control usage
            $this->editingStageData->controlUsage()->delete();
            if (isset($this->stageFormData['controls'])) {
                foreach ($this->stageFormData['controls'] as $control) {
                    if ($control['control_id']) {
                        $controlItem = LabSubCategory::find($control['control_id']);
                        MethodSequenceStageControlUsage::create([
                            'run_stage_data_id' => $this->editingStageData->id,
                            'control_id' => $control['control_id'],
                            'control_name' => $controlItem->name ?? '',
                            'volume' => $control['volume'] ?? null,
                            'unit' => $control['unit'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            $this->showStageModal = false;
            $this->setMessage('Stage data saved successfully!', 'success');
            $this->loadData();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setMessage('Error saving stage data: ' . $e->getMessage(), 'error');
        }
    }

    public function addMediaRow(): void
    {
        $this->stageFormData['media'][] = ['id' => null, 'media_id' => null, 'volume' => null, 'unit' => null];
    }

    public function addControlRow(): void
    {
        $this->stageFormData['controls'][] = ['id' => null, 'control_id' => null, 'volume' => null, 'unit' => null, 'batch_number' => null];
    }

    public function toggleRunExpansion($runId): void
    {
        if (in_array($runId, $this->expandedRunIds)) {
            $this->expandedRunIds = array_diff($this->expandedRunIds, [$runId]);
        } else {
            $this->expandedRunIds[] = $runId;
            $this->loadRunStages($runId);
        }
    }

    public function toggleStageExpansion($stageId): void
    {
        if (in_array($stageId, $this->expandedStageIds)) {
            $this->expandedStageIds = array_diff($this->expandedStageIds, [$stageId]);
        } else {
            $this->expandedStageIds[] = $stageId;
        }
    }

    public function loadRunStages($runId): void
    {
        // Just reload the data - no need to manipulate the collection
        // The blade template will access relationships directly
        $this->loadData();
    }

    public function autoSaveStageField($stageDataId, $field, $value): void
    {
        try {
            $stageData = MethodSequenceRunStageData::find($stageDataId);
            if ($stageData) {
                $stageData->update([$field => $value]);
                $this->setMessage('Field saved successfully!', 'success');
            }
        } catch (\Exception $e) {
            $this->setMessage('Error saving field: ' . $e->getMessage(), 'error');
        }
    }

    public function autoSaveEquipmentUsage($stageDataId, $equipmentId, $equipmentName): void
    {
        try {
            // Delete existing equipment usage
            MethodSequenceStageEquipmentUsage::where('run_stage_data_id', $stageDataId)->delete();
            
            if ($equipmentId) {
                MethodSequenceStageEquipmentUsage::create([
                    'run_stage_data_id' => $stageDataId,
                    'equipment_id' => $equipmentId,
                    'equipment_name' => $equipmentName,
                ]);
            }
            
            $this->setMessage('Equipment usage saved!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error saving equipment usage: ' . $e->getMessage(), 'error');
        }
    }

    public function autoSaveMediaUsage($stageDataId, $mediaId, $volume, $unit, $batchNumber): void
    {
        try {
            // Delete existing media usage
            MethodSequenceStageMediaUsage::where('run_stage_data_id', $stageDataId)->delete();
            
            if ($mediaId) {
                $media = LabSubCategory::find($mediaId);
                MethodSequenceStageMediaUsage::create([
                    'run_stage_data_id' => $stageDataId,
                    'media_id' => $mediaId,
                    'media_name' => $media->name ?? '',
                    'volume' => $volume,
                    'unit' => $unit,
                    'batch_number' => $batchNumber,
                ]);
            }
            
            $this->setMessage('Media usage saved!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error saving media usage: ' . $e->getMessage(), 'error');
        }
    }

    public function autoSaveControlUsage($stageDataId, $controlId, $volume, $unit, $batchNumber): void
    {
        try {
            // Delete existing control usage
            MethodSequenceStageControlUsage::where('run_stage_data_id', $stageDataId)->delete();
            
            if ($controlId) {
                $control = LabSubCategory::find($controlId);
                MethodSequenceStageControlUsage::create([
                    'run_stage_data_id' => $stageDataId,
                    'control_id' => $controlId,
                    'control_name' => $control->name ?? '',
                    'volume' => $volume,
                    'unit' => $unit,
                    'batch_number' => $batchNumber,
                ]);
            }
            
            $this->setMessage('Control usage saved!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error saving control usage: ' . $e->getMessage(), 'error');
        }
    }

    public function searchEquipments(): void
    {
        $this->filteredEquipments = Equipment::where('active', 1)
            ->where('name', 'LIKE', '%' . $this->equipmentSearch . '%')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function searchMedias(): void
    {
        $this->filteredMedias = LabSubCategory::whereHas('category', function($q) {
                $q->where('name', 'LIKE', '%media%');
            })
            ->where('name', 'LIKE', '%' . $this->mediaSearch . '%')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function searchControls(): void
    {
        $this->filteredControls = LabSubCategory::whereHas('category', function($q) {
                $q->where('name', 'LIKE', '%control%');
            })
            ->where('name', 'LIKE', '%' . $this->controlSearch . '%')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function updateResult($stageDataId, $result, $remark): void
    {
        try {
            $stageData = MethodSequenceRunStageData::find($stageDataId);
            if ($stageData) {
                // Update captured results
                $stageData->update([
                    'result' => $result,
                    'remark' => $remark,
                    'status' => 'completed'
                ]);

                // Check if this is an end stage
                $stage = $stageData->stage;
                if ($stage && $stage->is_end_stage) {
                    // Check if we should complete based on result
                    $shouldComplete = true;
                    
                    if ($stage->is_end_stage_if_pass) {
                        // Only complete if result is Pass
                        $shouldComplete = ($result === 'Pass');
                    }
                    
                    if ($shouldComplete) {
                        // Complete the run
                        $run = $stageData->run;
                        $run->update([
                            'status' => 'completed',
                            'completed_at' => now()
                        ]);
                    }
                }

                $this->setMessage('Result updated successfully!', 'success');
                $this->loadData();
            }
        } catch (\Exception $e) {
            $this->setMessage('Error updating result: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteRun($runId): void
    {
        try {
            $run = MethodSequenceRun::find($runId);
            if ($run) {
                $run->delete();
                $this->setMessage('Run deleted successfully!', 'success');
                $this->loadData();
            }
        } catch (\Exception $e) {
            $this->setMessage('Error deleting run: ' . $e->getMessage(), 'error');
        }
    }

    public function editRun($runId): void
    {
        $run = MethodSequenceRun::find($runId);
        if ($run) {
            $this->editingRunId = $runId;
            $this->editRunName = $run->run_name;
            $this->editRunAnalystId = $run->analyst_id;
            $this->editRunDate = $run->run_date ? $run->run_date->format('Y-m-d') : '';
            $this->showEditRunModal = true;
        }
    }

    public function updateRun(): void
    {
        try {
            $run = MethodSequenceRun::find($this->editingRunId);
            if ($run) {
                $run->update([
                    'run_name' => $this->editRunName,
                    'analyst_id' => $this->editRunAnalystId,
                    'run_date' => $this->editRunDate,
                ]);
                
                $this->showEditRunModal = false;
                $this->setMessage('Run updated successfully!', 'success');
                $this->loadData();
            }
        } catch (\Exception $e) {
            $this->setMessage('Error updating run: ' . $e->getMessage(), 'error');
        }
    }

    public function selectMedia($stageDataId, $mediaId, $mediaName): void
    {
        $this->showMediaDropdown = false;
        $this->mediaSearch = '';
        // Store the selected media for later use when user fills volume/batch
    }

    public function selectControl($stageDataId, $controlId, $controlName): void
    {
        $this->showControlDropdown = false;
        $this->controlSearch = '';
        // Store the selected control for later use when user fills volume/batch
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function getAnalysts()
    {
        // Get analyst role ID from system configuration
        $analystRoleConfig = \App\Models\System\SystemConfiguration::where('key', 'analyst_role_id')->first();
        
        if (!$analystRoleConfig) {
            // Fallback to all active users if config not found
            return User::where('active', 1)->get();
        }
        
        $analystRoleId = $analystRoleConfig->value;
        
        // Get user IDs with analyst role
        $analystUserIds = \App\UserRole::where('role_id', $analystRoleId)
            ->pluck('user_id')
            ->toArray();
        
        // Return users with analyst role and active status
        return User::whereIn('id', $analystUserIds)
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        $selectedRun = $this->selectedRunId ? MethodSequenceRun::with([
            'samples.capturedResult.sample',
            'stageData.stage',
            'stageData.equipmentUsage',
            'stageData.mediaUsage',
            'stageData.controlUsage.results',
            'stageData.sampleResults'
        ])->find($this->selectedRunId) : null;

        // dd($this->getAnalysts());
        $this->analysts = $this->getAnalysts();

        // dd($this->analysts);

        return view('livewire.worksheets.method-sequence-worksheet', [
            'users' => User::where('active', 1)->get(),
            'analysts' => $this->analysts,
            'equipments' => Equipment::where('active', 1)->get(),
            'medias' => LabSubCategory::whereHas('category', function($q) {
                $q->where('name', 'LIKE', '%media%');
            })->get(),
            'controls' => LabSubCategory::whereHas('category', function($q) {
                $q->where('name', 'LIKE', '%control%');
            })->get(),
            'selectedRun' => $selectedRun,
        ]);
    }
}
