<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuotationHeaderView extends Model
{
    protected $table = "quotation_header_view";
    protected $appends = ['contact'];

    public function getContactAttribute(){
        return $this->contact_first.$this->contact_middle.$this->contact_last;
    }
}
