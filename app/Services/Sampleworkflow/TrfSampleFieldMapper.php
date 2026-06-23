<?php

namespace App\Services\Sampleworkflow;

use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use Carbon\Carbon;

/**
 * Explicit TRF form_data → sample_headers / sample_details field mappings.
 *
 * Header mappings (form_data / enquiry → sample_headers):
 * - crm_contact_id, contact_person → crm_contact_id
 * - customer_email, email → schedule_customer_email
 * - sampled_by → sampling_officer_name
 * - sampling_time → radio_active_levels (H:i)
 * - sampling_location → crm_unit_name (when unit not already set)
 * - sample_rows[0].sample_description → description
 *
 * Detail mappings (sample_rows[] → sample_details):
 * - sample_description → comments
 * - sample_quantity + sample_quantity_unit → quantity
 * - production_date → mfg_date
 * - expiration_date → expiry_date
 * - batch_number → batch_lot_no
 * - sampling_point / location → sample_point_id (lookup by name under customer)
 */
class TrfSampleFieldMapper
{
    /**
     * @param  array<string, mixed>  $formData
     * @param  array{
     *     crm_customer_id?: string|null,
     *     crm_contact_id?: string|null,
     *     crm_unit_id?: string|null,
     *     crm_unit_name?: string|null,
     *     email?: string|null
     * }  $enquiryContext
     * @return array<string, mixed>
     */
    public function mapToSampleHeader(array $formData, array $enquiryContext = []): array
    {
        $mapped = [];
        $customerId = $this->scalar($enquiryContext['crm_customer_id'] ?? $formData['crm_customer_id'] ?? null);

        $contactId = $this->scalar($formData['crm_contact_id'] ?? $enquiryContext['crm_contact_id'] ?? null);
        if ($contactId === null) {
            $contactId = $this->resolveContactIdFromName(
                $customerId,
                $this->scalar($formData['contact_person'] ?? null),
            );
        }
        if ($contactId !== null) {
            $mapped['crm_contact_id'] = $contactId;
        }

        $email = $this->scalar(
            $formData['customer_email'] ?? null,
            $formData['email'] ?? null,
            $formData['email_address'] ?? null,
            $enquiryContext['email'] ?? null,
        );
        if ($email !== null) {
            $mapped['schedule_customer_email'] = $email;
        }

        $sampledBy = $this->scalar(
            $formData['sampled_by'] ?? null,
            $formData['customer_representative_name'] ?? null,
        );
        if ($sampledBy !== null) {
            $mapped['sampling_officer_name'] = $sampledBy;
        }

        $samplingTime = $this->scalar($formData['sampling_time'] ?? null);
        if ($samplingTime !== null) {
            $parsed = $this->parseTime($samplingTime);
            if ($parsed !== null) {
                $mapped['radio_active_levels'] = $parsed;
            }
        }

        $unitId = $this->scalar($enquiryContext['crm_unit_id'] ?? null);
        $unitName = $this->scalar($enquiryContext['crm_unit_name'] ?? null);
        if ($unitId === null && ($unitName === null || $unitName === 'N/A')) {
            $location = $this->scalar($formData['sampling_location'] ?? null);
            if ($location !== null) {
                $mapped['crm_unit_name'] = $location;
            }
        }

        $rows = $formData['sample_rows'] ?? [];
        if (is_array($rows) && $rows !== []) {
            $firstRow = reset($rows);
            if (is_array($firstRow)) {
                $description = $this->scalar($firstRow['sample_description'] ?? null);
                if ($description !== null) {
                    $mapped['description'] = $description;
                }
            }
        }

        $sampleTemp = $this->scalar($formData['sample_temperature'] ?? null);
        if ($sampleTemp === null && is_array($rows) && $rows !== []) {
            $firstRow = reset($rows);
            if (is_array($firstRow)) {
                $sampleTemp = $this->scalar($firstRow['sample_temp'] ?? null);
            }
        }
        if ($sampleTemp !== null) {
            $mapped['condition_quality_sample'] = $sampleTemp;
        }

        return array_filter($mapped, fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function mapToSampleDetail(array $row, int $rowIndex = 0, ?string $crmCustomerId = null): array
    {
        $mapped = [];

        $description = $this->scalar($row['sample_description'] ?? null);
        if ($description !== null) {
            $mapped['comments'] = $description;
        }

        $quantity = $this->formatRowQuantity($row);
        if ($quantity !== '') {
            $mapped['quantity'] = $quantity;
        }

        $mfgDate = $this->parseDate($row['production_date'] ?? null);
        if ($mfgDate !== null) {
            $mapped['mfg_date'] = $mfgDate;
        }

        $expiryDate = $this->parseDate($row['expiration_date'] ?? $row['expiry_date'] ?? null);
        if ($expiryDate !== null) {
            $mapped['expiry_date'] = $expiryDate;
        }

        $lotNo = $this->scalar($row['batch_number'] ?? $row['lot_no'] ?? null);
        if ($lotNo !== null) {
            $mapped['batch_lot_no'] = $lotNo;
        }

        $pointName = $this->scalar($row['sampling_point'] ?? $row['location'] ?? null);
        if ($pointName !== null && $crmCustomerId !== null && $crmCustomerId !== '') {
            $pointId = SamplePoint::query()
                ->where('crm_customer_id', $crmCustomerId)
                ->whereRaw('name ILIKE ?', [$pointName])
                ->value('id');

            if ($pointId !== null) {
                $mapped['sample_point_id'] = (string) $pointId;
            }
        }

        return $mapped;
    }

    /**
     * Merge TRF-mapped values only where the target array has empty/null values.
     *
     * @param  array<string, mixed>  $existing
     * @param  array<string, mixed>  $fromTrf
     * @return array<string, mixed>
     */
    public function mergeFillGaps(array $existing, array $fromTrf): array
    {
        foreach ($fromTrf as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $current = $existing[$key] ?? null;
            if ($current === null || $current === '') {
                $existing[$key] = $value;
            }
        }

        return $existing;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function sampleRowsFromFormData(array $formData): array
    {
        $rows = $formData['sample_rows'] ?? [];

        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_filter($rows, fn ($row) => is_array($row)));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function formatRowQuantity(array $row): string
    {
        $quantity = trim((string) ($row['sample_quantity'] ?? ''));
        $unit = trim((string) ($row['sample_quantity_unit'] ?? ''));

        if ($quantity !== '' && $unit !== '') {
            return $quantity.' '.$unit;
        }

        if ($quantity !== '') {
            return $quantity;
        }

        $legacy = trim((string) ($row['qty'] ?? ''));

        return $legacy;
    }

    private function resolveContactIdFromName(?string $customerId, ?string $contactName): ?string
    {
        if ($customerId === null || $contactName === null || trim($contactName) === '') {
            return null;
        }

        $contact = CustomerContact::query()
            ->where('crm_customer_id', $customerId)
            ->where(function ($query) use ($contactName) {
                $query->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) ILIKE ?", [trim($contactName)])
                    ->orWhereRaw('first_name ILIKE ?', [trim($contactName)]);
            })
            ->first();

        return $contact?->id !== null ? (string) $contact->id : null;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    private function scalar(mixed ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if ($candidate === null) {
                continue;
            }

            $value = trim((string) $candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
