<?php

namespace Database\Seeders;

use App\City;
use App\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitiesTableSeeder extends Seeder
{
    /**
     * Idempotent upsert of cities by (country_id, name), resolved via iso_code_2.
     */
    public function run(): void
    {
        $rows = require database_path('seeders/data/cities.php');
        if (! is_array($rows) || $rows === []) {
            return;
        }

        $countriesByIso = Country::query()
            ->get(['id', 'iso_code_2'])
            ->keyBy(static fn (Country $country): string => strtoupper((string) $country->iso_code_2));

        $now = now();
        $buffer = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $iso = strtoupper(trim((string) ($row['iso'] ?? '')));
            if ($name === '' || $iso === '') {
                continue;
            }

            $country = $countriesByIso->get($iso);
            if ($country === null) {
                continue;
            }

            $existing = City::query()
                ->where('country_id', $country->id)
                ->where('name', $name)
                ->first();

            if ($existing !== null) {
                if ((int) $existing->status !== 1) {
                    $existing->status = 1;
                    $existing->save();
                }

                continue;
            }

            $buffer[] = [
                'id' => (string) Str::uuid(),
                'country_id' => $country->id,
                'name' => $name,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($buffer) >= 500) {
                City::query()->insert($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            City::query()->insert($buffer);
        }
    }
}
