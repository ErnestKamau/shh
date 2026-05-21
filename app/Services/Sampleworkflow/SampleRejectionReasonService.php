<?php

namespace App\Services\Sampleworkflow;

use App\Models\System\SystemConfiguration;
use Illuminate\Support\Str;

class SampleRejectionReasonService
{
    /**
     * @return list<array{key: string, label: string}>
     */
    public function getReasons(): array
    {
        $config = SystemConfiguration::query()
            ->where('key', 'sample_rejection_reasons')
            ->where('status', true)
            ->first();

        if ($config === null || trim((string) $config->value) === '') {
            return [];
        }

        $decoded = json_decode((string) $config->value, true);
        if (! is_array($decoded)) {
            return [];
        }

        $reasons = [];

        foreach ($decoded as $index => $item) {
            if (is_string($item)) {
                $label = trim($item);
                if ($label === '') {
                    continue;
                }
                $reasons[] = [
                    'key' => Str::slug($label, '_') ?: 'reason_' . $index,
                    'label' => $label,
                ];

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? $item['name'] ?? $item['value'] ?? ''));
            if ($label === '') {
                continue;
            }

            $key = trim((string) ($item['key'] ?? ''));
            if ($key === '') {
                $key = Str::slug($label, '_') ?: 'reason_' . $index;
            }

            $reasons[] = [
                'key' => $key,
                'label' => $label,
            ];
        }

        return $reasons;
    }
}
