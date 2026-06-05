<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CrmCustomerAttachment extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'crm_customer_attachments';

    protected $fillable = [
        'title',
        'type',
        'file_type',
        'file_size',
        'file_path',
        'description',
        'crm_customer_id',
        'posted_by',
        'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'is_delete' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }
}
