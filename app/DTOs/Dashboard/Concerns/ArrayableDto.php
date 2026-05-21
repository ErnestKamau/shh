<?php

namespace App\DTOs\Dashboard\Concerns;

use Illuminate\Support\Str;

trait ArrayableDto
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->normalizeArray($this->buildArray());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildArray(): array
    {
        $data = [];

        foreach (get_object_vars($this) as $key => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $data[$key] = $value->toArray();

                continue;
            }

            if (is_array($value)) {
                $data[$key] = array_map(
                    fn ($item) => is_object($item) && method_exists($item, 'toArray') ? $item->toArray() : $item,
                    $value
                );

                continue;
            }

            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeArray(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalizedKey = Str::snake($key);

            if (is_array($value)) {
                $isList = array_is_list($value);
                $normalized[$normalizedKey] = $isList
                    ? array_map(fn ($item) => is_array($item) ? $this->normalizeArray($item) : $item, $value)
                    : $this->normalizeArray($value);

                continue;
            }

            $normalized[$normalizedKey] = $value;
        }

        return $normalized;
    }
}
