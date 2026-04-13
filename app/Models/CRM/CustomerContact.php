<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CustomerContact extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'crm_customer_contacts';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<CRMCustomer, $this>
     */
    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }
}
