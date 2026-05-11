<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('countries')) {
            return;
        }

        $rows = $this->resolveFromLegacySeeder();
        if ($rows === []) {
            return;
        }

        $now = now();

        foreach ($rows as $row) {
            $iso2 = strtoupper(trim((string) ($row['iso_code_2'] ?? '')));
            $iso3 = strtoupper(trim((string) ($row['iso_code_3'] ?? '')));
            $name = trim((string) ($row['name'] ?? ''));

            if ($iso2 === '' || strlen($iso2) !== 2 || $iso3 === '' || strlen($iso3) !== 3 || $name === '') {
                continue;
            }

            $existing = DB::table('countries')
                ->where('iso_code_2', $iso2)
                ->first();

            if ($existing) {
                DB::table('countries')
                    ->where('id', $existing->id)
                    ->update([
                        'name' => $name,
                        'iso_code_3' => $iso3,
                        'status' => 1,
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('countries')->insert([
                'id' => (string) Str::uuid(),
                'name' => $name,
                'iso_code_2' => $iso2,
                'iso_code_3' => $iso3,
                'address_format' => (string) ($row['address_format'] ?? ''),
                'postcode_required' => (int) ($row['postcode_required'] ?? 0),
                'status' => (int) ($row['status'] ?? 1),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Non-destructive by design.
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveFromLegacySeeder(): array
    {
        $path = database_path('seeds/CountriesTableSeeder.php');

        if (!is_file($path)) {
            return [];
        }

        $content = file_get_contents($path);
        if (!is_string($content) || $content === '') {
            return [];
        }

        if (!preg_match('/->insert\(array\s*\((.*)\)\s*\);/sU', $content, $matches)) {
            return [];
        }

        $arrayBody = $matches[1] ?? '';
        if (!is_string($arrayBody) || trim($arrayBody) === '') {
            return [];
        }

        try {
            $rows = eval('return array (' . $arrayBody . ');');
        } catch (\Throwable $exception) {
            return [];
        }

        return is_array($rows) ? $rows : [];
    }
};
