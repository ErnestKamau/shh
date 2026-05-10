<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SubmissionFormInstanceValue extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;


    protected $fillable = [
        'submission_form_instance_id',
        'submission_form_element_id',
        'array_index',
        'value',
        'file_path'
    ];

    protected $casts = [
        'value' => 'encrypted',
    ];

    /**
     * Get the instance that owns this value
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    /**
     * Get the element that this value belongs to
     */
    public function element(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormElement::class, 'submission_form_element_id');
    }

    /**
     * Get the formatted value based on the element type
     */
    public function getFormattedValue()
    {
        $element = $this->element;
        
        if (!$element) {
            return $this->value;
        }
        
        switch ($element->element_type) {
            case 'date':
                return $this->value ? Carbon::parse($this->value)->format('Y-m-d') : null;
            
            case 'datetime':
                return $this->value ? Carbon::parse($this->value)->format('Y-m-d H:i:s') : null;
            
            case 'file':
                return $this->file_path;
            case 'camera_photo':
                return $this->file_path;
            
            case 'checkbox':
                return $this->value === '1' || $this->value === 'true' || $this->value === true ? 'Yes' : 'No';
            
            case 'select':
            case 'radio':
                // Get the label for the selected option
                if ($element->hasOptions()) {
                    $options = $element->getFormattedOptions();
                    return $options[$this->value] ?? $this->value;
                }
                return $this->value;
            
            case 'number':
                return is_numeric($this->value) ? (float) $this->value : $this->value;
            
            default:
                return $this->value;
        }
    }

    /**
     * Get the raw value (unformatted)
     */
    public function getRawValue()
    {
        return $this->value;
    }

    /**
     * Get the display value for UI presentation
     */
    public function getDisplayValue(): string
    {
        $formatted = $this->getFormattedValue();
        
        if ($formatted === null || $formatted === '') {
            return '-';
        }
        
        // Handle file uploads
        if ($this->element && in_array($this->element->element_type, ['file', 'camera_photo'], true) && $this->file_path) {
            return basename($this->file_path);
        }
        
        return (string) $formatted;
    }

    /**
     * Check if this value is empty
     */
    public function isEmpty(): bool
    {
        return $this->value === null || 
               $this->value === '' || 
               ($this->element && in_array($this->element->element_type, ['file', 'camera_photo'], true) && empty($this->file_path));
    }

    /**
     * Check if this value is valid according to the element's validation rules
     */
    public function isValid(): bool
    {
        if (!$this->element) {
            return false;
        }
        
        return $this->element->isValidValue($this->value);
    }

    /**
     * Get validation errors for this value
     */
    public function getValidationErrors(): array
    {
        if (!$this->element) {
            return ['Element not found'];
        }
        
        $rules = $this->element->getLaravelValidationRules();
        
        $validator = validator(
            [$this->element->name => $this->value],
            [$this->element->name => $rules]
        );
        
        return $validator->errors()->get($this->element->name);
    }

    /**
     * Set the value with type conversion based on element type
     */
    public function setValue($value): void
    {
        if (!$this->element) {
            $this->value = $value;
            return;
        }
        
        switch ($this->element->element_type) {
            case 'checkbox':
                $this->value = $value ? '1' : '0';
                break;
            
            case 'number':
                $this->value = is_numeric($value) ? (string) $value : $value;
                break;
            
            case 'date':
                if ($value instanceof Carbon) {
                    $this->value = $value->format('Y-m-d');
                } elseif (is_string($value) && !empty($value)) {
                    try {
                        $this->value = Carbon::parse($value)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $this->value = $value;
                    }
                } else {
                    $this->value = $value;
                }
                break;
            
            case 'datetime':
                if ($value instanceof Carbon) {
                    $this->value = $value->format('Y-m-d H:i:s');
                } elseif (is_string($value) && !empty($value)) {
                    try {
                        $this->value = Carbon::parse($value)->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        $this->value = $value;
                    }
                } else {
                    $this->value = $value;
                }
                break;
            
            default:
                $this->value = $value;
                break;
        }
    }

    /**
     * Scope to filter by element
     */
    public function scopeForElement($query, int $elementId)
    {
        return $query->where('submission_form_element_id', $elementId);
    }

    /**
     * Scope to filter by instance
     */
    public function scopeForInstance($query, int $instanceId)
    {
        return $query->where('submission_form_instance_id', $instanceId);
    }

    /**
     * Scope to filter non-empty values
     */
    public function scopeNotEmpty($query)
    {
        return $query->where(function($q) {
            $q->whereNotNull('value')
              ->where('value', '!=', '')
              ->orWhereNotNull('file_path');
        });
    }
}