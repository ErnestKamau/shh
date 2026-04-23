<?php

namespace App\Services\System;

use App\Models\System\TranslationLanguageLine;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class TranslationManagementService
{
    /**
     * @param array<string, mixed> $text
     */
    public function upsertTranslation(string $group, string $key, array $text): TranslationLanguageLine
    {
        $payload = $this->normalizeText($text);

        if ($payload === []) {
            throw new \InvalidArgumentException('At least one language value is required.');
        }

        $line = TranslationLanguageLine::query()->updateOrCreate(
            ['group' => trim($group), 'key' => trim($key)],
            ['text' => $payload]
        );

        $this->flushGroupCache($line->group);

        return $line;
    }

    public function deleteTranslation(TranslationLanguageLine $line): void
    {
        $group = (string) $line->group;
        $line->delete();
        $this->flushGroupCache($group);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<string, mixed>
     */
    public function bulkUpsert(array $rows, array $languageCodes): array
    {
        $summary = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 1;

            try {
                $group = trim((string) Arr::get($row, 'group', ''));
                $key = trim((string) Arr::get($row, 'key', ''));

                if ($group === '' || $key === '') {
                    throw new \InvalidArgumentException('Group and key are required.');
                }

                $text = [];
                foreach ($languageCodes as $languageCode) {
                    $value = Arr::get($row, $languageCode);
                    if ($value !== null && trim((string) $value) !== '') {
                        $text[$languageCode] = trim((string) $value);
                    }
                }

                if ($text === []) {
                    throw new \InvalidArgumentException('At least one language value is required.');
                }

                DB::transaction(function () use ($group, $key, $text, &$summary): void {
                    $existing = TranslationLanguageLine::query()
                        ->where('group', $group)
                        ->where('key', $key)
                        ->exists();

                    TranslationLanguageLine::query()->updateOrCreate(
                        ['group' => $group, 'key' => $key],
                        ['text' => $this->normalizeText($text)]
                    );

                    if ($existing) {
                        $summary['updated']++;
                    } else {
                        $summary['created']++;
                    }
                });

                $this->flushGroupCache($group);
            } catch (Throwable $exception) {
                $summary['failed']++;
                $summary['errors'][] = "Row {$lineNumber}: {$exception->getMessage()}";
            }
        }

        return $summary;
    }

    public function flushGroupCache(?string $group = null): void
    {
        try {
            $loader = app('translation.loader');
            if (method_exists($loader, 'flushGroupCache')) {
                if ($group !== null) {
                    $loader->flushGroupCache($group);
                } else {
                    $loader->flushGroupCache();
                }
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @param array<string, mixed> $text
     * @return array<string, string>
     */
    private function normalizeText(array $text): array
    {
        $normalized = [];

        foreach ($text as $languageCode => $value) {
            $code = strtolower(trim((string) $languageCode));
            $content = trim((string) $value);

            if ($code === '' || $content === '') {
                continue;
            }

            $normalized[$code] = $content;
        }

        return $normalized;
    }
}
