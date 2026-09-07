<?php

namespace App\Services\Sampleworkflow;

use App\Models\Equipments\Equipment;
use Illuminate\Support\Collection;

/**
 * Food/Water TRF: thermometer_id stores one or more equipment UUIDs (JSON array).
 * Legacy free-text values remain displayable on PDFs.
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
        $ids = $this->decodeIds($value);
        if ($ids === []) {
            return '';
        }

        $equipment = Equipment::query()
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'equipment_number'])
            ->keyBy(fn (Equipment $row): string => (string) $row->id);

        $labels = [];
        foreach ($ids as $id) {
            $match = $equipment->get($id);
            if ($match === null) {
                $labels[] = $id;

                continue;
            }

            $number = trim((string) ($match->equipment_number ?? ''));
            $name = trim((string) ($match->name ?? ''));
            if ($number !== '' && $name !== '') {
                $labels[] = $name.' ('.$number.')';
            } elseif ($number !== '') {
                $labels[] = $number;
            } else {
                $labels[] = $name !== '' ? $name : $id;
            }
        }

        return implode(', ', $labels);
    }

    /**
     * Compact PDF line: prefer equipment_number only.
     */
    public function formatForPdf(mixed $value): string
    {
        $ids = $this->decodeIds($value);
        if ($ids === []) {
            return '';
        }

        $equipment = Equipment::query()
            ->whereIn('id', $ids)
            ->get(['id', 'equipment_number'])
            ->keyBy(fn (Equipment $row): string => (string) $row->id);

        $labels = [];
        foreach ($ids as $id) {
            $match = $equipment->get($id);
            if ($match === null) {
                // Legacy free-text equipment id (already a number / code).
                $labels[] = $id;

                continue;
            }

            $number = trim((string) ($match->equipment_number ?? ''));
            if ($number !== '') {
                $labels[] = $number;
            }
        }

        return implode(', ', $labels);
    }

    /**
     * @return Collection<int, array{id: string, name: string}>
     */
    public function activeEquipmentSelectOptions(): Collection
    {
        return Equipment::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'equipment_number'])
            ->map(static function (Equipment $equipment): array {
                $number = trim((string) ($equipment->equipment_number ?? ''));
                $name = trim((string) ($equipment->name ?? ''));
                $label = $name;
                if ($number !== '') {
                    $label = $name !== '' ? $name.' ('.$number.')' : $number;
                }

                return [
                    'id' => (string) $equipment->id,
                    'name' => $label !== '' ? $label : (string) $equipment->id,
                ];
            })
            ->values();
    }
}
