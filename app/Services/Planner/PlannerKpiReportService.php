<?php

namespace App\Services\Planner;

use App\AnalysisType;
use App\Analyte;
use App\Models\CRM\CRMCustomer;
use App\Models\SamplingSchedule;
use App\Models\SubmissionFormInstance;
use App\SampleType;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class PlannerKpiReportService
{
    public function __construct(
        private readonly SubmissionFormValueNormalizer $valueNormalizer,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function getReportRows(array $filters, ?string $companyId = null): Collection
    {
        $companyId ??= getUserCompany();

        $schedules = $this->baseQuery($filters, $companyId)
            ->with([
                'client',
                'contact',
                'personnel',
                'sample_type',
                'analysis_type',
                'submissionFormInstances.values.element',
                'submissionFormInstances.submissionForm.sampleTypes',
            ])
            ->orderByDesc('sampling_datetime')
            ->get();

        $lookup = $this->buildLookupMaps($schedules);

        return $schedules
            ->map(fn (SamplingSchedule $schedule): array => $this->buildRow($schedule, $lookup))
            ->filter(fn (array $row): bool => $this->matchesPostFilters($row, $filters, $lookup))
            ->values();
    }

    /**
     * @param  Collection<int, SamplingSchedule>  $schedules
     * @return array{
     *     sample_types: array<string, string>,
     *     analysis_types: array<string, string>,
     *     analytes: array<string, string>
     * }
     */
    private function buildLookupMaps(Collection $schedules): array
    {
        $sampleTypeIds = [];
        $analysisTypeIds = [];
        $analyteIds = [];

        foreach ($schedules as $schedule) {
            if ($schedule->sample_type_id) {
                $sampleTypeIds[] = (string) $schedule->sample_type_id;
            }
            if ($schedule->analysis_type_id) {
                $analysisTypeIds[] = (string) $schedule->analysis_type_id;
            }

            foreach ($schedule->sample_details ?? [] as $entry) {
                if (! empty($entry['sample_type_id'])) {
                    $sampleTypeIds[] = (string) $entry['sample_type_id'];
                }
                if (! empty($entry['analysis_type_id'])) {
                    $analysisTypeIds[] = (string) $entry['analysis_type_id'];
                }
                foreach ($entry['parameters'] ?? [] as $paramId) {
                    $analyteIds[] = (string) $paramId;
                }
            }

            foreach ($schedule->parameters ?? [] as $paramId) {
                $analyteIds[] = (string) $paramId;
            }

            foreach ($schedule->submissionFormInstances as $instance) {
                if ($instance->selected_sample_type_id) {
                    $sampleTypeIds[] = (string) $instance->selected_sample_type_id;
                }
            }
        }

        return [
            'sample_types' => SampleType::query()
                ->whereIn('id', array_unique(array_filter($sampleTypeIds)))
                ->pluck('name', 'id')
                ->all(),
            'analysis_types' => AnalysisType::query()
                ->whereIn('id', array_unique(array_filter($analysisTypeIds)))
                ->pluck('name', 'id')
                ->all(),
            'analytes' => Analyte::query()
                ->whereIn('id', array_unique(array_filter($analyteIds)))
                ->pluck('name', 'id')
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function baseQuery(array $filters = [], ?string $companyId = null): Builder
    {
        $companyId ??= getUserCompany();

        $query = SamplingSchedule::query()
            ->where('company_id', $companyId);

        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(function (Builder $searchQuery) use ($term): void {
                $searchQuery
                    ->where('title', 'like', $term)
                    ->orWhere('location', 'like', $term)
                    ->orWhere('frequency', 'like', $term)
                    ->orWhereHas('client', function (Builder $clientQuery) use ($term): void {
                        $clientQuery->where('name', 'like', $term);
                    });
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('sampling_datetime', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('sampling_datetime', '<=', $filters['date_to']);
        }

        if (! empty($filters['client_id'])) {
            $query->where('crm_customer_id', $filters['client_id']);
        }

        if (! empty($filters['contact_id'])) {
            $query->where('contact_id', $filters['contact_id']);
        }

        if (! empty($filters['frequency'])) {
            $query->where('frequency', $filters['frequency']);
        }

        if (! empty($filters['sample_type_id'])) {
            $sampleTypeId = $filters['sample_type_id'];
            $query->where(function (Builder $q) use ($sampleTypeId): void {
                $q->whereJsonContains('sample_details', [['sample_type_id' => $sampleTypeId]])
                    ->orWhere('sample_type_id', $sampleTypeId);
            });
        }

        if (! empty($filters['analysis_type_id'])) {
            $analysisTypeId = $filters['analysis_type_id'];
            $query->where(function (Builder $q) use ($analysisTypeId): void {
                $q->whereJsonContains('sample_details', [['analysis_type_id' => $analysisTypeId]])
                    ->orWhere('analysis_type_id', $analysisTypeId);
            });
        }

        if (isset($filters['scheduled_min']) && $filters['scheduled_min'] !== '' && $filters['scheduled_min'] !== null) {
            $query->where('number_of_samples', '>=', (int) $filters['scheduled_min']);
        }

        if (isset($filters['scheduled_max']) && $filters['scheduled_max'] !== '' && $filters['scheduled_max'] !== null) {
            $query->where('number_of_samples', '<=', (int) $filters['scheduled_max']);
        }

        if (! empty($filters['parameter_id'])) {
            $parameterId = $filters['parameter_id'];
            $query->where(function (Builder $q) use ($parameterId): void {
                $q->whereJsonContains('sample_details', [['parameters' => [$parameterId]]])
                    ->orWhereJsonContains('parameters', $parameterId);
            });
        }

        if (! empty($filters['contract_valid_from'])) {
            $query->whereHas('client', function (Builder $clientQuery) use ($filters): void {
                $clientQuery->whereDate('contract_valid_from', '>=', $filters['contract_valid_from']);
            });
        }

        if (! empty($filters['contract_valid_to'])) {
            $query->whereHas('client', function (Builder $clientQuery) use ($filters): void {
                $clientQuery->whereDate('contract_valid_to', '<=', $filters['contract_valid_to']);
            });
        }

        return $query;
    }

    /**
     * @param  array{sample_types: array<string, string>, analysis_types: array<string, string>, analytes: array<string, string>}  $lookup
     * @return array<string, mixed>
     */
    public function buildRow(SamplingSchedule $schedule, array $lookup = []): array
    {
        if ($lookup === []) {
            $lookup = $this->buildLookupMaps(collect([$schedule]));
        }

        $scheduled = $this->extractScheduledInfo($schedule, $lookup);
        $collected = $this->extractCollectedInfo($schedule, $lookup);
        $client = $schedule->client;
        $contact = $schedule->contact;

        $contactName = $contact
            ? trim(($contact->first_name ?? '').' '.($contact->middle_name ?? '').' '.($contact->last_name ?? ''))
            : '';
        $contactEmail = $contact->email ?? '';
        $contactPhone = $contact->telephone ?? $contact->mobile ?? '';

        $contractFrom = $this->formatDate($client?->contract_valid_from);
        $contractTo = $this->formatDate($client?->contract_valid_to);

        $collectedAt = $schedule->submissionFormInstances
            ->pluck('submitted_at')
            ->filter()
            ->sortDesc()
            ->first();

        $status = $this->resolveCollectionStatus($schedule, $scheduled, $collected);

        return [
            'schedule_id' => $schedule->id,
            'title' => $schedule->title,
            'date' => $schedule->sampling_datetime?->format('Y-m-d') ?? 'N/A',
            'time' => $schedule->sampling_datetime?->format('H:i') ?? '',
            'client_name' => $client?->name ?? 'N/A',
            'contract_validity' => $contractFrom.' — '.$contractTo,
            'contract_valid_from' => $contractFrom,
            'contract_valid_to' => $contractTo,
            'contact_name' => $contactName ?: 'N/A',
            'contact_email' => $contactEmail ?: 'N/A',
            'contact_phone' => $contactPhone ?: 'N/A',
            'contact_details' => $this->formatContactDetails($contactName, $contactEmail, $contactPhone),
            'frequency' => $schedule->frequency ?? 'One-time',
            'location' => $schedule->location ?? '',
            'personnel' => $schedule->personnel?->name ?? 'N/A',
            'scheduled_categories' => $scheduled['categories'],
            'scheduled_details' => $scheduled['details'],
            'scheduled_samples' => $scheduled['samples_count'],
            'scheduled_parameters' => $scheduled['parameters'],
            'collected_categories' => $collected['categories'],
            'collected_details' => $collected['details'],
            'collected_samples' => $collected['samples_count'],
            'collected_parameters' => $collected['parameters'],
            'collected_at' => $collectedAt?->format('Y-m-d H:i') ?? ($schedule->is_collected ? $schedule->updated_at?->format('Y-m-d H:i') : null),
            'status' => $status,
            'is_collected' => (bool) $schedule->is_collected,
        ];
    }

    /**
     * @param  array{categories: string, details: string, samples_count: int, parameters: string}  $scheduled
     * @param  array{categories: string, details: string, samples_count: int, parameters: string}  $collected
     */
    private function resolveCollectionStatus(SamplingSchedule $schedule, array $scheduled, array $collected): string
    {
        if (! $schedule->is_collected) {
            return 'pending';
        }

        if ($schedule->submissionFormInstances->isEmpty()) {
            return 'collected';
        }

        $scheduledCount = $scheduled['samples_count'];
        $collectedCount = $collected['samples_count'];

        if ($collectedCount <= 0) {
            return 'partial';
        }

        if ($collectedCount < $scheduledCount) {
            return 'partial';
        }

        return 'collected';
    }

    /**
     * @param  array{sample_types: array<string, string>, analysis_types: array<string, string>, analytes: array<string, string>}  $lookup
     * @return array{categories: string, details: string, samples_count: int, parameters: string}
     */
    private function extractScheduledInfo(SamplingSchedule $schedule, array $lookup): array
    {
        $categories = [];
        $details = [];
        $parameters = [];

        $entries = $schedule->sample_details;
        if (empty($entries) || ! is_array($entries)) {
            if ($schedule->sample_type_id) {
                $stName = $lookup['sample_types'][$schedule->sample_type_id]
                    ?? $schedule->sample_type?->name
                    ?? null;
                if ($stName) {
                    $categories[] = $stName;
                }
                if ($schedule->analysis_type_id) {
                    $atName = $lookup['analysis_types'][$schedule->analysis_type_id]
                        ?? $schedule->analysis_type?->name
                        ?? null;
                    if ($atName) {
                        $details[] = $atName;
                    }
                }
                foreach ($schedule->parameters ?? [] as $paramId) {
                    $name = $lookup['analytes'][(string) $paramId] ?? null;
                    if ($name) {
                        $parameters[] = $name;
                    }
                }
            }
        } else {
            foreach ($entries as $entry) {
                if (! empty($entry['sample_type_id'])) {
                    $stName = $lookup['sample_types'][(string) $entry['sample_type_id']] ?? null;
                    if ($stName) {
                        $categories[] = $stName;
                    }
                }
                if (! empty($entry['analysis_type_id'])) {
                    $atName = $lookup['analysis_types'][(string) $entry['analysis_type_id']] ?? null;
                    if ($atName) {
                        $details[] = $atName;
                    }
                }
                foreach ($entry['parameters'] ?? [] as $paramId) {
                    $name = $lookup['analytes'][(string) $paramId] ?? null;
                    if ($name) {
                        $parameters[] = $name;
                    }
                }
            }
        }

        return [
            'categories' => implode('; ', array_unique(array_filter($categories))) ?: 'N/A',
            'details' => implode('; ', array_unique(array_filter($details))) ?: 'N/A',
            'samples_count' => (int) ($schedule->number_of_samples ?? 1),
            'parameters' => implode('; ', array_unique(array_filter($parameters))) ?: 'N/A',
        ];
    }

    /**
     * @param  array{sample_types: array<string, string>, analysis_types: array<string, string>, analytes: array<string, string>}  $lookup
     * @return array{categories: string, details: string, samples_count: int, parameters: string}
     */
    private function extractCollectedInfo(SamplingSchedule $schedule, array $lookup): array
    {
        if (! $schedule->is_collected) {
            return $this->emptyCollected();
        }

        if ($schedule->submissionFormInstances->isEmpty()) {
            return [
                'categories' => 'Not recorded',
                'details' => 'Not recorded',
                'samples_count' => 0,
                'parameters' => 'Not recorded',
            ];
        }

        $categories = [];
        $details = [];
        $parameters = [];
        $samplesCount = 0;

        foreach ($schedule->submissionFormInstances as $instance) {
            $instanceData = $this->extractInstanceCollectedInfo($instance, $schedule, $lookup);
            $categories = array_merge($categories, $instanceData['category_names']);
            $details = array_merge($details, $instanceData['detail_names']);
            $parameters = array_merge($parameters, $instanceData['parameter_names']);
            $samplesCount += $instanceData['samples_count'];
        }

        return [
            'categories' => implode('; ', array_unique(array_filter($categories))) ?: 'N/A',
            'details' => implode('; ', array_unique(array_filter($details))) ?: 'N/A',
            'samples_count' => $samplesCount,
            'parameters' => implode('; ', array_unique(array_filter($parameters))) ?: 'N/A',
        ];
    }

    /**
     * @return array{categories: string, details: string, samples_count: int, parameters: string}
     */
    private function emptyCollected(): array
    {
        return [
            'categories' => '—',
            'details' => '—',
            'samples_count' => 0,
            'parameters' => '—',
        ];
    }

    /**
     * @param  array{sample_types: array<string, string>, analysis_types: array<string, string>, analytes: array<string, string>}  $lookup
     * @return array{category_names: list<string>, detail_names: list<string>, parameter_names: list<string>, samples_count: int}
     */
    private function extractInstanceCollectedInfo(
        SubmissionFormInstance $instance,
        SamplingSchedule $schedule,
        array $lookup,
    ): array {
        $instance->loadMissing(['values.element', 'submissionForm.sampleTypes']);

        $categoryNames = $instance->getResolvedSampleTypeNames();
        $detailNames = [];
        $parameterNames = [];
        $samplesCount = 0;

        foreach ($instance->values as $value) {
            $element = $value->element;
            if ($element === null || $value->isEmpty()) {
                continue;
            }

            $elementType = (string) ($element->element_type ?? '');
            $elementName = Str::lower(trim((string) ($element->name ?? '').' '.($element->label ?? '')));

            if ($elementType === 'analysis_type_select' || $elementName === 'analysis type') {
                $resolved = trim((string) $instance->resolveDisplayValue($element, $value->value, $value->array_index));
                if ($resolved !== '' && $resolved !== 'N/A' && $resolved !== '-') {
                    $detailNames[] = $resolved;
                }
            }

            if ($elementType === 'analysis_elements_select'
                || Str::contains($elementName, ['parameter', 'test required', 'tests required'])) {
                $tokens = $this->extractInstanceValueTokens($instance, (string) $value->value);
                foreach ($tokens as $token) {
                    $resolved = trim((string) $instance->resolveDisplayValue($element, $token, $value->array_index));
                    if ($resolved !== '' && $resolved !== 'N/A' && $resolved !== '-') {
                        $parameterNames[] = $resolved;
                    }
                }
            }

            if (in_array($elementType, ['number'], true)
                && Str::contains($elementName, ['qty', 'quantity', 'number of samples', 'no. of samples'])) {
                $qty = (int) $value->value;
                if ($qty > 0) {
                    $samplesCount = max($samplesCount, $qty);
                }
            }
        }

        $formData = $this->valueNormalizer->valuesMapFromInstance($instance);

        if ($detailNames === []) {
            $analysisTypeId = $formData['analysis_type_id'] ?? null;
            if (is_array($analysisTypeId)) {
                foreach ($analysisTypeId as $id) {
                    $name = $lookup['analysis_types'][(string) $id] ?? AnalysisType::query()->whereKey($id)->value('name');
                    if ($name) {
                        $detailNames[] = $name;
                    }
                }
            } elseif ($analysisTypeId) {
                $name = $lookup['analysis_types'][(string) $analysisTypeId] ?? AnalysisType::query()->whereKey($analysisTypeId)->value('name');
                if ($name) {
                    $detailNames[] = $name;
                }
            }

            $analysisTypeName = $formData['analysis_type'] ?? $formData['analysis_types'] ?? null;
            if (is_array($analysisTypeName)) {
                $detailNames = array_merge($detailNames, array_filter($analysisTypeName, 'is_string'));
            } elseif (is_string($analysisTypeName) && $analysisTypeName !== '') {
                $detailNames[] = $analysisTypeName;
            }
        }

        if ($parameterNames === []) {
            $rawParams = $formData['parameters'] ?? [];
            if (is_string($rawParams) && $rawParams !== '') {
                $parameterNames = array_merge($parameterNames, $this->tokenizeList($rawParams));
            } elseif (is_array($rawParams)) {
                foreach ($rawParams as $param) {
                    if (is_array($param)) {
                        $parameterNames = array_merge($parameterNames, array_filter($param, 'is_string'));
                    } elseif (is_string($param) && $param !== '') {
                        $parameterNames = array_merge($parameterNames, $this->tokenizeList($param));
                    }
                }
            }
        }

        $rows = is_array($formData['sample_rows'] ?? null) ? $formData['sample_rows'] : [];
        if ($rows !== []) {
            $rowCount = count($rows);
            $samplesCount = max($samplesCount, $rowCount);

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                if (! empty($row['parameters'])) {
                    $parameterNames = array_merge($parameterNames, $this->tokenizeList((string) $row['parameters']));
                }

                if (! empty($row['analysis_type_id'])) {
                    $name = $lookup['analysis_types'][(string) $row['analysis_type_id']]
                        ?? AnalysisType::query()->whereKey($row['analysis_type_id'])->value('name');
                    if ($name) {
                        $detailNames[] = $name;
                    }
                }

                if (isset($row['number_of_samples']) && (int) $row['number_of_samples'] > 0) {
                    $samplesCount = max($samplesCount, (int) $row['number_of_samples']);
                } elseif (isset($row['qty']) && (int) $row['qty'] > 0) {
                    $samplesCount = max($samplesCount, (int) $row['qty']);
                }
            }
        }

        if ($samplesCount === 0 && isset($formData['number_of_samples']) && (int) $formData['number_of_samples'] > 0) {
            $samplesCount = (int) $formData['number_of_samples'];
        }

        if ($samplesCount === 0 && $rows !== []) {
            $samplesCount = count($rows);
        }

        if ($samplesCount === 0 && $schedule->is_collected) {
            $samplesCount = 1;
        }

        $scheduleFallback = $this->scheduleFallbackForInstance($instance, $schedule, $lookup);
        if ($categoryNames === []) {
            $categoryNames = $scheduleFallback['category_names'];
        }
        if ($detailNames === []) {
            $detailNames = $scheduleFallback['detail_names'];
        }
        if ($parameterNames === []) {
            $parameterNames = $scheduleFallback['parameter_names'];
        }

        return [
            'category_names' => array_values(array_unique(array_filter($categoryNames))),
            'detail_names' => array_values(array_unique(array_filter($detailNames))),
            'parameter_names' => array_values(array_unique(array_filter($parameterNames))),
            'samples_count' => $samplesCount,
        ];
    }

    /**
     * When a TRF submission omits analysis/parameter fields, fall back to the linked schedule entry.
     *
     * @param  array{sample_types: array<string, string>, analysis_types: array<string, string>, analytes: array<string, string>}  $lookup
     * @return array{category_names: list<string>, detail_names: list<string>, parameter_names: list<string>}
     */
    private function scheduleFallbackForInstance(
        SubmissionFormInstance $instance,
        SamplingSchedule $schedule,
        array $lookup,
    ): array {
        $selectedTypeId = (string) ($instance->selected_sample_type_id ?? $schedule->sample_type_id ?? '');
        $entries = is_array($schedule->sample_details) ? $schedule->sample_details : [];
        $match = null;

        foreach ($entries as $entry) {
            if ($selectedTypeId !== '' && (string) ($entry['sample_type_id'] ?? '') === $selectedTypeId) {
                $match = $entry;
                break;
            }
        }

        if ($match === null && $schedule->sample_type_id) {
            $match = [
                'sample_type_id' => $schedule->sample_type_id,
                'analysis_type_id' => $schedule->analysis_type_id,
                'parameters' => $schedule->parameters ?? [],
            ];
        }

        if ($match === null) {
            return ['category_names' => [], 'detail_names' => [], 'parameter_names' => []];
        }

        $categoryNames = [];
        $detailNames = [];
        $parameterNames = [];

        if (! empty($match['sample_type_id'])) {
            $name = $lookup['sample_types'][(string) $match['sample_type_id']] ?? null;
            if ($name) {
                $categoryNames[] = $name;
            }
        }

        if (! empty($match['analysis_type_id'])) {
            $name = $lookup['analysis_types'][(string) $match['analysis_type_id']] ?? null;
            if ($name) {
                $detailNames[] = $name;
            }
        }

        foreach ($match['parameters'] ?? [] as $paramId) {
            $name = $lookup['analytes'][(string) $paramId] ?? null;
            if ($name) {
                $parameterNames[] = $name;
            }
        }

        return [
            'category_names' => $categoryNames,
            'detail_names' => $detailNames,
            'parameter_names' => $parameterNames,
        ];
    }

    /**
     * @return list<string>
     */
    private function extractInstanceValueTokens(SubmissionFormInstance $instance, string $rawValue): array
    {
        $decoded = json_decode($rawValue, true);
        if (is_array($decoded)) {
            $tokens = [];
            foreach ($decoded as $item) {
                if (is_string($item)) {
                    $tokens[] = $item;
                } elseif (is_array($item)) {
                    foreach (['value', 'id', 'uuid'] as $key) {
                        if (! empty($item[$key]) && is_string($item[$key])) {
                            $tokens[] = $item[$key];
                            break;
                        }
                    }
                }
            }

            return array_values(array_filter($tokens, static fn (string $t): bool => trim($t) !== ''));
        }

        return $this->tokenizeList($rawValue);
    }

    /**
     * @return list<string>
     */
    private function tokenizeList(string $value): array
    {
        $parts = preg_split('/[,;|]+/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn (string $p): bool => $p !== ''));
    }

    private function formatContactDetails(string $name, string $email, string $phone): string
    {
        $parts = array_filter([
            $name !== '' ? $name : null,
            $email !== '' ? $email : null,
            $phone !== '' ? $phone : null,
        ]);

        return $parts !== [] ? implode(' | ', $parts) : 'N/A';
    }

    private function formatDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return substr((string) $value, 0, 10) ?: 'N/A';
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $filters
     * @param  array{sample_types: array<string, string>, analysis_types: array<string, string>, analytes: array<string, string>}  $lookup
     */
    private function matchesPostFilters(array $row, array $filters, array $lookup): bool
    {
        if (isset($filters['collected_min']) && $filters['collected_min'] !== '' && $filters['collected_min'] !== null) {
            if ($row['collected_samples'] < (int) $filters['collected_min']) {
                return false;
            }
        }

        if (isset($filters['collected_max']) && $filters['collected_max'] !== '' && $filters['collected_max'] !== null) {
            if ($row['collected_samples'] > (int) $filters['collected_max']) {
                return false;
            }
        }

        if (! empty($filters['parameter_id'])) {
            $paramName = $lookup['analytes'][(string) $filters['parameter_id']] ?? null;
            if ($paramName !== null) {
                $needle = strtolower($paramName);
                $inScheduled = str_contains(strtolower($row['scheduled_parameters']), $needle);
                $inCollected = str_contains(strtolower($row['collected_parameters']), $needle);
                if (! $inScheduled && ! $inCollected) {
                    return false;
                }
            }
        }

        if (! empty($filters['collection_status']) && $filters['collection_status'] !== 'all') {
            if ($row['status'] !== $filters['collection_status']) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{total: int, collected: int, pending: int, partial: int, collection_rate: float}
     */
    public function summarize(Collection $rows): array
    {
        $total = $rows->count();
        $collected = $rows->where('status', 'collected')->count();
        $pending = $rows->where('status', 'pending')->count();
        $partial = $rows->where('status', 'partial')->count();

        $scheduledSamples = (int) $rows->sum('scheduled_samples');
        $collectedSamples = (int) $rows->sum('collected_samples');
        $collectionRate = $scheduledSamples > 0
            ? round($collectedSamples / $scheduledSamples * 100, 1)
            : 0.0;

        return [
            'total' => $total,
            'collected' => $collected,
            'pending' => $pending,
            'partial' => $partial,
            'collection_rate' => $collectionRate,
        ];
    }

    /**
     * @return list<CRMCustomer>
     */
    public function activeClients(): array
    {
        return CRMCustomer::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get()
            ->all();
    }
}
