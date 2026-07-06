<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AuditChecklistAudit extends Pivot
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'audit_checklist_audit';

    protected $fillable = [
        'audit_id',
        'audit_checklist_id',
        'order_index',
    ];
}
