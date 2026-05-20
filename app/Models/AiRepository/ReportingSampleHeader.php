<?php

namespace App\Models\AiRepository;

class ReportingSampleHeader extends AiRepositoryModel
{
    protected $table = 'sample_headers';

    protected $primaryKey = 'source_id';

    public $incrementing = false;

    protected $casts = [
        'is_qc_batch' => 'boolean',
        'isactive' => 'boolean',
    ];
}
