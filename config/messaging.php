<?php

return [
    'events' => [
        'RESULT_READY' => [
            'recipient_phone_source' => 'crm_customer_contacts.phone',
            'provider_channel' => 'whatsapp',
            'idempotency_source' => 'variables.idempotency_key',
            'provider_payload' => [
                'header_value' => null,
                'body_values' => [
                    'customer_name',
                    'result_reference',
                ],
            ],
            'language_source' => 'tenant_message_mappings.language',
        ],
    ],
];