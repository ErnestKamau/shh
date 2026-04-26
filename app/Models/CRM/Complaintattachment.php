<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Complaintattachment extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'complaintattachments';

    protected $fillable = [
        'title',
        'type',
        'file_path',
        'description',
        'complaint_id',
        'posted_by',
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
