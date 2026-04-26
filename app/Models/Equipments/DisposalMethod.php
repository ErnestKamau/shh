<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class DisposalMethod extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'equipment_disposal_methods';

    protected $fillable = [
        'method',
        'display_name',
        'description',
        'applicable_categories',
        'regulatory_requirements',
        'required_documentation',
        'approved_vendors',
        'safety_requirements',
        'environmental_compliance',
        'is_active',
    ];

    protected $casts = [
        'applicable_categories' => 'array',
        'regulatory_requirements' => 'array',
        'required_documentation' => 'array',
        'approved_vendors' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if method is applicable for a given category
     *
     * @param string $category
     * @return bool
     */
    public function isApplicableFor(string $category): bool
    {
        if (!$this->applicable_categories) {
            return false;
        }

        return in_array($category, $this->applicable_categories);
    }

    /**
     * Get required permits/certifications
     *
     * @return array
     */
    public function getRequiredPermits(): array
    {
        return $this->regulatory_requirements['permits'] ?? [];
    }

    /**
     * Get required documentation list
     *
     * @return array
     */
    public function getRequiredDocs(): array
    {
        return $this->required_documentation ?? [];
    }

    /**
     * Check if method has approved vendors
     *
     * @return bool
     */
    public function hasApprovedVendors(): bool
    {
        return !empty($this->approved_vendors);
    }

    /**
     * Check if method requires special safety measures
     *
     * @return bool
     */
    public function requiresSafetyMeasures(): bool
    {
        return !empty($this->safety_requirements);
    }

    /**
     * Scope to get active methods
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCategory($query, string $category)
    {
        return $query->whereJsonContains('applicable_categories', $category);
    }
}


