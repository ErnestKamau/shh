<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ZohoCustomers extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "zoho_customers";

    protected $fillable = [
        'customer_no',
        'name',
        'name_2',
        'email',
        'phone_no',
        'contact',
        'status',
        'currency_id',
        'currency_code',
        'zoho_contact_id',
        'responsibility_center',
        'location_code',
        'post_code',
        'country_region_code',
        'ic_partner_code',
        'salesperson_code',
        'customer_posting_group',
        'allow_multiple_posting_groups',
        'gen_bus_posting_group',
        'vat_bus_posting_group',
        'customer_price_group',
        'customer_disc_group',
        'payment_terms_code',
        'reminder_terms_code',
        'fin_charge_terms_code',
        'language_code',
        'search_name',
        'credit_limit_lcy',
        'blocked',
        'privacy_blocked',
        'last_date_modified',
        'application_method',
        'combine_shipments',
        'reserve',
        'ship_to_code',
        'shipping_advice',
        'shipping_agent_code',
        'base_calendar_code',
        'balance_lcy',
        'balance_due_lcy',
        'sales_lcy',
        'payments_lcy',
        'coupled_to_crm',
        'coupled_to_dataverse',
    ];

    
}
