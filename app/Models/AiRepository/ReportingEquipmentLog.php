<?php

namespace App\Models\AiRepository;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportingEquipmentLog extends AiRepositoryModel
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'equipment';
}
