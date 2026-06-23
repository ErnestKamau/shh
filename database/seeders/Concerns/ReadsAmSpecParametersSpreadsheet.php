<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Collection;

trait ReadsAmSpecParametersSpreadsheet
{
    public const AMSPEC_PARAMETERS_PATH = 'database/seeders/data/amspec_parameter_rows.php';

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function readAmSpecParameterRows(?string $path = null): Collection
    {
        $absolutePath = base_path($path ?? self::AMSPEC_PARAMETERS_PATH);

        if (! is_file($absolutePath)) {
            throw new \RuntimeException("AmSpec parameter seed data not found: {$absolutePath}");
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = require $absolutePath;

        return collect($rows);
    }

    protected function generateAmSpecCode(?string $name): string
    {
        if (empty($name)) {
            return 'CODE-'.uniqid();
        }

        $slug = preg_replace('/[^A-Za-z0-9]/', '_', $name);
        $slug = preg_replace('/_+/', '_', (string) $slug);
        $slug = trim((string) $slug, '_');

        return strtoupper(substr($slug, 0, 100));
    }

    /**
     * @return Collection<int, array{code: string, name: string}>
     */
    protected function uniqueAmSpecSampleTypes(?string $path = null): Collection
    {
        return $this->readAmSpecParameterRows($path)
            ->unique('sample_type_code')
            ->map(fn (array $row) => [
                'code' => $row['sample_type_code'],
                'name' => $row['sample_type_name'],
            ])
            ->values();
    }
}
