<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTeamMember extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'audit_team_members';

    protected $fillable = [
        'audit_id',
        'user_id',
        'role_id',
        'role_name',
        'responsibilities',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(AuditTeamRole::class, 'role_id');
    }
}
