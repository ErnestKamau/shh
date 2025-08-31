<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionFormSection extends Model
{

    protected $fillable = [
        'submission_form_id',
        'title',
        'description',
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
        return $this->getElementCount() > 0;
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
}