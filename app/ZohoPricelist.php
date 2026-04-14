<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class ZohoPricelist extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "zoho_items_pricelist";
    protected $fillable = ['item_id','unit_price','customer_id'];
}
