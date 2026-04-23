<?php

namespace Tests\Feature\System;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TranslationApiTest extends TestCase
{
    public function test_translations_api_returns_grouped_payload(): void
    {
        if (!Schema::hasTable('language_lines')) {
            $this->markTestSkipped('language_lines table not available.');
        }

        DB::table('language_lines')->where('group', 'auth')->where('key', 'login')->delete();

        DB::table('language_lines')->insert([
            'group' => 'auth',
            'key' => 'login',
            'text' => json_encode(['en' => 'Login', 'sw' => 'Ingia']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/translations');

        $response->assertOk();
        $response->assertJsonPath('data.auth.login.en', 'Login');
        $response->assertJsonPath('meta.lang', null);
    }

    public function test_translations_api_supports_lang_filter(): void
    {
        if (!Schema::hasTable('language_lines')) {
            $this->markTestSkipped('language_lines table not available.');
        }

        DB::table('language_lines')->where('group', 'trip')->where('key', 'assigned')->delete();

        DB::table('language_lines')->insert([
            'group' => 'trip',
            'key' => 'assigned',
            'text' => json_encode(['en' => 'Trip assigned', 'sw' => 'Safari imepewa']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/translations?lang=en');

        $response->assertOk();
        $response->assertJsonPath('data.trip.assigned', 'Trip assigned');
        $response->assertJsonPath('meta.lang', 'en');
    }
}
