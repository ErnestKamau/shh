<?php

namespace App\Models\AiRepository;

class ReportingSampleDate extends AiRepositoryModel
{
    protected $table = 'sample_details';

    protected $primaryKey = 'source_id';

    public $incrementing = false;
}
