<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionFormElement extends Model
{
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
        'sort_order'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_readonly' => 'boolean',
        'validation_rules' => 'array',
        'options' => 'array',
        'conditional_logic' => 'array'
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
            'textarea', 'select', 'radio', 'checkbox', 'file', 'signature',
            'client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select'
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
            'client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select'
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
            'client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select'
        ]);
    }

    /**
     * Get dynamic options for custom elements
     */
    public function getDynamicOptions($clientId = null)
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
}