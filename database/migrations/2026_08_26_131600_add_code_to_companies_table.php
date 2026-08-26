<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('companies', 'code')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('code', 32)->nullable()->unique()->after('name');
            });
        }

        $knownCodes = [
            '019dde3f-07d3-73d0-a0f2-a01ac58346b4' => 'uae',
            '019dde3f-07d3-73d0-a0f2-a01ac58346b5' => 'brl',
        ];

        foreach ($knownCodes as $companyId => $code) {
            DB::table('companies')
                ->where('id', $companyId)
                ->where(function ($query): void {
                    $query->whereNull('code')->orWhere('code', '');
                })
                ->update(['code' => $code]);
        }

        $this->upsertSystemTranslations();
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'code')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            });
        }
    }

    private function upsertSystemTranslations(): void
    {
        if (! Schema::hasTable('language_lines')) {
            return;
        }

        $translations = [
            'company_code' => [
                'en' => 'Company Code',
                'sw' => 'Msimbo wa Kampuni',
            ],
            'company_code_hint' => [
                'en' => 'Used in the codebase to enable features for this company only. Examples: brl, uae.',
                'sw' => 'Hutumika katika msimbo kuwasha vipengele vya kampuni hii pekee. Mifano: brl, uae.',
            ],
            'company_code_placeholder' => [
                'en' => 'e.g. brl',
                'sw' => 'mf. brl',
            ],
            'search_by_country_or_company_id' => [
                'en' => 'Search by country, company code, or company id...',
                'sw' => 'Tafuta kwa nchi, msimbo wa kampuni, au kitambulisho cha kampuni...',
            ],
        ];

        foreach ($translations as $key => $text) {
            $existing = DB::table('language_lines')
                ->where('group', 'system')
                ->where('key', $key)
                ->first();

            $existingText = [];
            if ($existing && is_string($existing->text ?? null) && $existing->text !== '') {
                $decoded = json_decode($existing->text, true);
                if (is_array($decoded)) {
                    $existingText = $decoded;
                }
            } elseif ($existing && is_array($existing->text ?? null)) {
                $existingText = $existing->text;
            }

            $payload = [
                'text' => json_encode(array_merge($existingText, $text)),
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('language_lines')->where('id', $existing->id)->update($payload);
                continue;
            }

            DB::table('language_lines')->insert([
                'id' => (string) Str::uuid(),
                'group' => 'system',
                'key' => $key,
                'text' => json_encode($text),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        try {
            \App\Models\System\TranslationLanguageLine::flushGroupCacheForAllLocales('system');
        } catch (\Throwable) {
        }
    }
};
