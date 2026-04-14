<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionFormSection extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;


    protected $fillable = [
        'submission_form_id',
        'title',
        'description',
        'section_type',
        'sort_order'
    ];

    /**
     * Get the submission form that owns this section
     */
    public function submissionForm()
    {
        return $this->belongsTo(SubmissionForm::class);
    }

    /**
     * Get the element holders for this section
     */
    public function elementHolders()
    {
        return $this->hasMany(SubmissionFormElementHolder::class, 'submission_form_section_id')
                    ->orderBy('sort_order');
    }

    /**
     * Get all elements across all element holders in this section
     */
    public function elements()
    {
        return $this->hasManyThrough(
            SubmissionFormElement::class,
            SubmissionFormElementHolder::class,
            'submission_form_section_id',
            'submission_form_element_holder_id'
        )->orderBy('submission_form_element_holders.sort_order')
         ->orderBy('submission_form_elements.sort_order');
    }

    /**
     * Get the total number of element holders in this section
     */
    public function getElementHolderCount()
    {
        return $this->elementHolders()->count();
    }

    /**
     * Get the total number of elements in this section
     */
    public function getElementCount()
    {
        return $this->elementHolders()
            ->with('elements')
            ->get()
            ->sum(function ($holder) {
                return $holder->elements->count();
            });
    }

    /**
     * Check if this section has any element holders
     */
    public function hasElementHolders()
    {
        return $this->elementHolders()->exists();
    }

    /**
     * Check if this section has any elements
     */
    public function hasElements()
    {
        return $this->elementHolders()
            ->with('elements')
            ->get()
            ->sum(function ($holder) {
                return $holder->elements->count();
            }) > 0;
    }

    /**
     * Check if this section is a rows section
     */
    public function isRowsSection()
    {
        return $this->section_type === 'rows_section';
    }

    /**
     * Check if this section is a regular section
     */
    public function isRegularSection()
    {
        return $this->section_type === 'regular' || empty($this->section_type);
    }

    /**
     * Get the template element holder for rows section
     * This is the first element holder that serves as a template for new rows
     */
    public function getTemplateElementHolder()
    {
        if (!$this->isRowsSection()) {
            return null;
        }
        
        return $this->elementHolders()->first();
    }

    /**
     * Scope to order sections by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the next sort order for a new section in the same form
     */
    public static function getNextSortOrder(int $submissionFormId)
    {
        $maxSortOrder = static::where('submission_form_id', $submissionFormId)
                             ->max('sort_order');
        
        return ($maxSortOrder ?? 0) + 1;
    }

    /**
     * Clone this section with all its element holders and elements
     * 
     * @param string|null $newTitle Optional custom title for the cloned section
     * @return SubmissionFormSection
     */
    public function clone($newTitle = null)
    {
        $clonedSection = $this->replicate();
        $clonedSection->title = $newTitle ?: $this->generateCloneTitle($this->title);
        $clonedSection->sort_order = static::getNextSortOrder($this->submission_form_id);
        $clonedSection->save();

        // Clone all element holders and their elements
        foreach ($this->elementHolders as $holder) {
            $clonedHolder = $holder->clone();
            $clonedHolder->submission_form_section_id = $clonedSection->id;
            $clonedHolder->save();
        }

        return $clonedSection->load('elementHolders.elements');
    }

    /**
     * Generate a unique title for cloned section
     * 
     * @param string $originalTitle
     * @return string
     */
    private function generateCloneTitle($originalTitle)
    {
        $baseTitle = $originalTitle . ' (Copy)';
        $title = $baseTitle;
        $counter = 1;

        while (static::where('submission_form_id', $this->submission_form_id)
                    ->where('title', $title)
                    ->exists()) {
            $title = $originalTitle . ' (Copy ' . $counter . ')';
            $counter++;
        }

        return $title;
    }
}