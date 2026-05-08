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

        $rows = $this->extractLegacyRows();
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

            $existing = DB::table('countries')->where('iso_code_2', $iso2)->first();

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
    private function extractLegacyRows(): array
    {
        $path = database_path('seeds/CountriesTableSeeder.php');
        if (!is_file($path)) {
            return [];
        }

        $content = file_get_contents($path);
        if (!is_string($content) || $content === '') {
            return [];
        }

        $needle = "->insert(";
        $insertPos = strpos($content, $needle);
        if ($insertPos === false) {
            return [];
        }

        $exprStart = $insertPos + strlen($needle);
        $expression = $this->extractBalancedExpression($content, $exprStart);
        if ($expression === null) {
            return [];
        }

        try {
            $rows = eval('return ' . $expression . ';');
        } catch (\Throwable $exception) {
            return [];
        }

        return is_array($rows) ? $rows : [];
    }

    private function extractBalancedExpression(string $content, int $start): ?string
    {
        $length = strlen($content);
        $level = 0;
        $started = false;
        $inSingle = false;
        $inDouble = false;
        $escaped = false;

        for ($i = $start; $i < $length; $i++) {
            $char = $content[$i];

            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if (!$inDouble && $char === "'") {
                $inSingle = !$inSingle;
                continue;
            }

            if (!$inSingle && $char === '"') {
                $inDouble = !$inDouble;
                continue;
            }

            if ($inSingle || $inDouble) {
                continue;
            }

            if ($char === '(') {
                $level++;
                $started = true;
            } elseif ($char === ')') {
                $level--;
                if ($started && $level === 0) {
                    return trim(substr($content, $start, ($i - $start) + 1));
                }
            }
        }

        return null;
    }
};
