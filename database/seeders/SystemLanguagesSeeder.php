<?php

namespace Database\Seeders;

use App\Models\System\Language;
use Illuminate\Database\Seeder;

class SystemLanguagesSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['name' => 'English', 'code' => 'en', 'is_active' => true, 'is_default' => true],
            ['name' => 'Swahili', 'code' => 'sw', 'is_active' => true, 'is_default' => false],
        ];

        foreach ($languages as $data) {
            Language::query()->updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }

        Language::query()
            ->where('code', '!=', 'en')
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}
