<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Complaintnotes extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'complaintnotes';

    protected $fillable = [
        'notes',
        'created_by',
        'complaint_id',
        'type',
        'is_public',
        'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
