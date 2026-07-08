<?php

namespace App\Models\RiskManagement;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class RiskConfigurationOption extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'risk_configuration_options';

    protected $fillable = [
        'option_type',
        'code',
        'name',
        'description',
        'color_code',
        'order_index',
        'is_active',
        'metadata',
        'company_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'is_active' => 'boolean',
        'company_id' => 'string',
    ];

    /**
     * Normalize metadata, including legacy double-encoded JSON.
     *
     * @return array<string, mixed>
     */
    public function getMetadataAttribute(mixed $value): array
    {
        return normalizeRiskConfigurationMetadata($value);
    }

    public function setMetadataAttribute(mixed $value): void
    {
        if (is_array($value)) {
            $this->attributes['metadata'] = json_encode($value);

            return;
        }

        if (is_string($value) && $value !== '') {
            $normalized = normalizeRiskConfigurationMetadata($value);
            $this->attributes['metadata'] = json_encode($normalized);

            return;
        }

        $this->attributes['metadata'] = null;
    }

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForType(Builder $query, string $type): Builder
    {
        return $query->where('option_type', $type);
    }

    public function scopeForCompany(Builder $query, int|string|null $companyId = null): Builder
    {
        if ($companyId === null || $companyId === '' || $companyId === 0 || $companyId === '0') {
            $companyId = riskCompanyId();
        }

        return riskApplyCompanyScope($query, $companyId !== null ? (string) $companyId : null);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_index', 'asc');
    }

    // Methods
    public function getBadgeClass(): string
    {
        // Map color codes to Bootstrap badge classes
        $colorMap = [
            '#dc3545' => 'danger',
            '#fd7e14' => 'warning',
            '#ffc107' => 'warning',
            '#28a745' => 'success',
            '#17a2b8' => 'info',
            '#6c757d' => 'secondary',
            '#6f42c1' => 'primary',
        ];

        return $colorMap[$this->color_code] ?? 'secondary';
    }

    public function getDisplayName(): string
    {
        return $this->name;
    }
}
