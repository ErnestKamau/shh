<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class QuotationHeader extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'quotation_headers';
    protected $appends = ['creator'];

    public function getCreatorAttribute(){
        return User::find($this->prepared_by_id)->name ?? '-';
    }
}
