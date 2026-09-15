<?php

namespace App\Services\Commercial;

use App\Services\Commercial\Concerns\ResolvesAmSpecCompanyBranding;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use PDF;
use RuntimeException;

final class AmSpecTrfPdfService
{
    use ResolvesAmSpecCompanyBranding;

    public function __construct(
        private SubmissionRequestSampleLineService $sampleLineService,
    ) {}

    public function generateAndStore(SubmissionFormInstance $instance): string
    {
        $instance->loadMissing(['submissionForm', 'crmCustomer', 'values.element']);
        $viewModel = $this->buildViewModel($instance);

        $customerName = preg_replace('/[^A-Za-z0-9]/', '', (string) ($viewModel['customer_name'] ?: 'Customer'));
        $formNumber = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($viewModel['form_number'] ?: 'TRF'));
        $filename = $formNumber.'-'.date('d-M-Y').'.pdf';

        $directory = storage_path('app/trf/'.$customerName);
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create TRF storage directory.');
        }

        $template = $this->resolveTemplate($instance);
        $pdf = PDF::loadView($template, $viewModel);
        $pdf->getDomPDF()->set_option('enable_php', true);
        $fullPath = $directory.'/'.$filename;
        $pdf->save($fullPath);

        return '/trf/'.$customerName.'/'.$filename;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildViewModel(SubmissionFormInstance $instance): array
    {
        $instance->loadMissing(['submissionForm', 'crmCustomer', 'values.element', 'batches']);
        $values = $this->indexedValues($instance);
        $enquiry = $instance->sampleSubmissionRequest
            ?? SampleSubmissionRequest::query()->where('submission_form_instance_id', $instance->id)->first();

        $company = getActiveCompany();
        $logos = $this->resolveAmSpecLogoPaths($company);
        $documentCode = strtoupper((string) ($instance->submissionForm->document_code ?? ''));
        $isFood = str_contains($documentCode, 'FOOD');
        $isWater = str_contains($documentCode, 'WATER') && ! str_contains($documentCode, 'WASTE');

        $jobNumber = '';
        $batch = $instance->batches->first();
        if ($batch !== null) {
            $jobNumber = (string) ($batch->batch_code ?? '');
        }

        $customerExtras = [
            'customer_tax_id' => (string) ($values['customer_tax_id'] ?? ''),
            'customer_email' => (string) ($values['customer_email'] ?? $instance->crmCustomer?->email ?? ''),
        ];

        $collectionExtras = [
            'date_received' => $values['date_received'] ?? null,
            'packaging' => $values['packaging'] ?? null,
            'sample_weight' => $values['sample_weight'] ?? null,
            'sample_information' => $values['sample_information'] ?? null,
            'ship_name' => $values['ship_name'] ?? null,
            'port_of_loading' => $values['port_of_loading'] ?? null,
            'port_of_discharge' => $values['port_of_discharge'] ?? null,
            'seal_number' => $values['seal_number'] ?? null,
        ];

        return [
            'instance' => $instance,
            'form' => $instance->submissionForm,
            'company' => $company,
            'form_number' => $instance->getDocumentControlNumber() ?? $instance->form_number ?? '',
            'document_title' => 'TEST REQUEST FORM',
            'document_code' => match (true) {
                $isFood => 'AMS/QMS/LWS/019',
                $isWater => 'AMS/QMS/LWS/020',
                default => (string) ($instance->submissionForm->document_code ?? ''),
            },
            'customer_name' => (string) ($values['customer_name'] ?? $instance->crmCustomer?->name ?? ''),
            'customer_address' => (string) ($values['customer_address'] ?? $instance->crmCustomer?->physical_address ?? ''),
            'customer_tel_fax' => (string) ($values['customer_phone'] ?? $values['customer_tel_fax'] ?? $instance->crmCustomer?->telephone1 ?? ''),
            'customer_mobile' => (string) ($values['mobile_number'] ?? $values['customer_mobile'] ?? ''),
            'customer_tax_id' => $customerExtras['customer_tax_id'],
            'customer_email' => $customerExtras['customer_email'],
            'has_customer_extras' => $this->hasAnyFilledValues($customerExtras),
            'contact_person' => (string) ($values['contact_person'] ?? ''),
            'job_number' => $jobNumber,
            'collection' => [
                'sampling_date' => $values['sampling_date'] ?? null,
                'sampling_time' => $values['sampling_time'] ?? null,
                'sampling_location' => $values['sampling_location'] ?? null,
                'sampling_apparatus' => $this->formatApparatus($values['sampling_apparatus'] ?? null),
                'method_of_sampling' => $this->formatLabel($values['method_of_sampling'] ?? null),
                'reason_of_collection' => $this->formatLabel($values['reason_of_collection'] ?? null),
                'transport_condition' => $this->formatList($values['transport_condition'] ?? null),
                'date_received' => $collectionExtras['date_received'],
                'packaging' => $collectionExtras['packaging'],
                'sample_weight' => $collectionExtras['sample_weight'],
                'sample_information' => $collectionExtras['sample_information'],
                'ship_name' => $collectionExtras['ship_name'],
                'port_of_loading' => $collectionExtras['port_of_loading'],
                'port_of_discharge' => $collectionExtras['port_of_discharge'],
                'seal_number' => $collectionExtras['seal_number'],
            ],
            'has_collection_extras' => $this->hasAllFilledValues($collectionExtras),
            'sample_lines' => $this->sampleLineService->linesForInstance($instance),
            'sign' => [
                'statement_of_conformity' => $this->formatLabel($values['statement_of_conformity'] ?? $enquiry?->statement_of_conformity),
                'sampled_by' => $values['sampled_by'] ?? null,
                'customer_representative_name' => $values['customer_representative_name'] ?? null,
                'customer_representative_contact' => $values['customer_representative_contact'] ?? null,
                'remarks' => $values['remarks'] ?? $enquiry?->purpose,
                'signature' => $values['customer_representative_signature'] ?? null,
            ],
            'is_food' => $isFood,
            'logo_path' => $logos['primary'],
            'logo_secondary_path' => $logos['secondary'],
        ];
    }

    private function resolveTemplate(SubmissionFormInstance $instance): string
    {
        $custom = (string) ($instance->submissionForm->print_template_name ?? '');
        if ($custom !== '' && view()->exists($custom)) {
            return $custom;
        }

        $code = strtoupper((string) ($instance->submissionForm->document_code ?? ''));
        if (str_contains($code, 'FOOD')) {
            return 'layouts.lab.invoice.print-trf-amspec-food';
        }

        return 'layouts.lab.invoice.print-trf-amspec-water';
    }

    /**
     * @return array<string, mixed>
     */
    private function indexedValues(SubmissionFormInstance $instance): array
    {
        $values = [];
        foreach ($instance->values as $row) {
            if ($row->array_index !== null) {
                continue;
            }
            $name = (string) ($row->element->name ?? '');
            if ($name !== '') {
                $values[$name] = $row->value;
            }
        }

        return $values;
    }

  /**
     * @param  mixed  $value
     */
    private function formatList(mixed $value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map(fn ($item) => $this->formatLabel($item), $value));
        }

        if ($value === null || $value === '') {
            return '';
        }

        $parts = array_filter(array_map('trim', explode(',', (string) $value)));

        return implode(', ', array_map(fn ($item) => $this->formatLabel($item), $parts));
    }

    /**
     * @param  mixed  $value
     */
    private function formatApparatus(mixed $value): string
    {
        $tokens = is_array($value)
            ? $value
            : array_filter(array_map('trim', explode(',', (string) $value)));

        $labels = array_map(fn ($token) => $this->formatApparatusLabel((string) $token), $tokens);

        return implode(', ', $labels);
    }

    private function formatApparatusLabel(string $token): string
    {
        if ($token === 'thermometer_ams_c_ins_116') {
            return 'Thermometer ID AMS/C/INS/116';
        }

        return $this->formatLabel($token);
    }

    private function formatLabel(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return ucwords(str_replace('_', ' ', (string) $value));
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function hasAnyFilledValues(array $values): bool
    {
        foreach ($values as $value) {
            if ($this->isFilledValue($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function hasAllFilledValues(array $values): bool
    {
        foreach ($values as $value) {
            if (! $this->isFilledValue($value)) {
                return false;
            }
        }

        return true;
    }

    private function isFilledValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        return trim((string) $value) !== '';
    }

}
