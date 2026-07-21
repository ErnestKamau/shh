<?php

namespace App\Services\Worksheets;

use App\AnalysisMethod;
use App\CapturedResult;
use App\Services\StandardLimitDisplayService;
use App\User;
use Illuminate\Support\Collection;

class WorksheetMetaResolver
{
    /** @var list<string> */
    public const EAGER = [
        'sample',
        'analysis_type',
        'analysisElement.analyte',
        'analysisElement.mmethod',
        'analysisElement.ltmethod',
        'analysisMethod',
        'ltmethod',
        'user',
        'operator',
        'my_analyte',
    ];

    public function __construct(
        private StandardLimitDisplayService $standardLimitDisplay,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     * @return array{
     *     captured_result_id: string,
     *     sample_code: string,
     *     test_name: string,
     *     analyst: string,
     *     method: string,
     *     unit: string,
     *     standard: string,
     *     standard_limit: string
     * }
     */
    public function forCapturedResult(CapturedResult $captured, array $context = []): array
    {
        $element = $captured->resolveAnalysisElement();
        $analystNames = $context['analyst_names'] ?? [];
        $analystFromContext = $analystNames[(string) $captured->id] ?? null;

        return [
            'captured_result_id' => (string) $captured->id,
            'sample_code' => $this->display($captured->sample?->sample_code),
            'test_name' => $this->resolveTestName($captured, $element),
            'analyst' => $this->display($analystFromContext ?? $this->resolveAnalystName($captured, $context)),
            'method' => $this->display($this->resolveMethodName($captured, $element)),
            'unit' => $this->display($captured->effectiveReportingUnitName()),
            'standard' => $this->display($this->standardLimitDisplay->standardNameForCapturedResult($captured)),
            'standard_limit' => $this->display($this->standardLimitDisplay->forCapturedResult($captured)),
        ];
    }

    /**
     * @param  Collection<int, CapturedResult>|array<int, CapturedResult>  $results
     * @return list<array<string, string>>
     */
    public function forMany(Collection|array $results, array $context = []): array
    {
        $collection = $results instanceof Collection ? $results : collect($results);

        return $collection
            ->map(fn (CapturedResult $captured) => $this->forCapturedResult($captured, $context))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, CapturedResult>|array<int, CapturedResult>  $results
     * @return array<string, string>
     */
    public function summaryForMany(Collection|array $results, array $context = []): array
    {
        $rows = $this->forMany($results, $context);

        if ($rows === []) {
            return $this->emptySummary();
        }

        $keys = ['sample_code', 'test_name', 'analyst', 'method', 'unit', 'standard', 'standard_limit'];
        $summary = [];

        foreach ($keys as $key) {
            $values = collect($rows)
                ->pluck($key)
                ->filter(fn (string $value) => $value !== '—')
                ->unique()
                ->values();

            if ($values->isEmpty()) {
                $summary[$key] = '—';
            } elseif ($values->count() === 1) {
                $summary[$key] = (string) $values->first();
            } else {
                $summary[$key] = $values->implode(', ');
            }
        }

        return $summary;
    }

    /**
     * @return array<string, string>
     */
    public function emptySummary(): array
    {
        return [
            'sample_code' => '—',
            'test_name' => '—',
            'analyst' => '—',
            'method' => '—',
            'unit' => '—',
            'standard' => '—',
            'standard_limit' => '—',
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function resolveAnalystName(CapturedResult $captured, array $context): ?string
    {
        if (! empty($context['analyst_name'])) {
            return (string) $context['analyst_name'];
        }

        if ($captured->relationLoaded('user') && $captured->user?->name) {
            return (string) $captured->user->name;
        }

        if ($captured->user_id) {
            $user = User::query()->find($captured->user_id);

            return $user?->name;
        }

        if ($captured->relationLoaded('operator') && $captured->operator?->name) {
            return (string) $captured->operator->name;
        }

        if ($captured->operator_id) {
            $operator = User::query()->find($captured->operator_id);

            return $operator?->name;
        }

        $defacto = $captured->defacto_analyst();

        return $defacto?->name;
    }

    private function resolveTestName(CapturedResult $captured, ?\App\AnalysisElements $element): string
    {
        if ($captured->relationLoaded('my_analyte') && $captured->my_analyte?->name) {
            return (string) $captured->my_analyte->name;
        }

        if ($element?->relationLoaded('analyte') && $element->analyte?->name) {
            return (string) $element->analyte->name;
        }

        if ($element) {
            $name = trim((string) ($element->analyte()->first()?->name ?? ''));

            if ($name !== '') {
                return $name;
            }
        }

        if ($captured->relationLoaded('analysis_type') && $captured->analysis_type?->name) {
            return (string) $captured->analysis_type->name;
        }

        return '—';
    }

    private function resolveMethodName(CapturedResult $captured, ?\App\AnalysisElements $element): ?string
    {
        if ($captured->relationLoaded('analysisMethod') && $captured->analysisMethod?->name) {
            return (string) $captured->analysisMethod->name;
        }

        if ($captured->method_id) {
            $method = AnalysisMethod::query()->find($captured->method_id);

            if ($method?->name) {
                return (string) $method->name;
            }
        }

        if ($captured->relationLoaded('ltmethod') && $captured->ltmethod?->name) {
            return (string) $captured->ltmethod->name;
        }

        if ($captured->ltm_method_id) {
            $ltMethod = AnalysisMethod::query()->find($captured->ltm_method_id);

            if ($ltMethod?->name) {
                return (string) $ltMethod->name;
            }
        }

        if ($element) {
            $method = $element->method();

            if ($method?->name) {
                return (string) $method->name;
            }

            if ($element->relationLoaded('mmethod') && $element->mmethod?->name) {
                return (string) $element->mmethod->name;
            }

            if ($element->relationLoaded('ltmethod') && $element->ltmethod?->name) {
                return (string) $element->ltmethod->name;
            }
        }

        return null;
    }

    private function display(?string $value): string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : '—';
    }
}
