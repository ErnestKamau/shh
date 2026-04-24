<?php

namespace Tests\Feature\System;

use App\Models\System\Language;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LanguageDefaultGuardTest extends TestCase
{
    public function test_default_language_cannot_be_deleted_by_guard(): void
    {
        if (!Schema::hasTable('languages')) {
            $this->markTestSkipped('languages table not available.');
        }

        Language::query()->delete();

        $english = Language::query()->create([
            'name' => 'English',
            'code' => 'en',
            'is_active' => true,
            'is_default' => true,
        ]);

        $swahili = Language::query()->create([
            'name' => 'Swahili',
            'code' => 'sw',
            'is_active' => true,
            'is_default' => false,
        ]);

        $this->assertFalse($english->canBeDeleted());
        $this->assertTrue($swahili->canBeDeleted());
    }

    public function test_switching_default_keeps_single_default_language(): void
    {
        if (!Schema::hasTable('languages')) {
            $this->markTestSkipped('languages table not available.');
        }

        Language::query()->delete();

        Language::query()->create([
            'name' => 'English',
            'code' => 'en',
            'is_active' => true,
            'is_default' => true,
        ]);

        $swahili = Language::query()->create([
            'name' => 'Swahili',
            'code' => 'sw',
            'is_active' => true,
            'is_default' => false,
        ]);

        $swahili->is_default = true;
        $swahili->save();

        $this->assertSame(1, Language::query()->where('is_default', true)->count());
        $this->assertSame('sw', Language::query()->where('is_default', true)->value('code'));
    }
}
