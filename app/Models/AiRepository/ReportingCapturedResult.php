<?php

namespace App\Models\AiRepository;

class ReportingCapturedResult extends AiRepositoryModel
{
    protected $table = 'reporting.captured_results';

    protected $primaryKey = 'source_id';

    public $incrementing = false;

    protected $casts = [
        'analyte_accredited' => 'boolean',
    ];
}
