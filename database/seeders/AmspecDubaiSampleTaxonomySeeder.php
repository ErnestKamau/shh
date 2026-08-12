<?php

namespace Database\Seeders;

use App\SampleType;
use App\SampleTypeCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmspecDubaiSampleTaxonomySeeder extends Seeder
{
    /**
     * Seed Amspec Dubai sample type categories and sample types.
     * Codes match the live Sample Types catalog (name may differ slightly from code).
     * Does not create analysis types or analysis elements.
     */
    public function run(): void
    {
        $companyId = $this->resolveCompanyId();

        if (! $companyId) {
            $this->command?->error('No company found. Create a company row before seeding taxonomy.');

            return;
        }

        $taxonomy = [
            'Food' => [
                ['code' => 'Sea Food', 'name' => 'Seafood'],
                ['code' => 'NonSea Food', 'name' => 'Nonseafood'],
                ['code' => 'Feeed', 'name' => 'Feed'],
            ],
            'Water' => [
                ['code' => 'Waste Water', 'name' => 'Waste Water'],
                ['code' => 'Drinking Water', 'name' => 'Drinking Water'],
            ],
            'Swab' => [
                ['code' => 'Hand Swab', 'name' => 'Hand Swab'],
                ['code' => 'Surface Swab', 'name' => 'Surface Swab'],
                ['code' => 'Sponge', 'name' => 'Sponge'],
            ],
            'Ice/Water' => [
                ['code' => 'Ice', 'name' => 'Ice'],
                ['code' => 'Ice Water', 'name' => 'Water'],
            ],
            'Air' => [],
            'Food Contact Material' => [],
            'Consumer Products' => [],
        ];

        foreach ($taxonomy as $categoryName => $sampleTypes) {
            $category = SampleTypeCategory::query()->updateOrCreate(
                ['sample_type_category' => $categoryName],
                ['active' => true]
            );

            $this->command?->info("Category: {$categoryName}");

            foreach ($sampleTypes as $sampleType) {
                $attributes = [
                    'name' => $sampleType['name'],
                    'description' => $sampleType['name'],
                    'sample_type_category' => $category->id,
                    'active' => true,
                    'is_results_attachable' => true,
                    'company_id' => $companyId,
                ];

                $created = SampleType::query()->updateOrCreate(
                    ['code' => $sampleType['code']],
                    $attributes
                );

                $this->command?->info("  Sample type: {$created->code} — {$created->name}");
            }
        }

        $this->command?->info('Amspec Dubai sample taxonomy seeding complete.');
    }

    private function resolveCompanyId(): mixed
    {
        try {
            $sessionCompanyId = session('company_id');
            if ($sessionCompanyId) {
                return $sessionCompanyId;
            }
        } catch (\Throwable) {
            // No session in artisan context.
        }

        $authUser = auth()->user();
        if ($authUser?->company_id) {
            return $authUser->company_id;
        }

        return DB::table('companies')->orderBy('id')->value('id');
    }
}
