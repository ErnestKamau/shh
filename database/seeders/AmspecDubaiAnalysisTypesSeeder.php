<?php

namespace Database\Seeders;

use App\AnalysisType;
use App\Lab;
use App\SampleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Seeds starter analysis types for Amspec Dubai sample types.
 *
 * Codes match the analysis type name (e.g. "Halal", "Microbiological").
 *
 * Mapping:
 * - Seafood / Nonseafood → Microbiological, Halal
 * - Feed → Chemical, Chemistry
 * - Hand / Surface / Sponge Swab → Microbiological
 * - Waste Water → Chemical
 *
 * Requires AmspecDubaiSampleTaxonomySeeder (or equivalent sample types) first.
 */
class AmspecDubaiAnalysisTypesSeeder extends Seeder
{
    /**
     * sample type code → list of analysis type names.
     *
     * @var array<string, list<string>>
     */
    private const MAP = [
        'Sea Food' => ['Microbiological', 'Halal'],
        'NonSea Food' => ['Microbiological', 'Halal'],
        'Feeed' => ['Chemical', 'Chemistry'],
        'Hand Swab' => ['Microbiological'],
        'Surface Swab' => ['Microbiological'],
        'Sponge' => ['Microbiological'],
        'Waste Water' => ['Chemical'],
    ];

    public function run(): void
    {
        $companyId = $this->resolveCompanyId();
        if (! $companyId) {
            $this->command?->error('No company found. Create a company row before seeding analysis types.');

            return;
        }

        $labId = $this->resolveLabId($companyId);
        if (! $labId) {
            $this->command?->error('No lab found. Create a lab before seeding analysis types (lab_id is required).');

            return;
        }

        foreach (self::MAP as $sampleTypeCode => $analysisTypeNames) {
            $sampleType = SampleType::query()
                ->where('code', $sampleTypeCode)
                ->where('company_id', $companyId)
                ->first()
                ?? SampleType::query()->where('code', $sampleTypeCode)->first();

            if (! $sampleType) {
                $this->command?->warn("Sample type {$sampleTypeCode} not found — skip. Run AmspecDubaiSampleTaxonomySeeder first.");

                continue;
            }

            $this->command?->info("Sample type: {$sampleType->code} — {$sampleType->name}");

            foreach ($analysisTypeNames as $name) {
                $legacyCode = $this->legacyAnalysisTypeCode($sampleTypeCode, $name);

                $analysisType = AnalysisType::query()
                    ->where('sample_type_id', $sampleType->id)
                    ->where('company_id', $companyId)
                    ->where(function ($query) use ($name, $legacyCode): void {
                        $query->where('name', $name)
                            ->orWhere('code', $name)
                            ->orWhere('code', $legacyCode);
                    })
                    ->first();

                if ($analysisType) {
                    $analysisType->fill([
                        'code' => $name,
                        'name' => $name,
                        'description' => $name,
                        'lab_id' => $labId,
                        'active' => true,
                        'has_no_result' => false,
                    ]);
                    $analysisType->save();
                } else {
                    $analysisType = AnalysisType::query()->create([
                        'code' => $name,
                        'name' => $name,
                        'description' => $name,
                        'sample_type_id' => $sampleType->id,
                        'company_id' => $companyId,
                        'lab_id' => $labId,
                        'active' => true,
                        'has_no_result' => false,
                    ]);
                }

                if (method_exists($analysisType, 'labs')) {
                    try {
                        $analysisType->labs()->syncWithoutDetaching([(string) $labId]);
                    } catch (\Throwable) {
                        // Pivot optional depending on schema.
                    }
                }

                $this->command?->info("  Analysis type: {$analysisType->code} — {$analysisType->name}");
            }
        }

        $this->command?->info('Amspec Dubai analysis types seeding complete.');
    }

    /**
     * Previous seeder code format (AT-SAMPLE_TYPE-NAME) used for rename/migration.
     */
    private function legacyAnalysisTypeCode(string $sampleTypeCode, string $name): string
    {
        $typeSlug = Str::upper(Str::slug($sampleTypeCode, '_'));
        $nameSlug = Str::upper(Str::slug($name, '_'));

        return 'AT-'.$typeSlug.'-'.$nameSlug;
    }

    private function resolveLabId(mixed $companyId): mixed
    {
        $query = Lab::query()->orderBy('id');

        if (Schema::hasColumn('labs', 'company_id')) {
            $scoped = (clone $query)->where('company_id', $companyId)->value('id');
            if ($scoped) {
                return $scoped;
            }
        }

        return $query->value('id') ?? DB::table('labs')->orderBy('id')->value('id');
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
