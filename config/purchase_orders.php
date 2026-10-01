<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Customer purchase orders — ledger-based PO cover
    |--------------------------------------------------------------------------
    |
    | When false, the PO ledger tables exist but enquiry capture, job creation
    | and invoicing keep their legacy behaviour (PO number + file per enquiry).
    | Enable per environment once the ledger backfill has run.
    |
    */
    'enabled' => env('PURCHASE_ORDERS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Defaults for new purchase orders
    |--------------------------------------------------------------------------
    */
    'default_expiry_notice_days' => (int) env('PURCHASE_ORDERS_EXPIRY_NOTICE_DAYS', 30),

];
