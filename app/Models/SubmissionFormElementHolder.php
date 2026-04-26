<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionFormElementHolder extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;


    protected $fillable = [
        'submission_form_section_id',
        'holder_type',
        'max_elements',
        'sort_order'
    ];

    /**
     * Get the section that owns this element holder
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormSection::class, 'submission_form_section_id');
    }

    /**
     * Get the elements for this holder
     */
    public function elements()
    {
        return $this->hasMany(SubmissionFormElement::class, 'submission_form_element_holder_id')
                    ->orderBy('sort_order');
    }

    /**
     * Get the current number of elements in this holder
     */
    public function getCurrentElementCount(): int
    {
        return $this->elements()->count();
    }

    /**
     * Check if this holder can accept more elements
     */
    public function canAddMoreElements(): bool
    {
        return $this->getCurrentElementCount() < $this->max_elements;
    }

    /**
     * Get the number of available slots for new elements
     */
    public function getAvailableSlots(): int
    {
        return max(0, $this->max_elements - $this->getCurrentElementCount());
    }

    /**
     * Check if this holder is at capacity
     */
    public function isAtCapacity(): bool
    {
        return $this->getCurrentElementCount() >= $this->max_elements;
    }

    /**
     * Check if this holder is a field type holder
     */
    public function isFieldHolder(): bool
    {
        return $this->holder_type === 'field';
    }

    /**
     * Check if this holder is a text type holder
     */
    public function isTextHolder(): bool
    {
        return $this->holder_type === 'text';
    }

    /**
     * Validate that we can add an element to this holder
     */
    public function validateElementAddition(): bool
    {
        if ($this->isAtCapacity()) {
            throw new \Exception("Element holder is at maximum capacity ({$this->max_elements} elements)");
        }

        return true;
    }

    /**
     * Scope to order holders by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope to filter by holder type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('holder_type', $type);
    }

    /**
     * Get the next sort order for a new holder in the same section
     */
    public static function getNextSortOrder(int $sectionId): int
    {
        $maxSortOrder = static::where('submission_form_section_id', $sectionId)
                             ->max('sort_order');
        
        return ($maxSortOrder ?? 0) + 1;
    }

    /**
     * Get holder statistics
     */
    public function getStatistics(): array
    {
        return [
            'current_elements' => $this->getCurrentElementCount(),
            'max_elements' => $this->max_elements,
            'available_slots' => $this->getAvailableSlots(),
            'is_at_capacity' => $this->isAtCapacity(),
            'holder_type' => $this->holder_type,
        ];
    }

    /**
     * Clone this element holder with all its elements
     * 
     * @param int|null $newSectionId Optional section ID for the cloned holder
     * @return SubmissionFormElementHolder
     */
    public function clone($newSectionId = null)
    {
        $clonedHolder = $this->replicate();
        $clonedHolder->submission_form_section_id = $newSectionId ?: $this->submission_form_section_id;
        $clonedHolder->sort_order = static::getNextSortOrder($clonedHolder->submission_form_section_id);
        $clonedHolder->save();

        // Clone all elements
        foreach ($this->elements as $element) {
            $clonedElement = $element->clone();
            $clonedElement->submission_form_element_holder_id = $clonedHolder->id;
            $clonedElement->save();
        }

        return $clonedHolder->load('elements');
    }
}