<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CustomerContact extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'crm_customer_contacts';

    protected $casts = [
        'job_occupation' => \App\Casts\SafeEncrypted::class,
        'unit_name' => \App\Casts\SafeEncrypted::class,
        'email' => \App\Casts\SafeEncrypted::class,
        'telephone' => \App\Casts\SafeEncrypted::class,
        'mobile' => \App\Casts\SafeEncrypted::class,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<CRMCustomer, $this>
     */
    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }
}
