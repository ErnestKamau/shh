<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use App\Models\Equipments\Equipment;
use App\User;

class ProcedureWorksheetStep extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'procedure_worksheet_id',
        'step',
        'value_type',
        'is_active',
        'is_result_step',
        'attracts_equipment_logbook',
        'logbook_equipment_ids',
        'default_equipment_id',
        'default_analyst_id',
        'default_measurand_ids',
        'default_value',
        'default_measurand_values',
        'select_options',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_result_step' => 'boolean',
        'attracts_equipment_logbook' => 'boolean',
        'logbook_equipment_ids' => 'array',
        'default_measurand_ids' => 'array',
        'default_equipment_id' => 'array',
        'default_analyst_id' => 'array',
        'default_measurand_values' => 'array',
        'select_options' => 'array',
    ];

    public function worksheet()
    {
        return $this->belongsTo(ProcedureWorksheet::class, 'procedure_worksheet_id');
    }

    public function getEquipmentAttribute()
    {
        $id = $this->default_equipment_id;
        if ($id === null || $id === '' || $id === 0) {
            return collect([]);
        }

        // Backwards-compatible: handle legacy JSON or comma-separated formats
        if (is_string($id)) {
            $trimmed = trim($id);
            if ($trimmed !== '' && $trimmed[0] === '[') {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    $id = $decoded;
                }
            } elseif (str_contains($trimmed, ',')) {
                $id = array_map('trim', explode(',', $trimmed));
            }
        }

        $ids = is_array($id) ? $id : [(int) $id];

        return Equipment::whereIn('id', $ids)->get();
    }

    public function getAnalystsAttribute()
    {
        $id = $this->default_analyst_id;
        if ($id === null || $id === '' || $id === 0) {
            return collect([]);
        }

        // Backwards-compatible: handle legacy JSON or comma-separated formats
        if (is_string($id)) {
            $trimmed = trim($id);
            if ($trimmed !== '' && $trimmed[0] === '[') {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    $id = $decoded;
                }
            } elseif (str_contains($trimmed, ',')) {
                $id = array_map('trim', explode(',', $trimmed));
            }
        }

        $ids = is_array($id) ? $id : [(int) $id];

        return User::whereIn('id', $ids)->get();
    }

    public function getMeasurandsAttribute()
    {
        if (empty($this->default_measurand_ids)) {
            return collect([]);
        }
        return \App\ReportingUnit::whereIn('id', $this->default_measurand_ids)->get();
    }

    public function getLogbookEquipmentAttribute()
    {
        if (empty($this->logbook_equipment_ids)) {
            return collect([]);
        }

        $ids = is_array($this->logbook_equipment_ids) ? $this->logbook_equipment_ids : [];

        return Equipment::whereIn('id', $ids)->get();
    }
}
