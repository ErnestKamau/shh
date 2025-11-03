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
        'company_id'
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
}