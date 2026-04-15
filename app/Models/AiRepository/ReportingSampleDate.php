<?php

namespace App\Models\AiRepository;

class ReportingSampleDate extends AiRepositoryModel
{
    protected $table = 'reporting.sample_dates';

    protected $primaryKey = 'source_id';

    public $incrementing = false;
}
