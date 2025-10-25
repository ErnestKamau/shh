<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ZohoPricelist extends Model
{
    protected $table = "zoho_items_pricelist";
    protected $fillable = ['item_id','unit_price','customer_id'];
}
