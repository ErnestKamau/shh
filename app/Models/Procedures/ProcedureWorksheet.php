<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class ProcedureWorksheet extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'document_control_no',
        'revision',
        'issue_date',
        'config_fields_placement',
        'layout_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'issue_date' => 'date',
        'layout_settings' => 'array',
    ];

    /**
     * Whether this procedure uses sectioned-matrix layout.
     */
    public function isSectionedMatrix(): bool
    {
        return data_get($this->layout_settings, 'mode') === 'sectioned_matrix';
    }

    /**
     * Get a section's config from layout_settings by key, or null if not found.
     *
     * @return array<string, mixed>|null
     */
    public function getMatrixSection(string $sectionKey): ?array
    {
        $sections = data_get($this->layout_settings, 'sections', []);
        foreach ($sections as $section) {
            if (($section['key'] ?? null) === $sectionKey) {
                return $section;
            }
        }

        return null;
    }

    public function steps()
    {
        return $this->hasMany(ProcedureWorksheetStep::class);
    }

    public function analysisTypes()
    {
        return $this->hasMany(\App\AnalysisType::class, 'procedure_worksheet_id');
    }

    public function analysisElements()
    {
        return $this->hasMany(\App\AnalysisElements::class, 'procedure_worksheet_id');
    }

    public function configFields()
    {
        return $this->hasMany(ProcedureConfigField::class)->orderBy('order');
    }

    public function configFieldSections()
    {
        return $this->hasMany(ProcedureConfigFieldSection::class)->orderBy('order');
    }

    public function stepGroups()
    {
        return $this->hasMany(ProcedureWorksheetStepGroup::class)->orderBy('order');
    }

    public function testKitColumns()
    {
        return $this->hasMany(ProcedureTestKitColumn::class)->orderBy('order');
    }
}
