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
    
    // Track which row is being edited (null = all read-only)
    public $editingRowIndex = null;
    
    // Analysis type dropdown state (per row)
    public $showAnalysisTypeDropdown = [];
    public $analysisTypeSearch = '';
    public $uncertaintyRequired = false;
    
    // Parameter Modal Data
    public $showEditStandardModal = false;
    public $standardValueOptions = [];
    public $editingStandardData = [
        'captured_result_id' => null,
        'analyte_id' => null,
        'standard_id' => null,
        'standard_level' => 1,
        'analyte_name' => '',
        'previous_value' => '',
        'standard_value_type' => 1, // 1=Range, 2=Value
        'min' => '',
        'max' => '',
        'standard_valuetype' => '', // ID from standard_values
        'limit_measure' => '',
        'value' => '',
    ];

    public $parametersForm = [];
    public $modalLists = [
        'operators' => [],
        'methods' => [],
        'equipments' => [],
        'units' => [],
    ];
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
        
        // Automatically enter edit mode for the new row
        $this->editingRowIndex = count($this->sampleForms) - 1;
        
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
        
        // Automatically enter edit mode for the duplicated row
        $this->editingRowIndex = count($this->sampleForms) - 1;
        
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
     * Enable editing for a specific row
     */
    public function editRow($index)
    {
        $this->editingRowIndex = $index;
    }
    
    /**
     * Cancel editing (makes all rows read-only)
     */
    public function cancelEditRow()
    {
        $this->editingRowIndex = null;
        // Clear dropdown states
        $this->showAnalysisTypeDropdown = [];
        $this->analysisTypeSearch = '';
    }
    
    
    /**
     * Toggle analysis type selection for a specific row
     */
    public function toggleAnalysisType($index, $analysisTypeId)
    {
        if (!isset($this->sampleForms[$index])) {
            return;
        }
        
        if (!is_array($this->sampleForms[$index]['analysis_type_id'])) {
            $this->sampleForms[$index]['analysis_type_id'] = [];
        }
        
        $key = array_search($analysisTypeId, $this->sampleForms[$index]['analysis_type_id']);
        
        if ($key !== false) {
            // Remove if already selected
            unset($this->sampleForms[$index]['analysis_type_id'][$key]);
            $this->sampleForms[$index]['analysis_type_id'] = array_values($this->sampleForms[$index]['analysis_type_id']);
        } else {
            // Add if not selected
            $this->sampleForms[$index]['analysis_type_id'][] = $analysisTypeId;
        }
    }
    
    /**
     * Get filtered analysis types for a specific row
     */
    public function getFilteredAnalysisTypes($index)
    {
        $search = $this->analysisTypeSearch;
        
        if (empty($search)) {
            return $this->analysisTypes;
        }
        
        return array_filter($this->analysisTypes, function($type) use ($search) {
            return stripos($type['name'], $search) !== false || 
                   stripos($type['code'], $search) !== false;
        });
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
            
            // Exit edit mode after successful save
            $this->editingRowIndex = null;
            
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
     * Phase 5: View Parameters Modal - Show all captured results for a sample
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
            
            $this->uncertaintyRequired = $this->batch->require_mu == 1;

            // Fetch all captured results for this sample with relationships
            $capturedResults = DB::table('captured_results')
                ->where('sample_detail_code', $sampleCode)
                ->where('sample_header_id', $this->batch->id)
                ->orderBy('analysis_type_order')
                ->orderBy('parameters_order')
                ->get();
            
            if ($capturedResults->isEmpty()) {
                $this->sampleParameters = [];
                $this->showParametersModal = true;
                return;
            }
            
            // Enrich each result with additional data
            $parameters = [];
            foreach ($capturedResults as $result) {
                // Get analysis type
                $analysisType = AnalysisType::find($result->analysis_type_id);
                
                // Get operator
                $operator = \App\User::find($result->operator_id);
                
                // Get method
                $method = \App\AnalysisMethod::find($result->method_id);
                
                // Get LTM method (stored as simple value, not a model)
                $ltmMethod = null;
                
                // Get equipment
                $equipment = \App\Models\Equipments\Equipment::find($result->equipment_id);
                
                // Get analyte for reporting unit
                $analyte = \App\Analyte::find($result->analyte_id);
                
                // Get standard info
                $standardInfo = $this->getStandardInfo(
                    $result->main_standard_id,
                    $result->main_value
                );
                
                $secStandardInfo = $result->secondary_standard_id ? $this->getStandardInfo(
                    $result->secondary_standard_id,
                    $result->secondary_value
                ) : null;
                
                // Fetch limit data for evaluation
                $limitType = null;
                $limitLow = null;
                $limitHigh = null;
                
                if ($result->main_standard_id && $result->analyte_id) {
                    $stdAnalyte = \App\StandardAnalytes::where('standard_id', $result->main_standard_id)
                        ->where('analyte_id', $result->analyte_id)
                        ->first();
                    if ($stdAnalyte) {
                        $limitType = $stdAnalyte->standard_value_type;
                        $limitLow = $stdAnalyte->low;
                        $limitHigh = $stdAnalyte->high;
                    }
                }

                $parameters[$result->id] = [
                    'id' => $result->id,
                    'sample_code' => $result->sample_detail_code,
                    'analysis_type' => $analysisType->code ?? '-',
                    'analysis_type_id' => $result->analysis_type_id,
                    'analyte_code' => $result->analyte_code,
                    'analyte_id' => $result->analyte_id,
                    'analyte_name' => $analyte->name ?? $result->analyte_code,
                    'result_reporting_symbol' => $result->result_reporting_symbol,
                    'result' => $result->result,
                    'measure_uncertanity' => $result->measure_uncertanity,
                    'standard_value' => $standardInfo['display'] ?? '-',
                    'standard_id' => $result->main_standard_id,
                    'sec_standard_value' => $secStandardInfo['display'] ?? null,
                    'sec_standard_id' => $result->secondary_standard_id,
                    'remark' => $result->remark ?: '', 
                    'remark_is_manual' => $result->remark_is_manual,
                    'reporting_unit' => $result->reporting_unit_id,
                    'operator_name' => $operator->name ?? '-',
                    'operator_id' => $result->operator_id,
                    'method_name' => $method->name ?? '-',
                    'method_id' => $result->method_id,
                    'ltm_method_name' => $result->ltm_method_id ? 'LTM-' . $result->ltm_method_id : '-',
                    'ltm_method_id' => $result->ltm_method_id,
                    'equipment_name' => $equipment->name ?? '-',
                    'equipment_id' => $result->equipment_id,
                    'subcontracted' => $result->analyte_status_contracted,
                    'accredited' => $result->analyte_accredited,
                    'result_confirmation' => $result->result, // Initialize with same value
                    'limit_type' => $limitType,
                    'limit_low' => $limitLow,
                    'limit_high' => $limitHigh,
                    'standard_editable' => false,
                ];
            }
            
            // Populate form data for editing
            $this->parametersForm = $parameters;
            
            // Load dropdown lists if empty
            if (empty($this->modalLists['operators'])) {
                $this->modalLists['operators'] = \App\User::orderBy('name')->get();
                $this->modalLists['methods'] = \App\AnalysisMethod::orderBy('name')->get();
                $this->modalLists['equipments'] = \App\Models\Equipments\Equipment::orderBy('name')->get();
                $this->modalLists['units'] = \App\ReportingUnit::all();
            }
            
            // Load standard values for edit modal
            if (empty($this->standardValueOptions)) {
                $this->standardValueOptions = \App\StandardValue::all();
            }

            $this->sampleParameters = $parameters;
            $this->showParametersModal = true;
            
        } catch (\Exception $e) {
            Log::error('Error loading sample parameters: ' . $e->getMessage());
            session()->flash('error', 'Failed to load parameters: ' . $e->getMessage());
        }
    }

    /**
     * Handle parameter updates (Auto-Remark)
     */
    public function updatedParametersForm($value, $key)
    {
        $parts = explode('.', $key);
        // key format: parametersForm.123.result
        if (count($parts) === 3 && $parts[2] === 'result') {
            $id = $parts[1];
            $this->evaluateResult($id);
        }
    }

    /**
     * Evaluate result against standard
     */
    public function evaluateResult($id)
    {
        if (!isset($this->parametersForm[$id])) return;
        
        $data = $this->parametersForm[$id];
        $result = $data['result'];
        
        // Skip if manual remark
        if (!empty($data['remark_is_manual']) && $data['remark_is_manual'] == 1) {
             return;
        }

        if (!is_numeric($result)) {
             // Non-numeric handling could go here
             return;
        }
        
        $val = floatval($result);
        $remark = 'PASS';
        
        if ($data['limit_type']) {
            $low = floatval($data['limit_low']);
            $high = floatval($data['limit_high']);
            
            if ($data['limit_type'] == 'is_range') {
                if ($val < $low || $val > $high) $remark = 'FAIL';
            } elseif ($data['limit_type'] == 'is_min') {
                if ($val < $low) $remark = 'FAIL';
            } elseif ($data['limit_type'] == 'is_max') {
                if ($val > $high) $remark = 'FAIL';
            }
        }
        
        $this->parametersForm[$id]['remark'] = $remark;
    }

    /**
     * Toggle standard edit mode
     */
    public function toggleStandardEdit($id)
    {
        if (isset($this->parametersForm[$id])) {
            $this->parametersForm[$id]['standard_editable'] = !$this->parametersForm[$id]['standard_editable'];
        }
    }

    public function openEditStandardModal($id, $level = 1)
    {
        if (!isset($this->parametersForm[$id])) {
            $this->dispatch('notify', ['message' => "Error: Parameter ID $id not found in form.", 'type' => 'error']);
            return;
        }
        
        $param = $this->parametersForm[$id];
        $standardId = $level == 1 ? $param['standard_id'] : $param['sec_standard_id'];
        
        if (!$standardId || !$param['analyte_id']) {
            $this->dispatch('notify', ['message' => 'No standard or analyte linked.', 'type' => 'error']);
            return;
        }

        // Fetch StandardAnalyte
        $stdAnalyte = \App\StandardAnalytes::where('standard_id', $standardId)
            ->where('analyte_id', $param['analyte_id'])
            ->first();

        if (!$stdAnalyte) {
            // Should creating new one be allowed? Legacy implies "update", but if missing maybe create?
            // Legacy code creates new StandardAnalytes() if missing.
            $stdAnalyte = new \App\StandardAnalytes();
            $stdAnalyte->standard_id = $standardId;
            $stdAnalyte->analyte_id = $param['analyte_id'];
        }

        $this->editingStandardData = [
            'captured_result_id' => $id,
            'analyte_id' => $param['analyte_id'],
            'standard_id' => $standardId,
            'standard_level' => $level,
            'analyte_name' => $param['analyte_name'],
            'previous_value' => $level == 1 ? $param['standard_value'] : $param['sec_standard_value'],
            'standard_value_type' => $stdAnalyte->standard_value_type == 'is_range' ? 1 : 2,
            'min' => $stdAnalyte->low,
            'max' => $stdAnalyte->high,
            'standard_valuetype' => $stdAnalyte->standard_value_id,
            'limit_measure' => $stdAnalyte->value_type,
            'value' => $stdAnalyte->standard_is_value,
        ];
        
        $this->showEditStandardModal = true;
        $this->dispatch('show-edit-standard-modal');
    }

    public function saveStandardLimit()
    {
        $data = $this->editingStandardData;
        
        // Find or create
        $stdAnalyte = \App\StandardAnalytes::where('standard_id', $data['standard_id'])
            ->where('analyte_id', $data['analyte_id'])
            ->first();

        if (!$stdAnalyte) {
            $stdAnalyte = new \App\StandardAnalytes();
            $stdAnalyte->standard_id = $data['standard_id'];
            $stdAnalyte->analyte_id = $data['analyte_id'];
        }

        // Map inputs to model (legacy logic)
        $stdAnalyte->low = $data['min'];
        $stdAnalyte->high = $data['max'];
        $stdAnalyte->standard_value_id = $data['standard_valuetype'];
        $stdAnalyte->value_type = $data['limit_measure'];
        $stdAnalyte->standard_is_value = $data['value'];
        $stdAnalyte->standard_value_type = $data['standard_value_type'] == 1 ? 'is_range' : 'is_standard_value';
        $stdAnalyte->is_active = 1;
        $stdAnalyte->save();

        // Calculate display string (Legacy Logic)
        $newValue = 'NS';
        if ($data['standard_value_type'] == 1) {
            $newValue = $data['min'] . ' - ' . $data['max'];
        } else {
             // Logic: 2 && limit_measure == '' -> limit code
             //        2 && limit_measure != '' -> value . ' ' . limit_measure
             if ($data['limit_measure'] == '') {
                 $sv = \App\StandardValue::find($data['standard_valuetype']);
                 $newValue = $sv ? $sv->code : $newValue;
             } else {
                 $newValue = $data['value'] . ' ' . $data['limit_measure']; // e.g. "10 Max"
             }
        }

        // Update CapturedResult snapshot
        $updateField = $data['standard_level'] == 1 ? 'standard_value' : 'sec_standard_value';
        
        DB::table('captured_results')
            ->where('id', $data['captured_result_id'])
            ->update([$updateField => $newValue]);

        // Refresh view
        $this->viewParameters();
        $this->showEditStandardModal = false;
        $this->dispatch('hide-edit-standard-modal');
        
        // Re-evaluate current row result against new limits
        $this->evaluateResult($data['captured_result_id']);
    }

    /**
     * Save parameters from modal
     */
    public function saveParameters()
    {
        try {
            foreach ($this->parametersForm as $id => $data) {
                $updateData = [
                    'result' => $data['result'],
                    'measure_uncertanity' => $data['measure_uncertanity'],
                    'remark' => $data['remark'],
                    'reporting_unit_id' => $data['reporting_unit'],
                    'operator_id' => $data['operator_id'],
                    'method_id' => $data['method_id'],
                    'equipment_id' => $data['equipment_id'],
                    'analyte_status_contracted' => $data['subcontracted'] ? 1 : 0,
                    'analyte_accredited' => $data['accredited'] ? 1 : 0,
                    'updated_at' => now(),
                    // Mark manual remark if changed? For now just save.
                ];
                
                DB::table('captured_results')
                    ->where('id', $id)
                    ->update($updateData);
            }
            
            session()->flash('message', 'Parameters saved successfully.');
            $this->showParametersModal = false;
            
        } catch (\Exception $e) {
            Log::error('Error saving parameters: ' . $e->getMessage());
            session()->flash('error', 'Failed to save parameters: ' . $e->getMessage());
        }
    }
    
    /**
     * Get formatted standard info
     */
    private function getStandardInfo($standardId, $value)
    {
        if (!$standardId || !$value) {
            return ['display' => '-'];
        }
        
        try {
            $standard = Standards::find($standardId);
            return [
                'display' => ($standard->name ?? 'Standard') . ': ' . $value,
                'name' => $standard->name ?? '-',
                'value' => $value
            ];
        } catch (\Exception $e) {
            return ['display' => $value];
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
     * Open Comments & Interpretations modal for a sample
     */
    public function openCommentsModal($sampleId)
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
     * Open Interlab Transfer modal for a sample
     */
    public function openInterlabModal($sampleId, $sampleCode)
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
