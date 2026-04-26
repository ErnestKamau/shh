<?php

namespace App\Models\AiRepository;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OperationalSyncRun extends AiRepositoryModel
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'reporting.sync_runs';

    public $timestamps = true;
}
