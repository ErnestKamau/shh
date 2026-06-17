<?php

/**
 * Canonical Test Request Form field names shared by walk-in TRF, portal SubmissionForm TRF templates, and TRFI form_data.
 *
 * @return array{
 *     scalar_aliases: array<string, string>,
 *     row_field_aliases: array<string, string>,
 *     customer_details: list<array{name: string, label: string, type: string, required?: bool}>,
 *     collection_data_water: list<array{name: string, label: string, type: string, required?: bool, options?: list<string>}>,
 *     collection_data_food: list<array{name: string, label: string, type: string, required?: bool, options?: list<string>}>,
 * }
 */
return [
    'scalar_aliases' => [
        'customer_tel_fax' => 'customer_phone',
        'customer_tel' => 'customer_phone',
        'tel' => 'customer_phone',
        'telephone' => 'customer_phone',
        'phone' => 'customer_phone',
        'phone_number' => 'customer_phone',
        'customer_mobile' => 'mobile_number',
        'mobile' => 'mobile_number',
        'client_name' => 'customer_name',
        'client' => 'customer_name',
        'customer' => 'customer_name',
        'address' => 'customer_address',
        'physical_address' => 'customer_address',
        'postal_address' => 'customer_address',
        'collection_date' => 'sampling_date',
        'date' => 'sampling_date',
        'email' => 'customer_email',
        'email_address' => 'customer_email',
    ],

    'row_field_aliases' => [
        'sample_id' => 'customer_sample_id',
        'sample_no' => 'lims_sample_no',
        'qty' => 'number_of_samples',
        'field_ph' => 'field_ph',
        'field_appearance' => 'field_appearance',
        'field_residual_chlorine' => 'field_residual_chlorine',
        'field_odor' => 'field_odor',
        'field_sample_temp' => 'field_sample_temp',
    ],

    'customer_details' => [
        ['name' => 'customer_name', 'label' => 'Name', 'type' => 'text', 'required' => true],
        ['name' => 'customer_address', 'label' => 'Address', 'type' => 'text', 'required' => false],
        ['name' => 'customer_phone', 'label' => 'Tel / Fax no.', 'type' => 'text', 'required' => false],
        ['name' => 'contact_person', 'label' => 'Contact person', 'type' => 'text', 'required' => false],
        ['name' => 'mobile_number', 'label' => 'Mobile number', 'type' => 'text', 'required' => false],
        ['name' => 'job_number', 'label' => 'JOB NUMBER', 'type' => 'text', 'required' => false],
    ],

    'collection_data_water' => [
        ['name' => 'sampling_date', 'label' => 'Sampling Date', 'type' => 'date', 'required' => true],
        ['name' => 'sampling_time', 'label' => 'Sampling Time', 'type' => 'text', 'required' => false],
        ['name' => 'sampling_location', 'label' => 'Sampling Location', 'type' => 'text', 'required' => false],
        ['name' => 'sampling_apparatus', 'label' => 'Sampling Apparatus', 'type' => 'select', 'required' => false],
        ['name' => 'thermometer_id', 'label' => 'Thermometer ID', 'type' => 'text', 'required' => false],
        ['name' => 'method_of_sampling', 'label' => 'Method of Sampling', 'type' => 'select', 'required' => false],
        ['name' => 'reason_of_collection', 'label' => 'Reason of Collection', 'type' => 'select', 'required' => false],
        ['name' => 'transport_condition', 'label' => 'Transport Condition', 'type' => 'select', 'required' => false],
    ],

    'collection_data_food' => [
        ['name' => 'sampling_date', 'label' => 'Sampling Date', 'type' => 'date', 'required' => true],
        ['name' => 'sampling_time', 'label' => 'Sampling Time', 'type' => 'text', 'required' => false],
        ['name' => 'sampling_location', 'label' => 'Sampling Location', 'type' => 'text', 'required' => false],
        ['name' => 'sampling_apparatus', 'label' => 'Sampling Apparatus', 'type' => 'select', 'required' => false],
        ['name' => 'thermometer_id', 'label' => 'Thermometer ID', 'type' => 'text', 'required' => false],
        ['name' => 'method_of_sampling', 'label' => 'Method of Sampling', 'type' => 'select', 'required' => false],
        ['name' => 'reason_of_collection', 'label' => 'Reason of Collection', 'type' => 'select', 'required' => false],
        ['name' => 'transport_condition', 'label' => 'Transport Condition', 'type' => 'select', 'required' => false],
    ],
];
