<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Model;

class ProcedureWorksheet extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'document_control_no',
        'revision',
        'issue_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'issue_date' => 'date',
    ];

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

    public function testKitColumns()
    {
        return $this->hasMany(ProcedureTestKitColumn::class)->orderBy('order');
    }
}
