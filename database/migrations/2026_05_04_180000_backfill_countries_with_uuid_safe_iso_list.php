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

        $now = now();
        $countries = $this->resolveCountryRows();

        foreach ($countries as $country) {
            $iso2 = $country['iso_code_2'];

            $existing = DB::table('countries')
                ->where('iso_code_2', $iso2)
                ->first();

            if ($existing) {
                DB::table('countries')
                    ->where('id', $existing->id)
                    ->update([
                        'name' => $country['name'],
                        'iso_code_3' => $country['iso_code_3'],
                        'status' => 1,
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('countries')->insert([
                'id' => (string) Str::uuid(),
                'name' => $country['name'],
                'iso_code_2' => $country['iso_code_2'],
                'iso_code_3' => $country['iso_code_3'],
                'address_format' => '',
                'postcode_required' => 0,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: do not remove countries to avoid breaking FKs.
    }

    /**
     * @return array<int, array{name:string, iso_code_2:string, iso_code_3:string}>
     */
    private function resolveCountryRows(): array
    {
        if (class_exists(\ResourceBundle::class) && class_exists(\Locale::class)) {
            $bundle = \ResourceBundle::create('en', 'ICUDATA-region');

            if ($bundle instanceof \ResourceBundle) {
                $rows = [];

                foreach ($bundle as $iso2 => $name) {
                    if (!is_string($iso2) || strlen($iso2) !== 2) {
                        continue;
                    }

                    $iso2 = strtoupper($iso2);
                    $iso3 = strtoupper((string) \Locale::getISO3Country($iso2));

                    if ($iso3 === '' || strlen($iso3) !== 3) {
                        continue;
                    }

                    $rows[] = [
                        'name' => trim((string) $name),
                        'iso_code_2' => $iso2,
                        'iso_code_3' => $iso3,
                    ];
                }

                usort($rows, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

                return $rows;
            }
        }

        // Fallback list if ICU data is unavailable.
        return [
            ['name' => 'Kenya', 'iso_code_2' => 'KE', 'iso_code_3' => 'KEN'],
            ['name' => 'Uganda', 'iso_code_2' => 'UG', 'iso_code_3' => 'UGA'],
            ['name' => 'Tanzania', 'iso_code_2' => 'TZ', 'iso_code_3' => 'TZA'],
            ['name' => 'Rwanda', 'iso_code_2' => 'RW', 'iso_code_3' => 'RWA'],
            ['name' => 'Burundi', 'iso_code_2' => 'BI', 'iso_code_3' => 'BDI'],
            ['name' => 'Ethiopia', 'iso_code_2' => 'ET', 'iso_code_3' => 'ETH'],
            ['name' => 'South Sudan', 'iso_code_2' => 'SS', 'iso_code_3' => 'SSD'],
        ];
    }
};
