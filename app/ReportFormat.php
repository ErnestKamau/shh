<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ReportFormat extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'report_name',
        'report_code',
        'is_active',
        'company_id',
        'results_display_type'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the sample types that use this report format.
     */
    public function sampleTypes()
    {
        return $this->hasMany('App\SampleType', 'report_format_id');
    }

    /**
     * Get the lab section configurations that use this report format.
     */
    public function labSectionConfigs()
    {
        return $this->hasMany(\App\Models\LabSectionReportConfig::class, 'report_format_id');
    }

    /**
     * Scope a query to only include active report formats.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include report formats for a specific company.
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Get the configurable sections for this report format.
     */
    public function sections()
    {
        return $this->hasMany(ReportFormatSection::class, 'report_format_id');
    }

    /**
     * Get the static text details for this report format.
     */
    public function details()
    {
        return $this->hasMany(ReportFormatDetail::class, 'report_format_id');
    }

    /**
     * Helper to get a detail value by key.
     */
    public function getDetail($key, $default = null)
    {
        $detail = $this->details->where('key_name', $key)->first();
        return $detail ? $detail->text_value : $default;
    }

    /**
     * Helper to check if a section is visible.
     */
    public function isSectionVisible($sectionName)
    {
        $section = $this->sections->where('section_name', $sectionName)->first();
        return $section ? $section->is_visible : false;
    }
}
