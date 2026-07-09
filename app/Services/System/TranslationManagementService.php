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

        $line = null;

        foreach ($this->resolveTranslationGroups($group) as $resolvedGroup) {
            $line = $this->upsertLine($resolvedGroup, $key, $payload);
            $this->flushGroupCache($resolvedGroup);
        }

        return $line ?? throw new \InvalidArgumentException('A valid translation group is required.');
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
            $lineNumber = (int)$index + 1;

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

                $payload = $this->normalizeText($text);
                $resolvedGroups = $this->resolveTranslationGroups($group);
                $wasExisting = TranslationLanguageLine::query()
                    ->where('group', $resolvedGroups[0])
                    ->where('key', $key)
                    ->exists();

                DB::transaction(function () use ($resolvedGroups, $key, $payload): void {
                    foreach ($resolvedGroups as $resolvedGroup) {
                        $this->upsertLine($resolvedGroup, $key, $payload);
                    }
                });

                foreach ($resolvedGroups as $resolvedGroup) {
                    $this->flushGroupCache($resolvedGroup);
                }

                if ($wasExisting) {
                    $summary['updated']++;
                } else {
                    $summary['created']++;
                }
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
            if ($group !== null) {
                TranslationLanguageLine::flushGroupCacheForAllLocales($group);

                return;
            }

            TranslationLanguageLine::query()
                ->select('group')
                ->distinct()
                ->orderBy('group')
                ->pluck('group')
                ->each(fn (string $resolvedGroup): mixed => TranslationLanguageLine::flushGroupCacheForAllLocales($resolvedGroup));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @return array<int, string>
     */
    public function resolveTranslationGroups(string $group): array
    {
        $group = trim($group);

        if ($group === '') {
            return [];
        }

        $groups = [$group];
        $counterpart = str_starts_with($group, 'mas/')
            ? substr($group, 4)
            : 'mas/' . $group;

        if ($counterpart !== '' && $this->counterpartGroupExists($group, $counterpart)) {
            $groups[] = $counterpart;
        }

        return array_values(array_unique($groups));
    }

    private function counterpartGroupExists(string $group, string $counterpart): bool
    {
        return TranslationLanguageLine::query()
            ->where('group', $counterpart)
            ->exists();
    }

    /**
     * @param array<string, string> $text
     */
    private function upsertLine(string $group, string $key, array $text): TranslationLanguageLine
    {
        $group = trim($group);
        $key = trim($key);

        $existing = TranslationLanguageLine::query()
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        $mergedText = array_merge(
            is_array($existing?->text) ? $existing->text : [],
            $text
        );

        return TranslationLanguageLine::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['text' => $mergedText]
        );
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
