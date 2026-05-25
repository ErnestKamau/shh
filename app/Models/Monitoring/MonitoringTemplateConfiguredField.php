<?php

namespace App\Models\Monitoring;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringTemplateConfiguredField extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'template_id',
        'placement',
        'label',
        'field_type',
        'order',
        'help_text',
        'model_tied_to',
        'field_config',
        'is_required',
        'field_value_name',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
        'field_config' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(MonitoringTemplate::class, 'template_id');
    }

    /**
     * @return array<string, string>
     */
    public static function placementOptions(): array
    {
        return [
            'top' => 'Top of worksheet',
            'bottom' => 'Bottom of worksheet',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function fieldTypeOptions(): array
    {
        return [
            'input' => 'Text input',
            'textarea' => 'Text area',
            'date' => 'Date',
            'month_day' => 'Month & day',
            'datetime' => 'Date & time',
            'dataset_related' => 'Dataset related',
            'monitoring_equipment' => 'Monitoring equipment',
            'lab_section_select' => 'Lab section',
            'equipment_calibration' => 'Equipment calibration',
            'manager_dropdown' => 'Manager',
            'user_signature' => 'Manager signature',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function datasetModelOptions(): array
    {
        return [
            'equipments' => 'Equipments',
            'users' => 'Users',
            'methods' => 'Methods',
        ];
    }

    /**
     * Calibration certificate attributes selectable at capture (latest cert on/before reading date).
     *
     * @return array<string, string>
     */
    public static function calibrationAttributeOptions(): array
    {
        return [
            'correction_factor' => 'Correction factor',
            'uncertainty_of_measure' => 'Uncertainty of measure',
            'date' => 'Calibration date',
            'certificate' => 'Certificate reference',
            'reference_number' => 'Reference number',
            'service_provider' => 'Service provider',
            'description' => 'Description',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function managerSourceOptions(): array
    {
        return [
            'lab_section' => 'Lab section (environmental)',
            'equipment_location' => 'Equipment location (equipment monitoring)',
        ];
    }
}
