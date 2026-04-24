<?php

namespace Tests\Feature\System;

use App\Services\System\TranslationManagementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TranslationManagementServiceTest extends TestCase
{
    public function test_upsert_translation_persists_values(): void
    {
        if (!Schema::hasTable('language_lines') || !Schema::hasTable('languages')) {
            $this->markTestSkipped('Required tables are not available.');
        }

        DB::table('language_lines')->where('group', 'trip')->where('key', 'assigned')->delete();

        $service = new TranslationManagementService();
        $service->upsertTranslation('trip', 'assigned', [
            'en' => 'Trip assigned',
            'sw' => 'Safari imepewa',
        ]);

        $line = DB::table('language_lines')->where('group', 'trip')->where('key', 'assigned')->first();

        $this->assertNotNull($line);
        $text = json_decode((string) $line->text, true);
        $this->assertSame('Trip assigned', $text['en'] ?? null);
    }

    public function test_bulk_upsert_returns_created_and_updated_counters(): void
    {
        if (!Schema::hasTable('language_lines') || !Schema::hasTable('languages')) {
            $this->markTestSkipped('Required tables are not available.');
        }

        DB::table('language_lines')->where('group', 'trip')->whereIn('key', ['assigned', 'completed'])->delete();

        DB::table('language_lines')->insert([
            'group' => 'trip',
            'key' => 'assigned',
            'text' => json_encode(['en' => 'Old']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new TranslationManagementService();

        $summary = $service->bulkUpsert([
            ['group' => 'trip', 'key' => 'assigned', 'en' => 'Trip assigned'],
            ['group' => 'trip', 'key' => 'completed', 'en' => 'Trip completed'],
        ], ['en', 'sw']);

        $this->assertSame(1, $summary['updated']);
        $this->assertSame(1, $summary['created']);
        $this->assertSame(0, $summary['failed']);
    }
}
