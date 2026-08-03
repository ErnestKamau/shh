<?php

namespace App\Services\Sampleworkflow;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;

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
            self::KEY_REPORT_NUMBER_FORMAT => '{job}-R{nn}',
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

    public function formatReportNumber(string $jobNumber, int $revision): string
    {
        $revision = max(1, $revision);
        $format = $this->get(self::KEY_REPORT_NUMBER_FORMAT);

        return $this->applyPlaceholders($format, $revision, $jobNumber);
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
    public function amendmentViewData(int $version, ?string $jobNumber = null): array
    {
        $version = max(1, $version);

        return [
            'revision_label' => $this->get(self::KEY_REVISION_LABEL),
            'reason_label' => $this->get(self::KEY_REASON_LABEL),
            'supersedes_text' => $this->get(self::KEY_SUPERSEDES_TEXT),
            'formatted_revision' => $this->formatRevision($version),
            'formatted_report_number' => $jobNumber !== null && $jobNumber !== ''
                ? $this->formatReportNumber($jobNumber, $version)
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

    private function applyPlaceholders(string $format, int $revision, ?string $jobNumber = null): string
    {
        $n = (string) $revision;
        $nn = str_pad($n, 2, '0', STR_PAD_LEFT);

        $replacements = [
            '{nn}' => $nn,
            '{n}' => $n,
        ];

        if ($jobNumber !== null) {
            $replacements['{job}'] = $jobNumber;
        }

        return str_replace(array_keys($replacements), array_values($replacements), $format);
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
