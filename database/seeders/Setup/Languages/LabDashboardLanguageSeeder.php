<?php

namespace Database\Seeders\Setup\Languages;

use App\Models\System\TranslationLanguageLine;
use Illuminate\Database\Seeder;

class LabDashboardLanguageSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'welcome_back' => ['en' => 'Welcome back', 'sw' => 'Karibu tena'],
            'overview_of_lab_operations' => [
                'en' => 'Overview of lab operations',
                'sw' => 'Muhtasari wa shughuli za maabara',
            ],
            'samples_reception' => ['en' => 'Samples Reception', 'sw' => 'Mapokezi ya Sampuli'],
            'drafts_total_pending_forms' => [
                'en' => 'Drafts / total pending forms',
                'sw' => 'Rasimu / jumla ya fomu zinazosubiri',
            ],
            'sample_verification' => ['en' => 'Sample Verification', 'sw' => 'Uthibitishaji wa Sampuli'],
            'awaiting_verification' => [
                'en' => 'Awaiting verification',
                'sw' => 'Inasubiri uthibitishaji',
            ],
        ];

        $count = 0;

        foreach ($translations as $key => $langValues) {
            TranslationLanguageLine::query()->updateOrCreate(
                [
                    'group' => 'dashboard',
                    'key' => $key,
                ],
                [
                    'text' => $langValues,
                ]
            );
            $count++;
        }

        $this->command?->info("Lab dashboard translations seeded: {$count} keys (group: dashboard)");
    }
}
