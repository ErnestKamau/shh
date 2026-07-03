<?php

namespace Database\Seeders;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\CapturedResult;
use App\Company;
use App\Models\QcModule\Configurations\Approvers;
use App\Result;
use App\SampleDetails;
use App\SampleHeader;
use App\StandardAnalytes;
use App\User;
use Database\Seeders\Concerns\ClearsAmSpecQcWorkflowData;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class QcWorkflowSeeder extends Seeder
{
    use ClearsAmSpecQcWorkflowData;
    use ResolvesAmSpecCompany;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING QC WORKFLOW SEEDING');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();
            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');

                return;
            }

            $qcTypes = DB::connection('pgsql')->table('qc_types')->where('is_active', true)->get()->keyBy('code');
            $qcSchemes = DB::connection('pgsql')->table('qc_scheme')->where('is_active', true)->get()->keyBy('code');

            if ($qcTypes->isEmpty() || $qcSchemes->isEmpty()) {
                $this->command?->error('QC types or schemes missing. Run Phase 11 first.');

                return;
            }

            $activeUser = User::query()->where('active', 1)->first() ?? User::query()->first();
            if (! $activeUser) {
                $this->command?->error('No active user found for QC workflow seeding.');

                return;
            }

            $this->clearAmSpecQcWorkflowData();

            $standards = $this->seedQcStandards($company, $qcTypes, $qcSchemes, $activeUser);
            $this->seedQcStandardAnalytes($standards);
            $this->seedQcApprovers($activeUser);
            $this->seedQcBatches($company, $qcTypes, $qcSchemes, $activeUser);
            $this->seedUnprocessedQcResults($qcTypes, $qcSchemes);

            $this->command?->info('====================================================');
            $this->command?->info('QC WORKFLOW SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    /**
     * @return array<string, object>
     */
    private function seedQcStandards(Company $company, $qcTypes, $qcSchemes, User $activeUser): array
    {
        $isoScheme = $qcSchemes->get('ISO-17025') ?? $qcSchemes->first();
        $astmScheme = $qcSchemes->get('ASTM-PET') ?? $qcSchemes->first();

        $definitions = [
            'STD-QC-REF' => [
                'name' => 'Certified Reference Material Standard 102',
                'qc_type' => 'CRM',
                'scheme' => $isoScheme,
            ],
            'STD-QC-BLK' => [
                'name' => 'Instrument Blank Reference',
                'qc_type' => 'BLK',
                'scheme' => $astmScheme,
            ],
            'STD-QC-SPK' => [
                'name' => 'Spiked Calibration Standard Set',
                'qc_type' => 'SPK',
                'scheme' => $astmScheme,
            ],
            'STD-QC-DUP' => [
                'name' => 'Duplicate Control Reference',
                'qc_type' => 'DUP',
                'scheme' => $isoScheme,
            ],
            'STD-QC-CAL' => [
                'name' => 'Calibration Verification Standard',
                'qc_type' => 'CAL',
                'scheme' => $isoScheme,
            ],
        ];

        $standards = [];
        foreach ($definitions as $code => $definition) {
            $qcType = $qcTypes->get($definition['qc_type']) ?? $qcTypes->first();
            $standardId = (string) Str::uuid();

            DB::connection('pgsql')->table('standards')->updateOrInsert(
                ['code' => $code],
                [
                    'id' => $standardId,
                    'name' => $definition['name'],
                    'status' => true,
                    'is_qc_standard' => true,
                    'qc_type_id' => $qcType->id,
                    'qc_scheme_ids' => $definition['scheme']->code,
                    'edited_by' => $activeUser->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $standards[$code] = DB::connection('pgsql')->table('standards')->where('code', $code)->first();
            $this->command?->info("Seeded QC standard: {$definition['name']} ({$code})");
        }

        return $standards;
    }

    /**
     * @param  array<string, object>  $standards
     */
    private function seedQcStandardAnalytes(array $standards): void
    {
        if (! Schema::connection('pgsql')->hasTable('standards_analytes')) {
            $this->command?->warn('standards_analytes table not found; skipping QC standard analyte tolerances.');

            return;
        }

        $analytes = Analyte::query()->orderBy('code')->limit(6)->get();
        if ($analytes->isEmpty()) {
            $this->command?->warn('No analytes found for QC standard analyte seeding.');

            return;
        }

        $count = 0;
        foreach ($standards as $standard) {
            foreach ($analytes->take(3) as $index => $analyte) {
                $expected = round(5.0 + ($index * 2.5), 3);
                $tolerance = round($expected * 0.1, 3);

                StandardAnalytes::query()->updateOrCreate(
                    [
                        'standard_id' => $standard->id,
                        'analyte_id' => $analyte->id,
                    ],
                    [
                        'standard_value_id' => null,
                        'standard_value_type' => 'is_range',
                        'expected_value' => $expected,
                        'absolute_tolerance' => 0,
                        'tolerance_1' => $tolerance,
                        'tolerance_2' => $tolerance * 2,
                        'low' => $expected - $tolerance,
                        'high' => $expected + $tolerance,
                        'comments' => 'Seeded QC tolerance for '.$analyte->code,
                        'recommendations' => 'Recalibrate if result exceeds tolerance band.',
                        'is_active' => true,
                    ]
                );
                $count++;
            }
        }

        $this->command?->info("Seeded {$count} QC standard analyte tolerance row(s).");
    }

    private function seedQcApprovers(User $activeUser): void
    {
        if (! Schema::connection('pgsql')->hasTable('qc_approvers_config')) {
            $this->command?->warn('qc_approvers_config table not found; skipping QC approvers.');

            return;
        }

        $approverUsers = User::query()
            ->where('active', 1)
            ->where('id', '!=', $activeUser->id)
            ->orderBy('name')
            ->limit(2)
            ->get();

        if ($approverUsers->isEmpty()) {
            $approverUsers = collect([$activeUser]);
        }

        foreach ($approverUsers as $user) {
            Approvers::query()->updateOrCreate(
                ['personnel_id' => $user->id],
                ['created_by' => $activeUser->id]
            );
            $this->command?->info("Seeded QC approver: {$user->name}");
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<string, object>  $qcTypes
     * @param  \Illuminate\Support\Collection<string, object>  $qcSchemes
     */
    private function seedQcBatches(Company $company, $qcTypes, $qcSchemes, User $activeUser): void
    {
        $templateHeader = SampleHeader::query()
            ->with(['samples'])
            ->where('is_qc_batch', false)
            ->whereNotNull('sample_type_id')
            ->whereIn('status', ['Samples In Lab', 'Sample Verification', 'Sample Approval'])
            ->orderByDesc('created_at')
            ->first();

        if (! $templateHeader || $templateHeader->samples->isEmpty()) {
            $this->command?->warn('No in-lab sample batch found to use as QC batch template. Run Phase 9/10 first.');

            return;
        }

        $templateDetail = $templateHeader->samples->first();
        $isoScheme = $qcSchemes->get('ISO-17025') ?? $qcSchemes->first();

        $scenarios = [
            ['code' => 'SEED-QC-CRM-001', 'qc_type' => 'CRM', 'status' => 'Sample Verification'],
            ['code' => 'SEED-QC-BLK-001', 'qc_type' => 'BLK', 'status' => 'Samples In Lab'],
            ['code' => 'SEED-QC-DUP-001', 'qc_type' => 'DUP', 'status' => 'Samples In Lab'],
        ];

        foreach ($scenarios as $scenario) {
            $qcType = $qcTypes->get($scenario['qc_type']) ?? $qcTypes->first();
            $now = now()->subDays(2);

            $qcHeader = $templateHeader->replicate();
            $qcHeader->id = (string) Str::uuid();
            $qcHeader->batch_code = $scenario['code'];
            $qcHeader->reference_number = $scenario['code'];
            $qcHeader->is_qc_batch = true;
            $qcHeader->qc_type_id = $qcType->id;
            $qcHeader->qc_scheme_id = $isoScheme->id;
            $qcHeader->status = $scenario['status'];
            $qcHeader->company_id = $company->id;
            $qcHeader->begin_process = true;
            $qcHeader->isactive = true;
            $qcHeader->receipt_date = $now->toDateString();
            $qcHeader->created_at = $now;
            $qcHeader->updated_at = $now;
            $qcHeader->save();

            $qcDetail = $templateDetail->replicate();
            $qcDetail->id = (string) Str::uuid();
            $qcDetail->sample_header_id = $qcHeader->id;
            $qcDetail->sample_code = $scenario['code'].'-01';
            $qcDetail->created_at = $now;
            $qcDetail->updated_at = $now;
            $qcDetail->save();

            $elements = AnalysisElements::query()
                ->with('analyte')
                ->where('analysis_type_id', $templateDetail->analysis_type_id)
                ->where('active', true)
                ->orderBy('level')
                ->limit(2)
                ->get();

            foreach ($elements as $element) {
                if (! $element->analyte) {
                    continue;
                }

                $guideLow = min((float) $element->lod, 99.0);
                $guideHigh = min((float) $element->hod, 99.0);
                $numeric = round(($guideLow + $guideHigh) / 2, 4);

                $captured = CapturedResult::query()->create([
                    'has_no_result_capture' => false,
                    'sample_detail_code' => $qcDetail->sample_code,
                    'sample_detail_id' => $qcDetail->id,
                    'sample_header_id' => $qcHeader->id,
                    'analyte_id' => $element->analyte_id,
                    'analyte_code' => $element->analyte->code,
                    'result' => (string) $numeric,
                    'user_id' => $activeUser->id,
                    'analysis_type_id' => $templateDetail->analysis_type_id,
                    'analysis_element_id' => $element->id,
                    'remark' => 'Seeded QC batch measurement.',
                    'scienctific_result' => (string) $numeric,
                ]);

                $result = Result::query()->create([
                    'captured_result_id' => $captured->id,
                    'sample_detail_code' => $qcDetail->sample_code,
                    'sample_detail_id' => $qcDetail->id,
                    'sample_header_id' => $qcHeader->id,
                    'analyte_id' => $element->analyte_id,
                    'analyte_code' => $element->analyte->code,
                    'result' => (string) $numeric,
                    'guide_low' => $guideLow,
                    'guide_high' => $guideHigh,
                    'guide' => $guideLow.' - '.$guideHigh,
                    'status_code' => 'PASSED',
                    'analysis_type_id' => $templateDetail->analysis_type_id,
                    'correct_target' => $numeric,
                    'standard_target' => $numeric,
                    'remark' => 'QC batch result',
                ]);

                $processed = DB::connection('pgsql')->table('qc_processed_result')
                    ->where('analyte_id', $element->analyte_id)
                    ->where('analysis_type_id', $templateDetail->analysis_type_id)
                    ->first();

                DB::connection('pgsql')->table('qc_results')->insert([
                    'id' => (string) Str::uuid(),
                    'captured_result_id' => $captured->id,
                    'sample_detail_code' => $qcDetail->sample_code,
                    'sample_detail_id' => $qcDetail->id,
                    'sample_header_id' => $qcHeader->id,
                    'analyte_id' => $element->analyte_id,
                    'analyte_code' => $element->analyte->code,
                    'result' => (string) $numeric,
                    'guide' => $guideLow.' - '.$guideHigh,
                    'guide_low' => $guideLow,
                    'guide_high' => $guideHigh,
                    'status_code' => 'PASSED',
                    'is_qc_processed' => true,
                    'analyte_processed_id' => $processed?->id,
                    'qc' => true,
                    'correct_target' => $numeric,
                    'standard_target' => $numeric,
                    'analysis_type_id' => $templateDetail->analysis_type_id,
                    'remarks' => 'QC workflow batch measurement',
                    'qc_scheme_id' => $isoScheme->id,
                    'qc_type_id' => $qcType->id,
                    'result_id' => $result->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $this->command?->info("Seeded QC batch {$scenario['code']} ({$scenario['qc_type']}, {$scenario['status']})");
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<string, object>  $qcTypes
     * @param  \Illuminate\Support\Collection<string, object>  $qcSchemes
     */
    private function seedUnprocessedQcResults($qcTypes, $qcSchemes): void
    {
        $processed = DB::connection('pgsql')->table('qc_processed_result')->orderBy('updated_at')->first();
        if (! $processed) {
            $this->command?->warn('No qc_processed_result rows found; skipping unprocessed QC queue.');

            return;
        }

        $analyte = Analyte::query()->find($processed->analyte_id);
        $analysisType = AnalysisType::query()->find($processed->analysis_type_id);
        $templateResult = DB::connection('pgsql')->table('qc_results')
            ->where('analyte_id', $processed->analyte_id)
            ->orderByDesc('created_at')
            ->first();

        if (! $analyte || ! $analysisType || ! $templateResult) {
            $this->command?->warn('Insufficient QC context to seed unprocessed results queue.');

            return;
        }

        $crmType = $qcTypes->get('CRM') ?? $qcTypes->first();
        $isoScheme = $qcSchemes->get('ISO-17025') ?? $qcSchemes->first();
        $mean = (float) ($processed->robust_mean ?? 10);

        for ($i = 1; $i <= 5; $i++) {
            $numeric = round($mean + (($i - 3) * 0.5), 4);
            DB::connection('pgsql')->table('qc_results')->insert([
                'id' => (string) Str::uuid(),
                'captured_result_id' => $templateResult->captured_result_id,
                'sample_detail_code' => $templateResult->sample_detail_code,
                'sample_detail_id' => $templateResult->sample_detail_id,
                'sample_header_id' => $templateResult->sample_header_id,
                'analyte_id' => $analyte->id,
                'analyte_code' => $analyte->code,
                'result' => (string) $numeric,
                'guide' => $templateResult->guide,
                'guide_low' => $templateResult->guide_low,
                'guide_high' => $templateResult->guide_high,
                'status_code' => 'PASSED',
                'is_qc_processed' => false,
                'analyte_processed_id' => $processed->id,
                'qc' => true,
                'correct_target' => $mean,
                'standard_target' => $mean,
                'analysis_type_id' => $analysisType->id,
                'remarks' => 'Pending robust statistics processing',
                'qc_scheme_id' => $isoScheme->id,
                'qc_type_id' => $crmType->id,
                'created_at' => now()->subHours($i),
                'updated_at' => now()->subHours($i),
            ]);
        }

        $this->command?->info('Seeded 5 unprocessed QC results for the processing workflow queue.');
    }
}
