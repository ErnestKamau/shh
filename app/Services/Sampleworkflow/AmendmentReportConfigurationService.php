<?php

namespace App\Services\Sampleworkflow;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\SampleAnalysisStage;
use App\SampleHeader;

class AmendmentReportConfigurationService
{
    public const TYPE_NAME = 'Amendment Report';

    public const KEY_REVISION_FORMAT = 'amendment_revision_format';

    public const KEY_REVISION_LABEL = 'amendment_revision_label';

    public const KEY_REASON_LABEL = 'amendment_reason_label';

    public const KEY_SUPERSEDES_TEXT = 'amendment_supersedes_text';

    public const KEY_REPORT_NUMBER_FORMAT = 'amendment_report_number_format';

    public const KEY_SAMPLE_NUMBER_SUFFIX_FORMAT = 'amendment_sample_number_suffix_format';

    /**
     * Keys that may intentionally be blank (empty string is not replaced by the default).
     *
     * @return list<string>
     */
    public static function optionalKeys(): array
    {
        return [
            self::KEY_SAMPLE_NUMBER_SUFFIX_FORMAT,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            self::KEY_REVISION_FORMAT => 'R{nn}',
            self::KEY_REVISION_LABEL => 'Revision No.',
            self::KEY_REASON_LABEL => 'Amendment Reason',
            self::KEY_SUPERSEDES_TEXT => 'This report supersedes the original report',
            self::KEY_REPORT_NUMBER_FORMAT => '{job}_({samples})_({sections})-R{nn}',
            self::KEY_SAMPLE_NUMBER_SUFFIX_FORMAT => '-V{n}',
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::defaults());
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        $defaults = self::defaults();
        $optional = self::optionalKeys();
        $stored = SystemConfiguration::query()
            ->whereIn('key', array_keys($defaults))
            ->get()
            ->keyBy('key');

        $resolved = [];
        foreach ($defaults as $key => $default) {
            $row = $stored->get($key);
            if ($row === null) {
                $resolved[$key] = $default;

                continue;
            }

            $value = trim((string) $row->value);
            if ($value === '' && ! in_array($key, $optional, true)) {
                $resolved[$key] = $default;
            } else {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }

    public function get(string $key): string
    {
        $all = $this->all();

        return $all[$key] ?? (self::defaults()[$key] ?? '');
    }

    public function formatRevision(int $version): string
    {
        return $this->applyPlaceholders($this->get(self::KEY_REVISION_FORMAT), max(1, $version));
    }

    /**
     * @param  list<int>  $sampleNumbers  Numeric sample sequence numbers (e.g. 1, 5, 45) covered by the report.
     * @param  list<string>  $labSectionNames  Lab section names covered by the report (e.g. ["Microbiology", "Chemistry"]).
     */
    public function formatReportNumber(string $jobNumber, int $revision, array $sampleNumbers = [], array $labSectionNames = []): string
    {
        $revision = max(1, $revision);
        $format = $this->get(self::KEY_REPORT_NUMBER_FORMAT);

        return $this->applyPlaceholders($format, $revision, $jobNumber, $sampleNumbers, $labSectionNames);
    }

    /**
     * Same as formatReportNumber(), but resolves {samples} / {sections} directly from the batch.
     *
     * @param  list<string>  $filterSampleIds  SampleDetails ids to restrict {samples} to (empty = every sample on the batch).
     * @param  list<string>  $filterLabSectionIds  Lab section ids to restrict {sections} to (empty = every section on the batch).
     */
    public function formatReportNumberForBatch(
        SampleHeader $batch,
        int $revision,
        array $filterSampleIds = [],
        array $filterLabSectionIds = [],
    ): string {
        return $this->formatReportNumber(
            (string) $batch->batch_code,
            $revision,
            $this->sampleSequenceNumbersForBatch($batch, $filterSampleIds),
            $this->labSectionNamesForBatch($batch, $filterLabSectionIds),
        );
    }

    /**
     * @param  list<string>  $filterSampleIds
     * @return list<int>
     */
    public function sampleSequenceNumbersForBatch(SampleHeader $batch, array $filterSampleIds = []): array
    {
        $query = $batch->samples();
        if ($filterSampleIds !== []) {
            $query = $query->whereIn('id', $filterSampleIds);
        }

        $numbers = [];
        foreach ($query->pluck('sample_code') as $sampleCode) {
            $sampleCode = $this->stripSampleNumberSuffix(trim((string) $sampleCode));
            if (preg_match('/(\d+)$/', $sampleCode, $matches) === 1) {
                $numbers[] = (int) $matches[1];
            }
        }

        return $numbers;
    }

    /**
     * @param  list<string>  $filterLabSectionIds
     * @return list<string>
     */
    public function labSectionNamesForBatch(SampleHeader $batch, array $filterLabSectionIds = []): array
    {
        if ($filterLabSectionIds !== []) {
            return SampleAnalysisStage::query()
                ->whereIn('id', $filterLabSectionIds)
                ->pluck('name')
                ->map(fn ($name): string => (string) $name)
                ->all();
        }

        $names = $batch->getLabSectionsNames();

        return $names === '' ? [] : array_map('trim', explode(',', $names));
    }

    public function formatSampleNumberSuffix(int $version): string
    {
        $format = trim($this->get(self::KEY_SAMPLE_NUMBER_SUFFIX_FORMAT));
        if ($format === '') {
            return '';
        }

        return $this->applyPlaceholders($format, max(1, $version));
    }

    /**
     * Remove a previously applied amendment suffix so a new one can be attached cleanly.
     */
    public function stripSampleNumberSuffix(string $sampleCode): string
    {
        $patterns = [];

        $currentFormat = trim($this->get(self::KEY_SAMPLE_NUMBER_SUFFIX_FORMAT));
        if ($currentFormat !== '') {
            $patterns[] = $this->suffixFormatToRegex($currentFormat);
        }

        $defaultFormat = self::defaults()[self::KEY_SAMPLE_NUMBER_SUFFIX_FORMAT];
        $patterns[] = $this->suffixFormatToRegex($defaultFormat);
        $patterns[] = '-V\\d{1,2}';

        foreach (array_unique($patterns) as $pattern) {
            $stripped = preg_replace('/'.$pattern.'$/i', '', $sampleCode);
            if (is_string($stripped) && $stripped !== '' && $stripped !== $sampleCode) {
                return $stripped;
            }
        }

        return $sampleCode;
    }

    /**
     * Labels / texts for amendment blocks on PDFs.
     *
     * @return array{
     *     revision_label: string,
     *     reason_label: string,
     *     supersedes_text: string,
     *     formatted_revision: string,
     *     formatted_report_number: string,
     *     sample_number_suffix: string
     * }
     */
    /**
     * @param  list<int>  $sampleNumbers
     * @param  list<string>  $labSectionNames
     */
    public function amendmentViewData(int $version, ?string $jobNumber = null, array $sampleNumbers = [], array $labSectionNames = []): array
    {
        $version = max(1, $version);

        return [
            'revision_label' => $this->get(self::KEY_REVISION_LABEL),
            'reason_label' => $this->get(self::KEY_REASON_LABEL),
            'supersedes_text' => $this->get(self::KEY_SUPERSEDES_TEXT),
            'formatted_revision' => $this->formatRevision($version),
            'formatted_report_number' => $jobNumber !== null && $jobNumber !== ''
                ? $this->formatReportNumber($jobNumber, $version, $sampleNumbers, $labSectionNames)
                : '',
            'sample_number_suffix' => $this->formatSampleNumberSuffix($version),
        ];
    }

    /**
     * @param  array<string, string>  $values
     */
    public function save(array $values): void
    {
        $type = SystemConfigurationsType::query()->updateOrCreate(
            ['configuration_type' => self::TYPE_NAME],
            [
                'description' => 'Wording and numbering formats for amended / revised lab reports.',
                'status' => true,
            ]
        );

        $defaults = self::defaults();
        $optional = self::optionalKeys();

        foreach ($defaults as $key => $default) {
            $value = array_key_exists($key, $values)
                ? trim((string) $values[$key])
                : $default;

            if ($value === '' && ! in_array($key, $optional, true)) {
                $value = $default;
            }

            SystemConfiguration::query()->updateOrCreate(
                ['key' => $key],
                [
                    'configuration_type_id' => $type->id,
                    'value' => $value,
                    'status' => true,
                ]
            );
        }
    }

    /**
     * @param  list<int>  $sampleNumbers
     * @param  list<string>  $labSectionNames
     */
    private function applyPlaceholders(
        string $format,
        int $revision,
        ?string $jobNumber = null,
        array $sampleNumbers = [],
        array $labSectionNames = [],
    ): string {
        $n = (string) $revision;
        $nn = str_pad($n, 2, '0', STR_PAD_LEFT);

        $replacements = [
            '{nn}' => $nn,
            '{n}' => $n,
        ];

        if ($jobNumber !== null) {
            $replacements['{job}'] = $jobNumber;
        }

        if (str_contains($format, '{samples}')) {
            $replacements['{samples}'] = $this->formatSampleRange($sampleNumbers);
        }

        if (str_contains($format, '{sections}')) {
            $replacements['{sections}'] = $this->formatSectionInitials($labSectionNames);
        }

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }

    /**
     * Sample numbers as "001" (single), "001 - 045" (contiguous range), or "001, 005, 045" (gaps).
     *
     * @param  list<int>  $sampleNumbers
     */
    private function formatSampleRange(array $sampleNumbers): string
    {
        $numbers = array_values(array_unique(array_filter($sampleNumbers, static fn (int $n): bool => $n > 0)));
        sort($numbers);

        if ($numbers === []) {
            return '';
        }

        $pad = static fn (int $n): string => str_pad((string) $n, 3, '0', STR_PAD_LEFT);

        if (count($numbers) === 1) {
            return $pad($numbers[0]);
        }

        $min = $numbers[0];
        $max = $numbers[count($numbers) - 1];
        $isContiguous = ($max - $min + 1) === count($numbers);

        return $isContiguous
            ? $pad($min).' - '.$pad($max)
            : implode(', ', array_map($pad, $numbers));
    }

    /**
     * Lab section names reduced to unique first-letter initials, e.g. ["Microbiology", "Chemistry"] -> "M, C".
     *
     * @param  list<string>  $labSectionNames
     */
    private function formatSectionInitials(array $labSectionNames): string
    {
        $initials = [];
        foreach ($labSectionNames as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $initial = mb_strtoupper(mb_substr($name, 0, 1));
            if (! in_array($initial, $initials, true)) {
                $initials[] = $initial;
            }
        }

        return implode(', ', $initials);
    }

    private function suffixFormatToRegex(string $format): string
    {
        $quoted = preg_quote($format, '/');

        return str_replace(
            ['\\{nn\\}', '\\{n\\}'],
            ['\\d{2}', '\\d+'],
            $quoted
        );
    }
}
