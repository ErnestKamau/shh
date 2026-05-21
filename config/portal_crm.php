<?php

return [

    'cache' => [
        'store' => env('PORTAL_CRM_CACHE_STORE', env('CACHE_STORE', env('CACHE_DRIVER', 'file'))),
        'invoices_ttl' => (int) env('PORTAL_CRM_INVOICES_CACHE_TTL', 120),
    ],

];
