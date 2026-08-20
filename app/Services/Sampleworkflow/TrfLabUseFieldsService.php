<?php

namespace App\Services\Sampleworkflow;

use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\SampleCondition;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Merges "FOR LAB USE ONLY" fields captured at physical check-in into the
 * linked SubmissionFormInstance values and regenerates its PDF.
 *
 * Field names:
 *   lab_received_datetime  — ISO-8601 local datetime string
 *   lab_received_by        — Name of the staff member receiving the samples
 *   lab_sample_condition   — "Acceptable" | "Not Acceptable"
 */
final class TrfLabUseFieldsService
{
    public const CONDITION_ACCEPTABLE = 'Acceptable';

    public const CONDITION_NOT_ACCEPTABLE = 'Not Acceptable';

    /** @var list<string> */
    private const LAB_FIELDS = [
        'lab_received_datetime',
        'lab_received_by',
        'lab_sample_condition',
    ];

    public function __construct(
        private readonly TestRequestFormPdfService $pdfService,
    ) {}

    /**
     * Persist lab-use fields after Receive Samples and regenerate the TRF PDF.
     *
     * @param  list<array<string, mixed>>  $configs
     */
    public function applyAfterPhysicalReceive(
        SubmissionFormInstance $instance,
        User $user,
        array $configs,
    ): void {
        $labFields = self::buildFieldsArray(
            now()->format('Y-m-d\TH:i'),
            (string) $user->name,
            self::labSampleConditionFromConfigs($configs),
        );

        $this->mergeIntoInstance($instance, $labFields);
    }

    /**
     * Merge lab-use field values into SFI element values, then regenerate the PDF.
     *
     * @param  array<string, mixed>  $labFields  Keyed by the three field names above.
     */
    public function mergeIntoInstance(SubmissionFormInstance $instance, array $labFields): void
    {
        $instance->loadMissing(['submissionForm.sections.elementHolders.elements', 'values.element']);

        $elementsByName = $instance->submissionForm?->sections
            ->flatMap(fn ($section) => $section->elementHolders)
            ->flatMap(fn ($holder) => $holder->elements)
            ->keyBy(fn (SubmissionFormElement $element) => (string) $element->name)
            ?? collect();

        DB::transaction(function () use ($instance, $labFields, $elementsByName): void {
            foreach (self::LAB_FIELDS as $key) {
                if (! array_key_exists($key, $labFields) || $labFields[$key] === null || $labFields[$key] === '') {
                    continue;
                }

                /** @var SubmissionFormElement|null $element */
                $element = $elementsByName->get($key);
                if ($element === null) {
                    continue;
                }

                $value = SubmissionFormInstanceValue::query()->firstOrNew([
                    'submission_form_instance_id' => $instance->id,
                    'submission_form_element_id' => $element->id,
                    'array_index' => null,
                ]);

                if (! $value->exists) {
                    $value->id = (string) Str::uuid7();
                }

                $value->value = is_scalar($labFields[$key]) ? (string) $labFields[$key] : json_encode($labFields[$key]);
                $value->save();
            }
        });

        try {
            $this->pdfService->generateAndStore($instance->fresh(['values.element', 'submissionForm.sampleTypeCategories', 'sampleSubmissionRequest']));
        } catch (\Throwable) {
            // PDF regeneration failure must not block the check-in flow.
        }
    }

    /**
     * @deprecated Use mergeIntoInstance()
     *
     * @param  array<string, mixed>  $labFields
     */
    public function mergeIntoTrfi(SubmissionFormInstance $instance, array $labFields): void
    {
        $this->mergeIntoInstance($instance, $labFields);
    }

    /**
     * @return array<string, string>
     */
    public static function buildFieldsArray(
        string $labReceivedDatetime,
        string $labReceivedBy,
        string $labSampleCondition,
    ): array {
        return [
            'lab_received_datetime' => $labReceivedDatetime,
            'lab_received_by' => $labReceivedBy,
            'lab_sample_condition' => $labSampleCondition,
        ];
    }

    /**
     * Form-level TRF lab condition: Not Acceptable if any sample is, otherwise Acceptable.
     *
     * @param  list<array<string, mixed>>  $configs
     */
    public static function labSampleConditionFromConfigs(array $configs): string
    {
        foreach (self::conditionNamesForConfigs($configs) as $name) {
            if (self::isNotAcceptableConditionName($name)) {
                return self::CONDITION_NOT_ACCEPTABLE;
            }
        }

        return self::CONDITION_ACCEPTABLE;
    }

    public static function isNotAcceptableConditionName(?string $name): bool
    {
        $normalized = mb_strtolower(trim((string) $name));
        if ($normalized === '') {
            return false;
        }

        $compact = preg_replace('/\s+/u', '', $normalized) ?? $normalized;

        return $compact === 'notacceptable'
            || str_contains($normalized, 'not acceptable');
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return array<string, string> config id => condition name
     */
    public static function conditionNameByConfigId(array $configs): array
    {
        $namesById = self::conditionNamesByConditionId($configs);
        $map = [];

        foreach ($configs as $config) {
            $configId = (string) ($config['id'] ?? '');
            if ($configId === '') {
                continue;
            }

            $label = self::conditionLabelForConfig($config, $namesById);
            if ($label !== null && $label !== '') {
                $map[$configId] = $label;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, string>|null  $namesByConditionId
     */
    public static function conditionLabelForConfig(array $config, ?array $namesByConditionId = null): ?string
    {
        $direct = trim((string) ($config['sample_condition'] ?? $config['sample_condition_name'] ?? ''));
        if ($direct !== '') {
            return $direct;
        }

        $conditionId = trim((string) ($config['sample_condition_id'] ?? ''));
        if ($conditionId === '') {
            return null;
        }

        $namesByConditionId ??= self::conditionNamesByConditionId([$config]);

        $resolved = trim((string) ($namesByConditionId[$conditionId] ?? ''));

        return $resolved !== '' ? $resolved : null;
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return list<string>
     */
    private static function conditionNamesForConfigs(array $configs): array
    {
        $names = [];
        $namesById = self::conditionNamesByConditionId($configs);

        foreach ($configs as $config) {
            $label = self::conditionLabelForConfig($config, $namesById);
            if ($label !== null && $label !== '') {
                $names[] = $label;
            }
        }

        return $names;
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return array<string, string>
     */
    private static function conditionNamesByConditionId(array $configs): array
    {
        $ids = [];
        foreach ($configs as $config) {
            $id = trim((string) ($config['sample_condition_id'] ?? ''));
            if ($id !== '') {
                $ids[$id] = true;
            }
        }

        if ($ids === []) {
            return [];
        }

        return SampleCondition::query()
            ->whereIn('id', array_keys($ids))
            ->get(['id', 'name'])
            ->mapWithKeys(fn (SampleCondition $condition): array => [
                (string) $condition->id => (string) $condition->name,
            ])
            ->all();
    }
}
