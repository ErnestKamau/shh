<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionFormElement extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'submission_form_element_holder_id',
        'element_type',
        'label',
        'name',
        'placeholder',
        'help_text',
        'is_required',
        'is_readonly',
        'default_value',
        'validation_rules',
        'options',
        'calculation_formula',
        'conditional_logic',
        'sort_order',
        'mapping_table',
        'mapping_field',
        'is_mapped',
        'depends_on_type',
        'depends_on_field',
        'source_table',
        'source_field',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_readonly' => 'boolean',
        'validation_rules' => 'array',
        'options' => 'array',
        'conditional_logic' => 'array',
        'is_mapped' => 'boolean'
    ];

    /**
     * Get the element holder that owns this element
     */
    public function holder()
    {
        return $this->belongsTo(SubmissionFormElementHolder::class, 'submission_form_element_holder_id');
    }

    /**
     * Get the instance values for this element
     */
    public function instanceValues()
    {
        return $this->hasMany(SubmissionFormInstanceValue::class, 'submission_form_element_id');
    }

    /**
     * Check if this element is a field that accepts user input
     */
    public function isInputField()
    {
        return in_array($this->element_type, [
            'text', 'number', 'email', 'date', 'datetime', 
            'textarea', 'select', 'radio', 'checkbox', 'file', 'camera_photo', 'image_upload', 'signature',
            'client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select',
            'analysis_type_select', 'store_select', 'store_slot_select', 'sample_condition_select', 'standard_select',
            'zone_select',
            'depended_field', 'pricelist_viewer'
        ]);
    }

    /**
     * Check if this element is a calculated field
     */
    public function isCalculatedField()
    {
        return $this->element_type === 'calculation';
    }

    /**
     * Check if this element has options (select, radio, checkbox)
     */
    public function hasOptions()
    {
        return in_array($this->element_type, [
            'select', 'radio', 'checkbox', 
            'client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select',
            'analysis_type_select', 'store_select', 'store_slot_select', 'sample_condition_select', 'standard_select'
        ]) && !empty($this->options);
    }

    /**
     * Get the validation rules as Laravel validation array
     */
    public function getLaravelValidationRules()
    {
        $rules = [];

        if ($this->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Add type-specific validation
        switch ($this->element_type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'datetime':
                $rules[] = 'date';
                break;
            case 'file':
                $rules[] = 'file';
                break;
            case 'camera_photo':
                $rules[] = 'image';
                $rules[] = 'mimes:jpg,jpeg,png,webp';
                break;
        }

        // Add custom validation rules from the validation_rules JSON
        if (!empty($this->validation_rules)) {
            $rules = array_merge($rules, $this->validation_rules);
        }

        return $rules;
    }

    /**
     * Get formatted options for select/radio/checkbox elements
     */
    public function getFormattedOptions()
    {
        if (!$this->hasOptions()) {
            return [];
        }

        $options = [];
        foreach ($this->options as $option) {
            if (is_array($option) && isset($option['value'], $option['label'])) {
                $options[$option['value']] = $option['label'];
            } elseif (is_string($option)) {
                $options[$option] = $option;
            }
        }

        return $options;
    }

    /**
     * Check if a value is valid for this element
     */
    public function isValidValue($value)
    {
        $rules = $this->getLaravelValidationRules();
        
        $validator = validator(
            [$this->name => $value],
            [$this->name => $rules]
        );

        return !$validator->fails();
    }

    /**
     * Get the default value for this element
     */
    public function getDefaultValue()
    {
        if ($this->default_value !== null) {
            return $this->default_value;
        }

        // Return type-specific defaults
        switch ($this->element_type) {
            case 'checkbox':
                return false;
            case 'number':
                return 0;
            case 'date':
            case 'datetime':
                return null;
            default:
                return '';
        }
    }

    /**
     * Check if this element should be visible based on conditional logic
     */
    public function shouldBeVisible(array $formData = [])
    {
        if (empty($this->conditional_logic)) {
            return true;
        }

        // Simple conditional logic implementation
        // This can be expanded based on requirements
        foreach ($this->conditional_logic as $condition) {
            if (!isset($condition['field'], $condition['operator'], $condition['value'])) {
                continue;
            }

            $fieldValue = $formData[$condition['field']] ?? null;
            
            switch ($condition['operator']) {
                case 'equals':
                    if ($fieldValue != $condition['value']) {
                        return false;
                    }
                    break;
                case 'not_equals':
                    if ($fieldValue == $condition['value']) {
                        return false;
                    }
                    break;
                case 'contains':
                    if (strpos($fieldValue, $condition['value']) === false) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Scope to order elements by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope to filter by element type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('element_type', $type);
    }

    /**
     * Scope to filter required elements
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Get the next sort order for a new element in the same holder
     */
    public static function getNextSortOrder($holderId)
    {
        $maxSortOrder = static::where('submission_form_element_holder_id', $holderId)
                             ->max('sort_order');
        
        return ($maxSortOrder ?? 0) + 1;
    }

    /**
     * Check if this element is a custom dynamic element
     */
    public function isCustomDynamicElement()
    {
        return in_array($this->element_type, [
            'client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select',
            'analysis_type_select', 'store_select', 'store_slot_select', 'sample_condition_select', 'standard_select', 'sample_point_select', 'user_select', 'user_signature', 'zone_select', 'pricelist_viewer'
        ]);
    }

    /**
     * Get dynamic options for custom elements
     */
    public function getDynamicOptions($clientId = null, $sampleTypeId = null, $storeId = null)
    {
        switch ($this->element_type) {
            case 'client_select':
                return $this->getClientOptions();
            case 'sample_type_select':
                return $this->getSampleTypeOptions();
            case 'client_unit_select':
                return $this->getClientUnitOptions($clientId);
            case 'client_contact_select':
                return $this->getClientContactOptions($clientId);
            case 'analysis_type_select':
                return $this->getAnalysisTypeOptions($sampleTypeId);
            case 'store_select':
                return $this->getStoreOptions();
            case 'store_slot_select':
                return $this->getStoreSlotOptions($storeId);
            case 'sample_condition_select':
                return $this->getSampleConditionOptions($sampleTypeId);
            case 'standard_select':
                return $this->getStandardOptions();
            case 'sample_point_select':
                return $this->getSamplePointOptions($clientId);
            case 'user_select':
                return $this->getUserOptions();
            case 'zone_select':
                return $this->getZoneOptions();
            default:
                return [];
        }
    }

    /**
     * Get client options
     */
    private function getClientOptions()
    {
        $clients = \App\Models\CRM\CRMCustomer::where('active', 1)
            ->where('company_id', getUserCompany())
            ->orderBy('name')
            ->get();

        $options = [];
        foreach ($clients as $client) {
            $options[] = [
                'value' => $client->id,
                'label' => $client->name
            ];
        }
        return $options;
    }

    /**
     * Get sample type options
     */
    private function getSampleTypeOptions()
    {
        $sampleTypes = \App\SampleType::orderBy('name')->get();

        $options = [];
        foreach ($sampleTypes as $sampleType) {
            $options[] = [
                'value' => $sampleType->id,
                'label' => $sampleType->name
            ];
        }
        return $options;
    }

    /**
     * Get client unit options for a specific client
     */
    private function getClientUnitOptions($clientId)
    {
        if (!$clientId) {
            return [];
        }

        $units = \App\Models\CRM\CRMCompanyUnit::where('crm_customer_id', $clientId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();

        $options = [];
        foreach ($units as $unit) {
            $options[] = [
                'value' => $unit->id,
                'label' => $unit->name
            ];
        }
        return $options;
    }

    /**
     * Get client contact options for a specific client
     */
    private function getClientContactOptions($clientId)
    {
        if (!$clientId) {
            return [];
        }

        $contacts = \App\Models\CRM\CustomerContact::where('crm_customer_id', $clientId)
            ->where('active', 1)
            ->orderBy('first_name')
            ->get();

        $options = [];
        foreach ($contacts as $contact) {
            $fullName = trim($contact->first_name . ' ' . $contact->middle_name . ' ' . $contact->last_name);
            $options[] = [
                'value' => $contact->id,
                'label' => $fullName . ' (' . $contact->email . ')'
            ];
        }
        return $options;
    }

    /**
     * Get analysis type options for a specific sample type
     */
    private function getAnalysisTypeOptions($sampleTypeId)
    {
        if (!$sampleTypeId) {
            return [];
        }

        $analysisTypes = \App\AnalysisType::where('sample_type_id', $sampleTypeId)
            ->where('active', 1)
            ->where('company_id', getUserCompany())
            ->orderBy('name')
            ->get();

        $options = [];
        foreach ($analysisTypes as $analysisType) {
            $options[] = [
                'value' => $analysisType->id,
                'label' => $analysisType->name . ' (' . $analysisType->code . ')'
            ];
        }
        return $options;
    }

    /**
     * Get store options
     */
    private function getStoreOptions()
    {
        $stores = \App\InventoryStore::where('company_id', getUserCompany())
            ->orderBy('name')
            ->get();

        $options = [];
        foreach ($stores as $store) {
            $options[] = [
                'value' => $store->id,
                'label' => $store->name
            ];
        }
        return $options;
    }

    /**
     * Get store slot options for a specific store
     */
    private function getStoreSlotOptions($storeId)
    {
        if (!$storeId) {
            return [];
        }

        $slots = \App\InventoryStoreSlot::where('inventory_store_id', $storeId)
            ->orderBy('name')
            ->get();

        $options = [];
        foreach ($slots as $slot) {
            $options[] = [
                'value' => $slot->id,
                'label' => $slot->name
            ];
        }
        return $options;
    }

    /**
     * Get sample condition options (all active conditions)
     */
    private function getSampleConditionOptions($sampleTypeId = null)
    {
        $sampleConditions = \App\SampleCondition::where('active', 1)
            ->orderBy('name')
            ->get();

        $options = [];
        foreach ($sampleConditions as $condition) {
            $options[] = [
                'value' => $condition->id,
                'label' => $condition->name . ($condition->short_name ? ' (' . $condition->short_name . ')' : '')
            ];
        }
        return $options;
    }

    /**
     * Get standard options
     */
    private function getStandardOptions()
    {
        $standards = \App\Standards::where('status', 1)
            ->orderBy('name')
            ->get();

        $options = [];
        foreach ($standards as $standard) {
            $options[] = [
                'value' => $standard->id,
                'label' => $standard->name . ' (' . $standard->code . ')'
            ];
        }
        return $options;
    }

    /**
     * Get user options scoped to the current company
     */
    private function getUserOptions()
    {
        $query = \App\User::query()
            ->where('active', 1)
            ->where('is_client', 0)
            ->whereNull('supplier_id')
            ->orderBy('name');

        if (function_exists('getUserCompany') && auth()->check()) {
            $query->where('company_id', getUserCompany());
        }

        $users = $query->get(['id', 'name', 'email']);

        $options = [];
        foreach ($users as $user) {
            $label = $user->name;
            if (!empty($user->email)) {
                $label .= ' (' . $user->email . ')';
            }

            $options[] = [
                'value' => $user->id,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function getZoneOptions(): array
    {
        $zones = \App\Zone::query()->orderBy('value')->get();

        $options = [];
        foreach ($zones as $zone) {
            $label = (string) $zone->value;
            if (! empty($zone->key)) {
                $label = $zone->key . ' — ' . $label;
            }
            if (! empty($zone->description)) {
                $label .= ' (' . \Illuminate\Support\Str::limit((string) $zone->description, 80) . ')';
            }

            $options[] = [
                'value' => $zone->id,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * Get sample point options based on client unit
     */
    private function getSamplePointOptions($clientUnitId = null)
    {
        if (!$clientUnitId) {
            return [];
        }

        $samplePoints = \App\Models\CRM\SamplePoint::where('active', 1)
            ->where('crm_company_unit_id', $clientUnitId)
            ->orderBy('name')
            ->get();

        $options = [];
        foreach ($samplePoints as $samplePoint) {
            $options[] = [
                'value' => $samplePoint->id,
                'label' => $samplePoint->name
            ];
        }
        return $options;
    }

    /**
     * Check if this element is mapped to a database field
     */
    public function isMapped()
    {
        return $this->is_mapped && !empty($this->mapping_table) && !empty($this->mapping_field);
    }

    /**
     * Get the mapping configuration
     */
    public function getMappingConfig()
    {
        if (!$this->isMapped()) {
            return null;
        }

        return [
            'table' => $this->mapping_table,
            'field' => $this->mapping_field
        ];
    }

    /**
     * Get available mapping tables
     */
    public static function getMappingTables()
    {
        return [
            'sample_headers' => 'Sample Headers',
            'sample_details' => 'Sample Details'
        ];
    }

    /**
     * Get available fields for a mapping table
     */
    public static function getMappingFields($table)
    {
        $fields = [];
        
        switch ($table) {
            case 'sample_headers':
                $fields = [
                    'batch_code' => 'Batch Code',
                    'receipt_date' => 'Receipt Date',
                    'date_collected' => 'Date Collected',
                    'crm_customer_id' => 'CRM Customer ID',
                    'crm_unit_name' => 'CRM Unit Name',
                    'sample_type_id' => 'Sample Type ID',
                    'reference_number' => 'Reference Number',
                    'status' => 'Status',
                    'is_routine' => 'Is Routine',
                    'routine_frequency' => 'Routine Frequency',
                    'is_amendment' => 'Is Amendment',
                    'schedule_sent' => 'Schedule Sent',
                    'sample_tracking_stage' => 'Sample Tracking Stage',
                    'description' => 'Description',
                    'document_number' => 'Document Number',
                    'importer_address' => 'Importer Address',
                    'receiving_officer' => 'Receiving Officer',
                    'sampling_officer' => 'Sampling Officer',
                    'priority' => 'Priority',
                    'reason_for_submission' => 'Reason for Submission',
                    'how_sample_was_obtained' => 'How Sample Was Obtained',
                    'specialist_analyst_id' => 'Specialist Analyst ID',
                    'declared_commodity_code' => 'Declared Commodity Code',
                    'net_quantity_and_unit_of_quantity' => 'Net Quantity and Unit of Quantity',
                    'sample_appearance_description' => 'Sample Appearance Description',
                    'use_of_goods' => 'Use of Goods',
                    'kra_office_ref' => 'KRA Office Reference',
                    'kra_office_station' => 'KRA Office Station',
                    'where_sample_was_obtained' => 'Where Sample Was Obtained',
                    'declared_amount' => 'Declared Amount',
                    'final_declared_amount' => 'Final Declared Amount',
                    'radio_active_levels' => 'Radio Active Levels',
                    'date_expected' => 'Date Expected',
                    'invoice_id' => 'Invoice ID',
                    'verify_user_id' => 'Verify User ID',
                    'approve_user_id' => 'Approve User ID',
                    'processing_date' => 'Processing Date',
                    'approval_date' => 'Approval Date',
                    'email_date' => 'Email Date',
                    'payment_date' => 'Payment Date',
                    'isactive' => 'Is Active',
                    'ammendment_number' => 'Amendment Number',
                    'customer_paid' => 'Customer Paid',
                    'payment_reference_no' => 'Payment Reference No',
                    'verification_email_date' => 'Verification Email Date',
                    'approval_email_date' => 'Approval Email Date',
                    'sampling_officer_name' => 'Sampling Officer Name',
                    'receiving_officer_name' => 'Receiving Officer Name',
                    'submit_by' => 'Submit By',
                    'batch_report_url' => 'Batch Report URL',
                    'current_account_status' => 'Current Account Status',
                    'quote_id' => 'Quote ID',
                    'user_agreement' => 'User Agreement',
                    'in_ammendment_proccess' => 'In Amendment Process (Old)',
                    'in_ammendment_process' => 'In Amendment Process',
                    'begin_proccess' => 'Begin Process (Old)',
                    'begin_process' => 'Begin Process',
                    'week' => 'Week',
                    'year' => 'Year',
                    'batch_type' => 'Batch Type',
                    'import_sample' => 'Import Sample',
                    'packlist_proccessed' => 'Packlist Processed',
                    'require_mu' => 'Require MU',
                    'is_exception' => 'Is Exception',
                    'payment_done_by' => 'Payment Done By',
                    'sampled_by_company_personnel' => 'Sampled by Company Personnel',
                    'in_lab_date' => 'In Lab Date',
                    'crm_unit_id' => 'CRM Unit ID',
                    'company_sub_unit_id' => 'Company Sub Unit ID',
                    'other_id' => 'Other ID',
                    'batch_scope' => 'Batch Scope',
                    'customer_survey' => 'Customer Survey',
                    'is_qc_batch' => 'Is QC Batch',
                    'qc_type_id' => 'QC Type ID',
                    'qc_scheme_id' => 'QC Scheme ID',
                    'repeat_batch_id' => 'Repeat Batch ID',
                    'repeat_sample_id' => 'Repeat Sample ID',
                    'quote_no' => 'Quote No',
                    'lab_capable' => 'Lab Capable',
                    'batch_subcontracted_client_approval' => 'Batch Subcontracted Client Approval',
                    'can_be_subcontracted' => 'Can Be Subcontracted',
                    'client_instruction_clear' => 'Client Instruction Clear',
                    'batch_instructions' => 'Batch Instructions',
                    'condition_quality_sample' => 'Condition Quality Sample',
                    'declaration_customer_approval_date' => 'Declaration Customer Approval Date',
                    'declaration_customer_signature' => 'Declaration Customer Signature',
                    'declaration_customer_contact_name' => 'Declaration Customer Contact Name',
                    'declaration_customer_review_approval_date' => 'Declaration Customer Review Approval Date',
                    'invoice_amount' => 'Invoice Amount',
                    'sampling_method_id' => 'Sampling Method ID',
                    'days_of_analysis' => 'Days of Analysis',
                    'schedule_analysis_sent' => 'Schedule Analysis Sent',
                    'report_verified_date' => 'Report Verified Date',
                    'lab_section_ids' => 'Lab Section IDs',
                    'c_focus_ids_clustered' => 'C Focus IDs Clustered',
                    'cluster_amount' => 'Cluster Amount',
                    'cluster_balance' => 'Cluster Balance',
                    'cluster_vat' => 'Cluster VAT',
                    'cluster_amount_paid' => 'Cluster Amount Paid',
                    'crm_contact_id' => 'CRM Contact ID',
                    'report_status' => 'Report Status',
                    'prelim_batch_status' => 'Prelim Batch Status',
                    'prelim_report_status' => 'Prelim Report Status',
                    'schedule_analysis_sender' => 'Schedule Analysis Sender',
                    'invoice_number' => 'Invoice Number',
                    'case_id' => 'Case ID',
                    'has_kenas' => 'Has KENAS',
                    'lab_id' => 'Lab ID',
                    'retention_date' => 'Retention Date',
                    'batch_report_online_url' => 'Batch Report Online URL'
                ];
                break;
                
            case 'sample_details':
                $fields = [
                    'sample_code' => 'Sample Code',
                    'analysis_type_id' => 'Analysis Type ID',
                    'sample_condition_id' => 'Sample Condition ID',
                    'barcode' => 'Barcode',
                    'comments' => 'Comments',
                    'gps' => 'GPS',
                    'photo_url' => 'Photo URL',
                    'sample_header_id' => 'Sample Header ID',
                    'sample_point_id' => 'Sample Point ID',
                    'company_sub_unit_id' => 'Company Sub Unit ID',
                    'company_product_id' => 'Company Product ID',
                    'main_body' => 'Main Body',
                    'header_body' => 'Header Body',
                    'is_ammendment' => 'Is Amendment',
                    'ammendment_number' => 'Amendment Number',
                    'main_standard' => 'Main Standard',
                    'secondary_standard' => 'Secondary Standard',
                    'short_code' => 'Short Code',
                    'material_status' => 'Material Status',
                    'third_standard_id' => 'Third Standard ID',
                    'sample_no' => 'Sample No',
                    'no_of_samples' => 'No of Samples',
                    'no_of_pots_plants' => 'No of Pots/Plants',
                    'standard_tests' => 'Standard Tests',
                    'compartiment_lot' => 'Compartment Lot',
                    'results' => 'Results',
                    'lab_sub_no' => 'Lab Sub No',
                    'store_id' => 'Store ID',
                    'store_slot_id' => 'Store Slot ID',
                    'quantity' => 'Quantity',
                    'reporting_unit_id' => 'Reporting Unit ID',
                    'mfg_date' => 'Manufacturing Date',
                    'expiry_date' => 'Expiry Date',
                    'batch_lot_no' => 'Batch/Lot No',
                    'coa_number_target' => 'COA Number Target',
                    'coa_number' => 'COA Number',
                    'comment_scope_type' => 'Comment Scope Type',
                    'lab_id' => 'Lab ID',
                    'disposal_date' => 'Disposal Date',
                    'sample_report_code' => 'Sample Report Code',
                    'notes_body' => 'Notes Body',
                    'is_disposed' => 'Is Disposed'
                ];
                break;
        }
        
        return $fields;
    }

    /**
     * Clone this element
     * 
     * @param int|null $newHolderId Optional holder ID for the cloned element
     * @return SubmissionFormElement
     */
    public function clone($newHolderId = null)
    {
        $clonedElement = $this->replicate();
        $clonedElement->submission_form_element_holder_id = $newHolderId ?: $this->submission_form_element_holder_id;
        $clonedElement->name = $this->generateCloneName($this->name);
        $clonedElement->label = $this->label . ' (Copy)';
        $clonedElement->sort_order = static::getNextSortOrder($clonedElement->submission_form_element_holder_id);
        $clonedElement->save();

        return $clonedElement;
    }

    /**
     * Generate a unique name for cloned element
     * 
     * @param string $originalName
     * @return string
     */
    private function generateCloneName($originalName)
    {
        $baseName = $originalName . '_copy';
        $name = $baseName;
        $counter = 1;

        // Get the form ID to check for uniqueness within the same form
        $formId = $this->holder->section->submission_form_id;

        while (static::whereHas('holder.section', function($query) use ($formId) {
            $query->where('submission_form_id', $formId);
        })->where('name', $name)->exists()) {
            $name = $originalName . '_copy_' . $counter;
            $counter++;
        }

        return $name;
    }
}