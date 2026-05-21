<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Acceptance form — sample creation dispatch
    |--------------------------------------------------------------------------
    |
    | After a customer signs an acceptance form, CreateSamplesFromAcceptanceFormJob
    | creates the sample batch, details, and invoice.
    |
    | When true (default), the job runs in-process so the portal API response can
    | include sample_header_id and invoice_id once signing completes.
    |
    | When false, the job is queued. You must run queue workers whenever
    | QUEUE_CONNECTION is not "sync" (e.g. php artisan queue:work).
    |
    */
    'acceptance_form' => [
        'dispatch_sample_creation_sync' => env('ACCEPTANCE_FORM_DISPATCH_SAMPLE_CREATION_SYNC', true),
    ],

];
