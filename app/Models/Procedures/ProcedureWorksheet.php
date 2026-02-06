<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Model;

class ProcedureWorksheet extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function steps()
    {
        return $this->hasMany(ProcedureWorksheetStep::class);
    }
}
