<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Models\SubmissionForm;

class CertificateTemplate extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'is_published',
        'is_active',
        'version',
        'page_settings',
        'header_settings',
        'footer_settings',
        'submission_form_id',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'page_settings' => 'array',
        'header_settings' => 'array',
        'footer_settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the submission form this template belongs to.
     */
    public function submissionForm(): BelongsTo
    {
        return $this->belongsTo(SubmissionForm::class);
    }

    /**
     * Get the user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the sections for this template.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(CertificateTemplateSection::class)->orderBy('sort_order');
    }

    /**
     * Get the root sections (sections without parent) for this template.
     */
    public function rootSections(): HasMany
    {
        return $this->hasMany(CertificateTemplateSection::class)->whereNull('parent_section_id')->orderBy('sort_order');
    }

    /**
     * Get the reports generated from this template.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(CertificateTemplateReport::class);
    }

    /**
     * Scope a query to only include published templates.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include published and active templates.
     */
    public function scopePublishedAndActive($query)
    {
        return $query->where('is_published', true)->where('is_active', true);
    }

    /**
     * Get the total number of sections in this template.
     */
    public function getSectionsCountAttribute(): int
    {
        return $this->sections()->count();
    }

    /**
     * Get the total number of elements in this template.
     */
    public function getElementsCountAttribute(): int
    {
        $directElements = $this->sections()->withCount('elements')->get()->sum('elements_count');
        $holderElements = DB::table('certificate_template_elements')
            ->join('certificate_template_element_holders', 'certificate_template_elements.certificate_template_element_holder_id', '=', 'certificate_template_element_holders.id')
            ->join('certificate_template_sections', 'certificate_template_element_holders.certificate_template_section_id', '=', 'certificate_template_sections.id')
            ->where('certificate_template_sections.certificate_template_id', $this->id)
            ->whereNotNull('certificate_template_elements.certificate_template_element_holder_id')
            ->count();
        
        return $directElements + $holderElements;
    }

    /**
     * Check if this template has any sections.
     */
    public function hasSections(): bool
    {
        return $this->sections()->exists();
    }

    /**
     * Check if this template is ready for publishing.
     */
    public function isReadyForPublishing(): bool
    {
        return $this->is_active && $this->hasSections();
    }
}
