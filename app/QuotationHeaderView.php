<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuotationHeaderView extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = "quotation_header_view";
    protected $appends = ['contact'];

    public function getContactAttribute(){
        return $this->contact_first.$this->contact_middle.$this->contact_last;
    }
}
