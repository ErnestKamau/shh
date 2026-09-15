<?php

namespace App\Services\Sampleworkflow;

/**
 * Food/Water TRF: thermometer_id stores one or more free-text Equipment ID values
 * (JSON array of strings). Values are printed on PDFs as entered.
 */
final class TrfSamplingEquipmentResolver
{
    /**
     * @return list<string>
     */
    public function decodeIds(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            $ids = [];
            foreach ($value as $item) {
                if (is_array($item)) {
                    $id = trim((string) ($item['equipment_id'] ?? $item['id'] ?? ''));
                } else {
                    $id = trim((string) $item);
                }

                if ($id !== '') {
                    $ids[] = $id;
                }
            }

            return array_values(array_unique($ids));
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return [];
        }

        if (str_starts_with($raw, '[')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $this->decodeIds($decoded);
            }
        }

        if (str_contains($raw, ',')) {
            return array_values(array_filter(array_map(
                static fn (string $token): string => trim($token),
                explode(',', $raw)
            ), static fn (string $token): bool => $token !== ''));
        }

        return [$raw];
    }

    /**
     * @param  list<string|null>  $ids
     */
    public function encodeIds(array $ids): ?string
    {
        $clean = array_values(array_unique(array_filter(array_map(
            static fn ($id): string => trim((string) $id),
            $ids
        ), static fn (string $id): bool => $id !== '')));

        return $clean === [] ? null : json_encode($clean);
    }

    /**
     * UI rows: at least one slot (may be empty string).
     *
     * @return list<string>
     */
    public function rowsForForm(mixed $value): array
    {
        $ids = $this->decodeIds($value);

        return $ids === [] ? [''] : $ids;
    }

    public function formatForDisplay(mixed $value): string
    {
        return implode(', ', $this->decodeIds($value));
    }

    /**
     * PDF line: print free-text Equipment IDs as entered.
     */
    public function formatForPdf(mixed $value): string
    {
        return $this->formatForDisplay($value);
    }
}
