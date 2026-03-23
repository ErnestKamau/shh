<?php

namespace App\Services;

use App\Models\CRM\CRMCompanySubUnit;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\SampleDetailStaging;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SubmissionFormBatchSyncService
{
    /**
     * True when at least one linked batch (with a matching form slice) would change if apply-to-batches ran.
     */
    public function linkedBatchesOutOfSyncWithForm(SubmissionFormInstance $instance): bool
    {
        $slices = collect($instance->getAllFieldsWithValues());

        if ($slices->isEmpty()) {
            return false;
        }

        $batches = SampleHeader::query()
            ->where('submission_form_instance_id', $instance->id)
            ->orderBy('id')
            ->get();

        if ($batches->isEmpty()) {
            return false;
        }

        $duplicateTypeIds = $batches
            ->groupBy('sample_type_id')
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->keys()
            ->all();

        if ($duplicateTypeIds !== []) {
            return false;
        }

        $batchCount = $batches->count();

        foreach ($batches as $header) {
            $slice = $this->findSliceForHeader($slices, $header, $batchCount);

            if ($slice === null) {
                continue;
            }

            $headerData = $slice['sample_header'] ?? [];
            $proposed = $this->collectProposedHeaderAttributeMap($header, $headerData);

            if ($this->headerAttributeMapDiffersFromModel($header, $proposed)) {
                return true;
            }

            $effectiveCustomerId = array_key_exists('crm_customer_id', $proposed)
                ? (int) $proposed['crm_customer_id']
                : (int) $header->crm_customer_id;

            if ($this->unprocessedStagingWouldChange($header, $slice, $effectiveCustomerId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply current submission form instance values to all linked sample headers and unprocessed staging rows.
     *
     * @return array{updated: int, skipped: int, messages: array{success: array<int, string>, warning: array<int, string>, error: array<int, string>}}
     */
    public function sync(SubmissionFormInstance $instance): array
    {
        $messages = [
            'success' => [],
            'warning' => [],
            'error' => [],
        ];

        $slices = collect($instance->getAllFieldsWithValues());

        if ($slices->isEmpty()) {
            $messages['error'][] = 'The submission form does not contain readable sample/batch information. Save the form again or ask reception or IT for help.';

            return ['updated' => 0, 'skipped' => 0, 'messages' => $messages];
        }

        $batches = SampleHeader::query()
            ->where('submission_form_instance_id', $instance->id)
            ->orderBy('id')
            ->get();

        if ($batches->isEmpty()) {
            $messages['error'][] = 'No lab batches are linked to this submission yet.';

            return ['updated' => 0, 'skipped' => 0, 'messages' => $messages];
        }

        $duplicateTypeIds = $batches
            ->groupBy('sample_type_id')
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($duplicateTypeIds !== []) {
            $details = [];
            foreach ($duplicateTypeIds as $typeId) {
                $conflictIds = $batches->where('sample_type_id', $typeId)->pluck('id')->implode(', ');
                $details[] = "type {$typeId} (batch record IDs: {$conflictIds})";
            }
            $messages['error'][] = 'More than one linked batch uses the same sample type, so the system cannot safely match the form to each batch. Please contact reception or IT. For support: duplicate sample type — ' . implode('; ', $details) . '.';

            return ['updated' => 0, 'skipped' => $batches->count(), 'messages' => $messages];
        }

        $updated = 0;
        $skipped = 0;

        foreach ($batches as $header) {
            $slice = $this->findSliceForHeader($slices, $header, $batches->count());

            if ($slice === null) {
                $messages['warning'][] = "Batch {$header->batch_code}: not matched to the form (sample type). Unchanged. Ask reception/IT if needed. (Ref {$header->id})";
                $skipped++;

                continue;
            }

            $hadSamples = $header->samples()->exists();
            $snapshot = $this->snapshotBatchPaperwork($header);

            $this->applyHeaderFieldsFromSlice($header, $slice['sample_header'] ?? []);

            $header->refresh();

            $stagingMeta = $this->syncStagingForHeader($header, $slice);

            $warningLine = $this->buildBatchApplySummaryLine(
                $header,
                $snapshot,
                $hadSamples,
                $stagingMeta
            );
            if ($warningLine !== null) {
                $messages['warning'][] = $warningLine;
            }

            $messages['success'][] = "{$header->batch_code}: updated from form.";
            $updated++;
        }

        Log::info('SubmissionFormBatchSyncService: sync completed', [
            'instance_id' => $instance->id,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);

        return [
            'updated' => $updated,
            'skipped' => $skipped,
            'messages' => $messages,
        ];
    }

    /**
     * @param  Collection<int, array{sample_header: array, sample_details: array}>  $slices
     */
    private function findSliceForHeader(Collection $slices, SampleHeader $header, int $batchCount): ?array
    {
        if ($batchCount === 1 && $slices->count() === 1) {
            return $slices->first();
        }

        $headerTypeId = (int) $header->sample_type_id;

        foreach ($slices as $slice) {
            $sliceTypeId = isset($slice['sample_header']['sample_type_id'])
                ? (int) $slice['sample_header']['sample_type_id']
                : null;

            if ($sliceTypeId !== null && $sliceTypeId === $headerTypeId) {
                return $slice;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $headerData
     */
    private function applyHeaderFieldsFromSlice(SampleHeader $header, array $headerData): void
    {
        $proposed = $this->collectProposedHeaderAttributeMap($header, $headerData);

        $crmUnitId = $proposed['crm_unit_id'] ?? '';
        $crmUnitName = $proposed['crm_unit_name'] ?? '';

        $attributes = $proposed;
        unset($attributes['crm_unit_id'], $attributes['crm_unit_name']);

        $attributes = array_filter(
            $attributes,
            static fn ($v) => $v !== null && $v !== ''
        );

        $header->crm_unit_id = $crmUnitId;
        $header->crm_unit_name = $crmUnitName;

        if ($attributes !== []) {
            $header->fill($attributes);
        }

        $header->save();
    }

    /**
     * Attribute map that would be written by apply (including crm_unit_id / crm_unit_name before fill filtering).
     *
     * @param  array<string, mixed>  $headerData
     * @return array<string, mixed>
     */
    private function collectProposedHeaderAttributeMap(SampleHeader $header, array $headerData): array
    {
        $getSingleValue = function ($value) {
            if (is_array($value)) {
                return ! empty($value) ? $value[0] : null;
            }

            return $value;
        };

        $getIntegerValue = function ($value) use ($getSingleValue) {
            $singleValue = $getSingleValue($value);

            return is_numeric($singleValue) ? (int) $singleValue : null;
        };

        $formatDate = function ($value, $default = null) use ($getSingleValue) {
            $singleValue = $getSingleValue($value);
            if (! $singleValue) {
                return $default;
            }

            try {
                return Carbon::parse($singleValue)->format('Y-m-d');
            } catch (\Throwable) {
                return $default;
            }
        };

        $headerDateDefault = function (string $attribute) use ($header): ?string {
            $current = $header->{$attribute} ?? null;
            if ($current === null || $current === '') {
                return null;
            }
            if ($current instanceof \DateTimeInterface) {
                return $current->format('Y-m-d');
            }
            if (is_string($current)) {
                try {
                    return Carbon::parse($current)->format('Y-m-d');
                } catch (\Throwable) {
                    return $current;
                }
            }

            return null;
        };

        $headerDataForUnit = $headerData;
        if (! array_key_exists('crm_customer_id', $headerDataForUnit) || $headerDataForUnit['crm_customer_id'] === null || $headerDataForUnit['crm_customer_id'] === '') {
            $headerDataForUnit['crm_customer_id'] = $header->crm_customer_id;
        }

        [$crmUnitId, $crmUnitName] = $this->resolveCrmUnit($headerDataForUnit, $getSingleValue, $getIntegerValue);

        $attributes = [];

        $directKeys = [
            'crm_customer_id', 'crm_contact_id', 'customer_email', 'receipt_date', 'date_collected',
            'batch_scope', 'customer_survey', 'quote_no', 'batch_instructions', 'sampling_method_id',
            'require_mu', 'payment_done_by', 'condition_quality_sample', 'description', 'document_number',
            'importer_address', 'date_expected', 'quote_id', 'reference_number', 'is_routine',
            'routine_frequency', 'is_client_order', 'submit_by',
        ];

        foreach ($directKeys as $key) {
            if (array_key_exists($key, $headerData)) {
                if (in_array($key, ['receipt_date', 'date_collected', 'date_expected'], true)) {
                    $attributes[$key] = $formatDate($headerData[$key] ?? null, $headerDateDefault($key));
                } elseif (str_ends_with($key, '_id') && $key !== 'quote_id') {
                    $attributes[$key] = $getIntegerValue($headerData[$key] ?? null);
                } elseif ($key === 'quote_id' || $key === 'require_mu' || $key === 'is_routine' || $key === 'routine_frequency' || $key === 'is_client_order') {
                    $attributes[$key] = $getIntegerValue($headerData[$key] ?? null);
                } else {
                    $attributes[$key] = $getSingleValue($headerData[$key] ?? null);
                }
            }
        }

        $attributes['crm_unit_id'] = $crmUnitId;
        $attributes['crm_unit_name'] = $crmUnitName;

        if (array_key_exists('receiving_officer', $headerData) || array_key_exists('receiving_officer_name', $headerData)) {
            $recv = $getSingleValue($headerData['receiving_officer'] ?? $headerData['receiving_officer_name'] ?? null);
            if ($recv !== null && $recv !== '' && is_numeric($recv)) {
                $attributes['receiving_officer'] = $recv;
                $attributes['receiving_officer_name'] = \App\User::find((int) $recv)->name ?? $header->receiving_officer_name;
            } else {
                $attributes['receiving_officer_name'] = $getSingleValue($headerData['receiving_officer_name'] ?? null) ?? $header->receiving_officer_name;
                if (array_key_exists('receiving_officer', $headerData)) {
                    $attributes['receiving_officer'] = $headerData['receiving_officer'];
                }
            }
        }

        if (array_key_exists('sampling_officer_name', $headerData)) {
            $attributes['sampling_officer_name'] = $getSingleValue($headerData['sampling_officer_name'] ?? null);
        }

        if (array_key_exists('radio_active_levels', $headerData)) {
            $raw = $getSingleValue($headerData['radio_active_levels'] ?? null);
            if ($raw !== null && $raw !== '') {
                $attributes['radio_active_levels'] = date('H:i:s', strtotime((string) $raw) ?: time());
            }
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $proposed
     */
    private function headerAttributeMapDiffersFromModel(SampleHeader $header, array $proposed): bool
    {
        $fillKeys = $proposed;
        $crmUnitId = $fillKeys['crm_unit_id'] ?? '';
        $crmUnitName = $fillKeys['crm_unit_name'] ?? '';
        unset($fillKeys['crm_unit_id'], $fillKeys['crm_unit_name']);

        $fillKeys = array_filter(
            $fillKeys,
            static fn ($v) => $v !== null && $v !== ''
        );

        foreach ($fillKeys as $key => $value) {
            if (! $this->valuesAreEquivalentForHeaderSync($header->{$key} ?? null, $value, (string) $key)) {
                return true;
            }
        }

        if (! $this->valuesAreEquivalentForHeaderSync($header->crm_unit_id ?? null, $crmUnitId, 'crm_unit_id')) {
            return true;
        }

        if (! $this->valuesAreEquivalentForHeaderSync($header->crm_unit_name ?? null, $crmUnitName, 'crm_unit_name')) {
            return true;
        }

        return false;
    }

    private function valuesAreEquivalentForHeaderSync(mixed $current, mixed $proposed, string $key): bool
    {
        $dateKeys = ['receipt_date', 'date_collected', 'date_expected'];
        $intKeys = [
            'crm_customer_id', 'crm_contact_id', 'sampling_method_id', 'require_mu', 'quote_id',
            'is_routine', 'routine_frequency', 'is_client_order', 'receiving_officer',
        ];

        $normCurrent = $this->normalizeHeaderValueForCompare($current, $key, $dateKeys, $intKeys);
        $normProposed = $this->normalizeHeaderValueForCompare($proposed, $key, $dateKeys, $intKeys);

        return $normCurrent === $normProposed;
    }

    /**
     * @param  array<int, string>  $dateKeys
     * @param  array<int, string>  $intKeys
     */
    private function normalizeHeaderValueForCompare(mixed $value, string $key, array $dateKeys, array $intKeys): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (in_array($key, $dateKeys, true)) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }
            if (is_string($value)) {
                try {
                    return Carbon::parse($value)->format('Y-m-d');
                } catch (\Throwable) {
                    return trim($value);
                }
            }

            return $value;
        }

        if ($key === 'crm_unit_id' || in_array($key, $intKeys, true) || str_ends_with($key, '_id')) {
            if ($value === '' || $value === null) {
                return null;
            }
            if (is_numeric($value)) {
                return (int) $value;
            }

            return is_string($value) ? trim($value) : $value;
        }

        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }

    private function unprocessedStagingWouldChange(SampleHeader $header, array $slice, int $customerId): bool
    {
        $stagingRows = SampleDetailStaging::query()
            ->where('sample_header_id', $header->id)
            ->where('is_processed', false)
            ->get();

        if ($stagingRows->isEmpty()) {
            return false;
        }

        foreach ($stagingRows as $staging) {
            $current = is_array($staging->data_json) ? $staging->data_json : [];
            $mergeWarnings = [];
            $merged = $this->mergeStagingSubUnitIntoData($current, $slice, $customerId, $mergeWarnings);

            $subKeys = ['company_sub_unit_id', 'company_sub_unit_name', 'company_sub_unit_code'];
            foreach ($subKeys as $subKey) {
                if (! $this->stagingSubUnitValuesAreEquivalent($current[$subKey] ?? null, $merged[$subKey] ?? null)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $warnings
     * @return array<string, mixed>
     */
    private function mergeStagingSubUnitIntoData(array $data, array $slice, int $customerId, array &$warnings): array
    {
        if (! is_array($data)) {
            $data = [];
        }

        $subUnitId = $this->resolveCompanySubUnitIdFromSlice($slice);

        if ($subUnitId !== null) {
            $sub = CRMCompanySubUnit::query()->find($subUnitId);
            if (! $sub || (int) $sub->crm_customer_id !== $customerId) {
                $data['company_sub_unit_id'] = null;
                $data['company_sub_unit_name'] = null;
                $data['company_sub_unit_code'] = null;
                $warnings[] = 'company_sub_unit_invalid';
            } else {
                $data['company_sub_unit_id'] = $subUnitId;
                $data['company_sub_unit_name'] = $sub->name ?? 'N/A';
                $data['company_sub_unit_code'] = $sub->code ?? 'N/A';
            }
        } else {
            $existingSubId = $data['company_sub_unit_id'] ?? null;
            if ($existingSubId !== null && $existingSubId !== '' && is_numeric($existingSubId)) {
                $existingSub = CRMCompanySubUnit::query()->find((int) $existingSubId);
                if (! $existingSub || (int) $existingSub->crm_customer_id !== $customerId) {
                    $data['company_sub_unit_id'] = null;
                    $data['company_sub_unit_name'] = null;
                    $data['company_sub_unit_code'] = null;
                    $warnings[] = 'staging_sub_unit_cleared';
                }
            }
        }

        return $data;
    }

    private function stagingSubUnitValuesAreEquivalent(mixed $a, mixed $b): bool
    {
        $norm = static function (mixed $v): ?string {
            if ($v === null || $v === '') {
                return null;
            }
            if (is_numeric($v)) {
                return (string) (int) $v;
            }

            return trim((string) $v);
        };

        return $norm($a) === $norm($b);
    }

    /**
     * @param  array<string, mixed>  $headerData
     * @return array{0: int|string, 1: string}
     */
    private function resolveCrmUnit(array $headerData, callable $getSingleValue, callable $getIntegerValue): array
    {
        $crmCustomerId = $getIntegerValue($headerData['crm_customer_id'] ?? null);
        $crmUnit = $getSingleValue($headerData['crm_unit_name'] ?? null);
        $crmUnitId = '';
        $crmUnitName = '';

        if ($crmCustomerId && ! $crmUnit) {
            $firstUnit = CRMCompanyUnit::where('crm_customer_id', $crmCustomerId)->first();
            if ($firstUnit) {
                $crmUnitName = $firstUnit->name;
                $crmUnitId = $firstUnit->id;
            }
        } else {
            $unit = CRMCompanyUnit::find((int) $crmUnit);
            $crmUnitId = $unit->id ?? '';
            $crmUnitName = $unit->name ?? '';
        }

        return [$crmUnitId, $crmUnitName];
    }

    /**
     * @return array{staging_processed_only: bool, sub_unit_problem: ?string}
     */
    private function syncStagingForHeader(SampleHeader $header, array $slice): array
    {
        $stagingRows = SampleDetailStaging::query()
            ->where('sample_header_id', $header->id)
            ->where('is_processed', false)
            ->get();

        if ($stagingRows->isEmpty()) {
            $processed = SampleDetailStaging::query()
                ->where('sample_header_id', $header->id)
                ->where('is_processed', true)
                ->exists();

            return [
                'staging_processed_only' => $processed,
                'sub_unit_problem' => null,
            ];
        }

        $customerId = (int) $header->crm_customer_id;
        $subUnitProblem = null;

        foreach ($stagingRows as $staging) {
            $data = $staging->data_json ?? [];
            if (! is_array($data)) {
                $data = [];
            }

            $rowWarnings = [];
            $data = $this->mergeStagingSubUnitIntoData($data, $slice, $customerId, $rowWarnings);

            foreach ($rowWarnings as $code) {
                if ($code === 'company_sub_unit_invalid') {
                    $subUnitProblem = 'invalid';
                }
                if ($code === 'staging_sub_unit_cleared') {
                    $subUnitProblem = $subUnitProblem ?? 'cleared';
                }
            }

            $staging->data_json = $data;
            $staging->save();
        }

        return [
            'staging_processed_only' => false,
            'sub_unit_problem' => $subUnitProblem,
        ];
    }

    /**
     * @return array{crm_customer_id: int|null, crm_unit_id: int|string|null, crm_unit_name: ?string}
     */
    private function snapshotBatchPaperwork(SampleHeader $header): array
    {
        $cid = $header->crm_customer_id;

        return [
            'crm_customer_id' => $cid !== null && $cid !== '' && is_numeric($cid) ? (int) $cid : null,
            'crm_unit_id' => $header->crm_unit_id,
            'crm_unit_name' => $header->crm_unit_name !== null && $header->crm_unit_name !== '' ? (string) $header->crm_unit_name : null,
        ];
    }

    /**
     * @param  array{crm_customer_id: int|null, crm_unit_id: int|string|null, crm_unit_name: ?string}  $snapshot
     * @param  array{staging_processed_only: bool, sub_unit_problem: ?string}  $stagingMeta
     */
    private function buildBatchApplySummaryLine(
        SampleHeader $header,
        array $snapshot,
        bool $hadSamples,
        array $stagingMeta
    ): ?string {
        $parts = [];

        $changeBits = $this->describePaperworkChanges($snapshot, $header);
        if ($changeBits !== []) {
            $parts[] = implode('; ', $changeBits);
        }

        if ($stagingMeta['staging_processed_only']) {
            $parts[] = 'samples already logged — site/sub-unit from form not re-applied';
        }

        if ($stagingMeta['sub_unit_problem'] === 'invalid') {
            $parts[] = 'site/sub-unit cleared (did not match client)';
        } elseif ($stagingMeta['sub_unit_problem'] === 'cleared') {
            $parts[] = 'old site cleared (wrong client)';
        }

        if ($hadSamples && ($this->customerIdChanged($snapshot, $header) || $this->companyUnitChanged($snapshot, $header))) {
            $parts[] = 'check samples';
        }

        if ($parts === []) {
            return null;
        }

        return 'Batch '.$header->batch_code.' — '.implode('. ', $parts).'.';
    }

    /**
     * @param  array{crm_customer_id: int|null, crm_unit_id: int|string|null, crm_unit_name: ?string}  $snapshot
     * @return array<int, string>
     */
    private function describePaperworkChanges(array $snapshot, SampleHeader $header): array
    {
        $out = [];

        if ($this->customerIdChanged($snapshot, $header)) {
            $from = $this->resolveCustomerDisplayLabel($snapshot['crm_customer_id']);
            $to = $this->resolveCustomerDisplayLabel(
                $header->crm_customer_id !== null && $header->crm_customer_id !== '' && is_numeric($header->crm_customer_id)
                    ? (int) $header->crm_customer_id
                    : null
            );
            $out[] = "Customer: {$from} → {$to}";
        }

        if ($this->companyUnitChanged($snapshot, $header)) {
            $from = $this->formatUnitLabel($snapshot['crm_unit_name'], $snapshot['crm_unit_id']);
            $to = $this->formatUnitLabel(
                $header->crm_unit_name !== null && $header->crm_unit_name !== '' ? (string) $header->crm_unit_name : null,
                $header->crm_unit_id
            );
            $out[] = "Unit: {$from} → {$to}";
        }

        return $out;
    }

    /**
     * @param  array{crm_customer_id: int|null, crm_unit_id: int|string|null, crm_unit_name: ?string}  $snapshot
     */
    private function customerIdChanged(array $snapshot, SampleHeader $header): bool
    {
        $after = $header->crm_customer_id;
        $afterInt = $after !== null && $after !== '' && is_numeric($after) ? (int) $after : null;

        return $snapshot['crm_customer_id'] !== $afterInt;
    }

    /**
     * @param  array{crm_customer_id: int|null, crm_unit_id: int|string|null, crm_unit_name: ?string}  $snapshot
     */
    private function companyUnitChanged(array $snapshot, SampleHeader $header): bool
    {
        $beforeName = $snapshot['crm_unit_name'] ?? '';
        $afterName = $header->crm_unit_name !== null && $header->crm_unit_name !== '' ? trim((string) $header->crm_unit_name) : '';
        $beforeId = $this->normalizeUnitIdScalar($snapshot['crm_unit_id']);
        $afterId = $this->normalizeUnitIdScalar($header->crm_unit_id);

        if ($beforeName !== $afterName) {
            return true;
        }

        return $beforeId !== $afterId;
    }

    private function normalizeUnitIdScalar(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function formatUnitLabel(?string $name, mixed $unitId): string
    {
        $n = $name !== null && trim($name) !== '' ? trim($name) : null;
        if ($n !== null) {
            return $n;
        }
        $id = $this->normalizeUnitIdScalar($unitId);

        return $id !== null ? '(unit #'.$id.')' : '—';
    }

    private function resolveCustomerDisplayLabel(?int $customerId): string
    {
        if ($customerId === null) {
            return '—';
        }
        $name = CRMCustomer::query()->whereKey($customerId)->value('name');
        if ($name !== null && trim((string) $name) !== '') {
            return trim((string) $name);
        }

        return '(client #'.$customerId.')';
    }

    /**
     * @param  array{sample_header: array, sample_details: array}  $slice
     */
    private function resolveCompanySubUnitIdFromSlice(array $slice): ?int
    {
        $header = $slice['sample_header'] ?? [];
        $details = $slice['sample_details'] ?? [];
        $firstDetail = $details[0] ?? [];

        $raw = $header['company_sub_unit_id'] ?? $firstDetail['company_sub_unit_id'] ?? null;

        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_array($raw)) {
            $raw = reset($raw);
        }

        return is_numeric($raw) ? (int) $raw : null;
    }
}
