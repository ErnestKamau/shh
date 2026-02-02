<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use App\SampleDetails;
use App\Models\SampleDetailStaging;
use App\AnalysisType;
use App\Standards;
use App\SampleCondition;
use App\Lab;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Samples extends Component
{
    public $batchId;
    public SampleHeader $batch;
    public $not_captured = [];
    
    // Staging edit form
    public $editingStagingId = null;
    public $stagingForm = [
        'company_sub_unit_name' => '',
        'analysis_type_names' => '',
        'quantity' => 1,
    ];
    
    // Delete confirmation modals
    public $deletingStagingId = null;
    public $showDeleteModal = false;
    public $showEditModal = false;
    
    // Sample management
    public $samples = [];
    public $sampleForms = [];  // Array of sample data for editing
    public $editingSampleIndex = null;
    
    // Dropdown data
    public $analysisTypes = [];
    public $standards = [];
    public $conditions = [];
    public $samplePoints = [];
    public $products = [];
    public $labSections = [];
    public $storageLocations = [];
    public $unitsOfMeasure = ['ml', 'L', 'kg', 'g', 'pcs', 'bottles'];
    
    // Sample delete confirmation
    public $deletingSampleIndex = null;
    public $showDeleteSampleModal = false;
    
    // View parameters modal
    public $showParametersModal = false;
    public $selectedSampleCode = null;
    public $sampleParameters = [];
    
    protected $listeners = ['samplesUpdated' => '$refresh', 'refreshSamples' => '$refresh'];

    public function mount(SampleHeader $batch)
    {
        $this->batch = $batch;
        $this->batchId = $batch->id;
        $this->loadNotCaptured();
        $this->loadDropdownData();
        $this->loadSamples();
    }

    /**
     * Load samples that don't have results captured
     */
    protected function loadNotCaptured()
    {
        try {
            $this->not_captured = $this->batch->samples()
                ->whereDoesntHave('captured_results')
                ->pluck('sample_code')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading not captured samples: ' . $e->getMessage());
            $this->not_captured = [];
        }
    }

    /**
     * Load all dropdown data
     */
    protected function loadDropdownData()
    {
        try {
            $this->analysisTypes = AnalysisType::select('id', 'name', 'code')->get()->toArray();
            $this->standards = Standards::select('id', 'code', 'name')->get()->toArray();
            $this->conditions = SampleCondition::select('id', 'name')->get()->toArray();
            
            // Load customer-specific data
            if ($this->batch->client_id) {
                $this->samplePoints = \App\Models\CRM\SamplePoint::where('customer_id', $this->batch->client_id)
                    ->select('id', 'name')->get()->toArray();
                $this->products = \App\Models\CRM\CompanyProduct::where('customer_id', $this->batch->client_id)
                    ->select('id', 'name')->get()->toArray();
            }
            
            $this->labSections = Lab::select('id', 'name', 'code')->get()->toArray();
            
            // Load storage locations if model exists
            if (class_exists('\App\LabStore')) {
                $this->storageLocations = \App\LabStore::select('id', 'name')->get()->toArray();
            }
            
        } catch (\Exception $e) {
            Log::error('Error loading dropdown data: ' . $e->getMessage());
        }
    }

    /**
     * Load existing samples into form array
     */
    protected function loadSamples()
    {
        try {
            $samples = $this->batch->samples;
            
            $this->sampleForms = [];
            foreach ($samples as $index => $sample) {
                $this->sampleForms[$index] = [
                    'id' => $sample->id,
                    'sample_code' => $sample->sample_code,
                    'analysis_type_id' => array_filter(explode(',', $sample->analysis_type_id ?? '')),
                    'lab_id' => $sample->lab_id ?? '',
                    'condition_id' => $sample->condition_id ?? '',
                    'sample_point_id' => $sample->sample_point_id ?? '',
                    'company_product_id' => $sample->company_product_id ?? '',
                    'description' => $sample->description ?? '',
                    'time_sampled' => $sample->time_sampled ?? '',
                    'main_standard' => $sample->main_standard ?? '',
                    'secondary_standard' => $sample->secondary_standard ?? '',
                    'disposal_date' => $sample->disposal_date ?? '',
                    'storage_location' => $sample->storage_location ?? '',
                    'storage_slot' => $sample->storage_slot ?? '',
                    'sample_quantity' => $sample->sample_quantity ?? 1,
                    'unit_of_measure' => $sample->unit_of_measure ?? 'ml',
                ];
            }
            
            $this->samples = $samples;
            
        } catch (\Exception $e) {
            Log::error('Error loading samples: ' . $e->getMessage());
            $this->sampleForms = [];
        }
    }

    /**
     * Assign samples from staging data
     * Note: This triggers the existing modal flow which handles complex assignment logic
     */
    public function assignSamples($stagingId)
    {
        $this->dispatch('openAssignModal', stagingId: $stagingId, headerId: $this->batchId);
    }

    /**
     * Open edit modal for staging record
     */
    public function editStaging($stagingId)
    {
        try {
            $staging = SampleDetailStaging::findOrFail($stagingId);
            
            $this->editingStagingId = $stagingId;
            $dataJson = $staging->data_json;
            
            $this->stagingForm = [
                'company_sub_unit_name' => $dataJson['company_sub_unit_name'] ?? '',
                'analysis_type_names' => $dataJson['analysis_type_names'] ?? '',
                'quantity' => $dataJson['quantity'] ?? 1,
            ];
            
            $this->showEditModal = true;
            
        } catch (\Exception $e) {
            Log::error('Error loading staging for edit: ' . $e->getMessage());
            session()->flash('error', 'Failed to load staging data');
        }
    }

    /**
     * Update staging record
     */
    public function updateStaging()
    {
        $this->validate([
            'stagingForm.company_sub_unit_name' => 'required|string',
            'stagingForm.analysis_type_names' => 'required|string',
            'stagingForm.quantity' => 'required|integer|min:1',
        ]);

        try {
            $staging = SampleDetailStaging::findOrFail($this->editingStagingId);
            
            $dataJson = $staging->data_json;
            $dataJson['company_sub_unit_name'] = $this->stagingForm['company_sub_unit_name'];
            $dataJson['analysis_type_names'] = $this->stagingForm['analysis_type_names'];
            $dataJson['quantity'] = $this->stagingForm['quantity'];
            
            $staging->data_json = $dataJson;
            $staging->save();
            
            $this->showEditModal = false;
            $this->reset('editingStagingId', 'stagingForm');
            
            $this->dispatch('samplesUpdated');
            session()->flash('success', 'Staging data updated successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error updating staging: ' . $e->getMessage());
            session()->flash('error', 'Failed to update staging data: ' . $e->getMessage());
        }
    }

    /**
     * Cancel editing
     */
    public function cancelEdit()
    {
        $this->showEditModal = false;
        $this->reset('editingStagingId', 'stagingForm');
    }

    /**
     * Confirm delete staging
     */
    public function confirmDeleteStaging($stagingId)
    {
        $this->deletingStagingId = $stagingId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete staging record
     */
    public function deleteStaging()
    {
        try {
            $staging = SampleDetailStaging::findOrFail($this->deletingStagingId);
            $staging->delete();
            
            $this->showDeleteModal = false;
            $this->reset('deletingStagingId');
            
            $this->dispatch('samplesUpdated');
            session()->flash('success', 'Staging record deleted successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error deleting staging: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete staging record: ' . $e->getMessage());
        }
    }

    /**
     * Cancel delete
     */
    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->reset('deletingStagingId');
    }

    /**
     * Add new empty sample row
     */
    public function addSample()
    {
        $nextCode = $this->generateSampleCode();
        
        $newSample = [
            'id' => null,  // Null indicates unsaved
            'sample_code' => $nextCode,
            'analysis_type_id' => [],
            'lab_id' => '',
            'condition_id' => '',
            'sample_point_id' => '',
            'company_product_id' => '',
            'description' => '',
            'time_sampled' => '',
            'main_standard' => '',
            'secondary_standard' => '',
            'disposal_date' => '',
            'storage_location' => '',
            'storage_slot' => '',
            'sample_quantity' => 1,
            'unit_of_measure' => 'ml',
        ];
        
        $this->sampleForms[] = $newSample;
        session()->flash('success', 'New sample row added');
    }

    /**
     * Generate sample code based on batch code
     */
    protected function generateSampleCode()
    {
        $batchCode = $this->batch->batch_code;
        $existingCount = count($this->sampleForms);
        
        // Format: BATCH_CODE + sequence (e.g., 2024L001-01)
        return $batchCode . '-' . str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Duplicate last sample row
     */
    public function duplicateLastSample()
    {
        if (count($this->sampleForms) === 0) {
            session()->flash('error', 'No sample to duplicate');
            return;
        }
        
        $lastSample = end($this->sampleForms);
        $newSample = $lastSample;
        $newSample['id'] = null;  // Mark as new
        $newSample['sample_code'] = $this->generateSampleCode();
        
        $this->sampleForms[] = $newSample;
        session()->flash('success', 'Sample duplicated successfully');
    }

    /**
     * Confirm sample deletion
     */
    public function confirmDeleteSample($index)
    {
        $this->deletingSampleIndex = $index;
        $this->showDeleteSampleModal = true;
    }

    /**
     * Delete sample
     */
    public function deleteSample()
    {
        $index = $this->deletingSampleIndex;
        
        if (isset($this->sampleForms[$index])) {
            $sampleId = $this->sampleForms[$index]['id'];
            
            // If saved to DB, delete from DB
            if ($sampleId) {
                try {
                    SampleDetails::find($sampleId)->delete();
                    session()->flash('success', 'Sample deleted from database');
                } catch (\Exception $e) {
                    Log::error('Error deleting sample: ' . $e->getMessage());
                    session()->flash('error', 'Failed to delete sample');
                    $this->showDeleteSampleModal = false;
                    return;
                }
            }
            
            // Remove from array
            unset($this->sampleForms[$index]);
            $this->sampleForms = array_values($this->sampleForms);  // Re-index
        }
        
        $this->showDeleteSampleModal = false;
        $this->deletingSampleIndex = null;
    }

    /**
     * Cancel sample delete
     */
    public function cancelDeleteSample()
    {
        $this->showDeleteSampleModal = false;
        $this->deletingSampleIndex = null;
    }

    /**
     * Save all samples with validation
     */
    public function saveSamples()
    {
        // Validate all samples
        $this->validate([
            'sampleForms.*.analysis_type_id' => 'required|array|min:1',
            'sampleForms.*.lab_id' => 'required',
            'sampleForms.*.condition_id' => 'required',
            'sampleForms.*.sample_point_id' => 'required',
            'sampleForms.*.company_product_id' => 'required',
            'sampleForms.*.main_standard' => 'required',
            'sampleForms.*.sample_quantity' => 'required|numeric|min:0',
        ], [
            'sampleForms.*.analysis_type_id.required' => 'Analysis type is required',
            'sampleForms.*.analysis_type_id.min' => 'At least  one analysis type must be selected',
            'sampleForms.*.lab_id.required' => 'Lab is required',
            'sampleForms.*.condition_id.required' => 'Condition is required',
            'sampleForms.*.sample_point_id.required' => 'Sample point is required',
            'sampleForms.*.company_product_id.required' => 'Product is required',
            'sampleForms.*.main_standard.required' => 'Main standard is required',
        ]);
        
        DB::beginTransaction();
        
        try {
            foreach ($this->sampleForms as $index => $sampleData) {
                if ($sampleData['id']) {
                    // Update existing
                    $sample = SampleDetails::find($sampleData['id']);
                } else {
                    // Create new
                    $sample = new SampleDetails();
                    $sample->sample_header_id = $this->batch->id;
                    $sample->sample_code = $sampleData['sample_code'];
                }
                
                // Set all fields
                $sample->analysis_type_id = is_array($sampleData['analysis_type_id']) 
                    ? implode(',', $sampleData['analysis_type_id'])
                    : $sampleData['analysis_type_id'];
                $sample->lab_id = $sampleData['lab_id'];
                $sample->condition_id = $sampleData['condition_id'];
                $sample->sample_point_id = $sampleData['sample_point_id'];
                $sample->company_product_id = $sampleData['company_product_id'];
                $sample->description = $sampleData['description'];
                $sample->time_sampled = $sampleData['time_sampled'];
                $sample->main_standard = $sampleData['main_standard'];
                $sample->secondary_standard = $sampleData['secondary_standard'];
                $sample->disposal_date = $sampleData['disposal_date'];
                $sample->storage_location = $sampleData['storage_location'];
                $sample->storage_slot = $sampleData['storage_slot'];
                $sample->sample_quantity = $sampleData['sample_quantity'];
                $sample->unit_of_measure = $sampleData['unit_of_measure'];
                
                $sample->save();
                
                // Update ID in form array for subsequent saves
                $this->sampleForms[$index]['id'] = $sample->id;
            }
            
            DB::commit();
            
            session()->flash('success', 'All samples saved successfully!');
            $this->dispatch('samplesUpdated');
            $this->loadSamples();  // Reload to get fresh data
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving samples: ' . $e->getMessage());
            session()->flash('error', 'Failed to save samples: ' . $e->getMessage());
        }
    }

    /**
     * Cancel delete staging
     */
    public function cancelDeleteOld()
    {
        $this->showDeleteModal = false;
        $this->reset('deletingStagingId');
    }

    /**
     * Phase 4: Dependent Dropdown - Filter labs when analysis types change
     */
    public function updatedSampleForms($value, $key)
    {
        // Check if analysis_type_id was updated
        if (str_contains($key, '.analysis_type_id')) {
            // Extract index from key (e.g., "0.analysis_type_id" -> 0)
            preg_match('/(\d+)\./', $key, $matches);
            $index = $matches[1] ?? null;
            
            if ($index !== null && isset($this->sampleForms[$index])) {
                $this->filterLabsForSample($index);
            }
        }
    }

    /**
     * Filter available labs based on selected analysis types
     */
    protected function filterLabsForSample($index)
    {
        $selectedAnalysisIds = $this->sampleForms[$index]['analysis_type_id'] ?? [];
        
        if (empty($selectedAnalysisIds)) {
            // Reset to all labs if no analysis types selected
            $this->labSections = Lab::select('id', 'name', 'code')->get()->toArray();
            return;
        }
        
        try {
            // Get unique lab IDs from selected analysis types
            $labIds = AnalysisType::whereIn('id', $selectedAnalysisIds)
                ->pluck('lab_id')
                ->unique()
                ->filter()
                ->toArray();
            
            if (!empty($labIds)) {
                // Filter labs to only those that handle the selected analysis types
                $this->labSections = Lab::whereIn('id', $labIds)
                    ->select('id', 'name', 'code')
                    ->get()
                    ->toArray();
            } else {
                // No labs found, keep all labs available
                $this->labSections = Lab::select('id', 'name', 'code')->get()->toArray();
            }
            
        } catch (\Exception $e) {
            Log::error('Error filtering labs: ' . $e->getMessage());
            // On error, show all labs
            $this->labSections = Lab::select('id', 'name', 'code')->get()->toArray();
        }
    }

    /**
     * Phase 5: View Parameters Modal - Show all analytes for a sample
     */
    public function viewParameters($sampleCode)
    {
        try {
            $this->selectedSampleCode = $sampleCode;
            
            // Find the sample
            $sample = SampleDetails::where('sample_code', $sampleCode)
                ->where('sample_header_id', $this->batch->id)
                ->first();
            
            if (!$sample) {
                session()->flash('error', 'Sample not found');
                return;
            }
            
            // Get analysis type IDs (comma-separated)
            $analysisIds = array_filter(explode(',', $sample->analysis_type_id ?? ''));
            
            if (empty($analysisIds)) {
                $this->sampleParameters = [];
                $this->showParametersModal = true;
                return;
            }
            
            // Load analytes grouped by analysis type
            $parameters = [];
            
            foreach ($analysisIds as $analysisId) {
                $analysisType = AnalysisType::find($analysisId);
                
                if ($analysisType) {
                    // Get active analysis elements (which contain analytes)
                    $elements = $analysisType->active_analysis_elements();
                    
                    $analytes = [];
                    foreach ($elements as $element) {
                        $analyte = \App\Analyte::find($element->analyte_id);
                        if ($analyte) {
                            $analytes[] = [
                                'code' => $analyte->code,
                                'name' => $analyte->name,
                                'unit' => $analyte->reporting_unit ?? '-',
                                'method' => $element->mmethod->name ?? '-',
                            ];
                        }
                    }
                    
                    if (!empty($analytes)) {
                        $parameters[$analysisType->name] = $analytes;
                    }
                }
            }
            
            $this->sampleParameters = $parameters;
            $this->showParametersModal = true;
            
        } catch (\Exception $e) {
            Log::error('Error loading sample parameters: ' . $e->getMessage());
            session()->flash('error', 'Failed to load parameters: ' . $e->getMessage());
        }
    }

    /**
     * Close view parameters modal
     */
    public function cancelViewParameters()
    {
        $this->showParametersModal = false;
        $this->selectedSampleCode = null;
        $this->sampleParameters = [];
    }

    // ========== Comments & Interpretations Feature ==========
    
    public $showCommentsModal = false;
    public $editingCommentsSampleId = null;
    public $commentsForm = [
        'header_body' => '',
        'main_body' => '',
        'notes_body' => '',
        'batch_comment_scope' => '1', // 1=Concatenate, 2=Overwrite
    ];

    /**
     * Show Comments & Interpretations modal for a sample
     */
    public function showCommentsModal($sampleId)
    {
        try {
            $sample = SampleDetails::findOrFail($sampleId);
            
            $this->editingCommentsSampleId = $sampleId;
            $this->commentsForm = [
                'header_body' => $sample->header_body ?? '',
                'main_body' => $sample->main_body ?? '',
                'notes_body' => $sample->notes_body ?? '',
                'batch_comment_scope' => '1',
            ];
            
            $this->showCommentsModal = true;
            
        } catch (\Exception $e) {
            Log::error('Error loading comments: ' . $e->getMessage());
            session()->flash('error', 'Failed to load sample comments');
        }
    }

    /**
     * Save comments and interpretations
     */
    public function saveComments()
    {
        try {
            $sample = SampleDetails::findOrFail($this->editingCommentsSampleId);
            
            $sample->main_body = $this->commentsForm['main_body'];
            $sample->header_body = $this->commentsForm['header_body'];
            $sample->notes_body = $this->commentsForm['notes_body'];
            $sample->save();
            
            $this->showCommentsModal = false;
            $this->reset('editingCommentsSampleId', 'commentsForm');
            
            session()->flash('success', 'Sample Comments and Interpretations have been saved');
            
        } catch (\Exception $e) {
            Log::error('Error saving comments: ' . $e->getMessage());
            session()->flash('error', 'Failed to save comments: ' . $e->getMessage());
        }
    }

    /**
     * Cancel comments modal
     */
    public function cancelComments()
    {
        $this->showCommentsModal = false;
        $this->reset('editingCommentsSampleId', 'commentsForm');
    }

    // ========== Interlab Transfer Feature ==========
    
    public $showInterlabModal = false;
    public $interlabSampleId = null;
    public $interlabSampleCode = '';
    public $interlabForm = [
        'to_lab_section_id' => '',
        'quantity' => '',
        'expected_date' => '',
        'prelim_date' => '',
        'remarks' => '',
    ];

    /**
     * Show Interlab Transfer modal for a sample
     */
    public function showInterlabModal($sampleId, $sampleCode)
    {
        try {
            $sample = SampleDetails::findOrFail($sampleId);
            
            $this->interlabSampleId = $sampleId;
            $this->interlabSampleCode = $sampleCode;
            
            // Get target date from batch
            $targetDate = $this->batch->batch_date_expected ?? date('Y-m-d');
            
            $this->interlabForm = [
                'to_lab_section_id' => '',
                'quantity' => '',
                'expected_date' => $targetDate,
                'prelim_date' => '',
                'remarks' => '',
            ];
            
            $this->showInterlabModal = true;
            
        } catch (\Exception $e) {
            Log::error('Error loading interlab modal: ' . $e->getMessage());
            session()->flash('error', 'Failed to open interlab transfer modal');
        }
    }

    /**
     * Save interlab transfer log
     */
    public function saveInterlabLog()
    {
        $this->validate([
            'interlabForm.to_lab_section_id' => 'required',
            'interlabForm.quantity' => 'required|numeric|min:1',
            'interlabForm.expected_date' => 'nullable|date',
        ], [
            'interlabForm.to_lab_section_id.required' => 'To Lab is required',
            'interlabForm.quantity.required' => 'Quantity is required',
            'interlabForm.quantity.numeric' => 'Quantity must be a number',
            'interlabForm.quantity.min' => 'Quantity must be at least 1',
        ]);

        try {
            DB::beginTransaction();
            
            // Get the last interlab log to determine from_lab
            $lastLog = \App\InterLabLog::where('sample_id', $this->interlabSampleId)
                ->where('status', 1)
                ->orderBy('date_received', 'DESC')
                ->first();
            
            $interlabData = [
                'sample_id' => $this->interlabSampleId,
                'to_lab_section_id' => $this->interlabForm['to_lab_section_id'],
                'from_lab_section_id' => $lastLog ? $lastLog->to_lab_section_id : 0,
                'quantity' => $this->interlabForm['quantity'],
                'submited_by' => auth()->id(),
                'date_submitted' => now(),
                'expected_date' => $this->interlabForm['expected_date'] ?: $this->batch->batch_date_expected,
                'prelim_date' => $this->interlabForm['prelim_date'] ?: null,
                'remarks' => $this->interlabForm['remarks'] ?: null,
            ];
            
            \App\InterLabLog::create($interlabData);
            
            DB::commit();
            
            $this->showInterlabModal = false;
            $this->reset('interlabSampleId', 'interlabSampleCode', 'interlabForm');
            
            // Dispatch event to refresh interlab logs tab if it exists
            $this->dispatch('interlabLogsUpdated');
            
            session()->flash('success', 'Inter laboratory Log created successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving interlab log: ' . $e->getMessage());
            session()->flash('error', 'Failed to save interlab log: ' . $e->getMessage());
        }
    }

    /**
     * Cancel interlab modal
     */
    public function cancelInterlab()
    {
        $this->showInterlabModal = false;
        $this->reset('interlabSampleId', 'interlabSampleCode', 'interlabForm');
    }

    public function render()
    {
        // Reload batch with fresh data
        $this->batch = $this->batch->fresh([
            'samples',
            'stagingDetails' => function ($query) {
                $query->where('is_processed', 0);
            },
            'sample_type'
        ]);
        
        return view('livewire.batch.tabs.samples', [
            'batch' => $this->batch,
            'not_captured' => $this->not_captured,
        ]);
    }
}
