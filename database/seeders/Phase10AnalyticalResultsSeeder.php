<?php

namespace Database\Seeders;

use App\AnalysisElements;
use App\CapturedResult;
use App\Result;
use App\SampleDetails;
use App\User;
use App\Lab;
use App\Services\Dashboards\Concerns\DashboardHelpers;
use Database\Seeders\Concerns\ClearsAmSpecAnalyticalResultsData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase10AnalyticalResultsSeeder extends Seeder
{
    use ClearsAmSpecAnalyticalResultsData;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 10 SEEDING: Analytical Results & TAT');
            $this->command?->info('====================================================');

            $this->clearAmSpecAnalyticalResultsData();

            $activeUser = User::query()->where('active', 1)->first() ?? User::query()->first();
            if (! $activeUser) {
                $this->command?->error('No users found. Run setup/personnel import first.');
                return;
            }

            $details = SampleDetails::query()
                ->orderBy('created_at')
                ->get();

            $seededSamples = 0;
            $seededParameters = 0;
            $tatIndex = 0;
            $withinTat = 0;
            $delayed = 0;
            $monthlyParameterCounts = [];

            foreach ($details as $detail) {
                $header = $detail->getSampleHeader();
                if (! $header) {
                    continue;
                }
                if (in_array($header->status, ['Samples Receiving', 'Samples Request Review'], true)) {
                    continue;
                }

                $createdAt = Carbon::parse($detail->created_at ?: $header->created_at ?: now());
                $parameterLimit = $this->parameterCountForSample($seededSamples);
                $elements = AnalysisElements::query()
                    ->with('analyte')
                    ->where('analysis_type_id', $detail->analysis_type_id)
                    ->where('active', true)
                    ->orderBy('level')
                    ->orderBy('id')
                    ->limit($parameterLimit)
                    ->get();

                if ($elements->isEmpty()) {
                    continue;
                }

                if ($elements->count() < 2) {
                    $this->command?->warn("Sample {$detail->sample_code} has only {$elements->count()} configured parameter(s).");
                }

                $seededSamples++;

                foreach ($elements as $element) {
                    if (! $element->analyte) {
                        continue;
                    }

                    [$numericValue, $resultText] = $this->makeResult($element->analyte->code, (float) $element->lod, (float) $element->hod);
                    $guideLow = $this->capDecimal((float) $element->lod);
                    $guideHigh = $this->capDecimal((float) $element->hod);
                    $target = $this->capDecimal(($guideLow + $guideHigh) / 2);

                    $captured = CapturedResult::query()->updateOrCreate(
                        [
                            'sample_detail_id' => $detail->id,
                            'analyte_id' => $element->analyte_id,
                        ],
                        [
                            'has_no_result_capture' => false,
                            'sample_detail_code' => $detail->sample_code,
                            'sample_header_id' => $header->id,
                            'analyte_code' => $element->analyte->code,
                            'result' => $resultText,
                            'user_id' => $header->specialist_analyst_id ?: $activeUser->id,
                            'analysis_type_id' => $detail->analysis_type_id,
                            'analysis_element_id' => $element->id,
                            'analysis_type_order' => 0,
                            'parameters_order' => 0,
                            'analyte_status_contracted' => false,
                            'remark' => 'Analysis performed within configured lab/directorate parameters.',
                            'scienctific_result' => $resultText,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]
                    );

                    Result::query()->updateOrCreate(
                        [
                            'sample_detail_id' => $detail->id,
                            'analyte_id' => $element->analyte_id,
                        ],
                        [
                            'has_no_result_capture' => false,
                            'captured_result_id' => $captured->id,
                            'sample_detail_code' => $detail->sample_code,
                            'sample_header_id' => $header->id,
                            'analyte_code' => $element->analyte->code,
                            'result' => substr($resultText, 0, 100),
                            'guide' => $element->lod.' - '.$element->hod,
                            'comments' => 'Generated from AmSpec parameter matrix.',
                            'recheck' => false,
                            'guide_low' => $guideLow,
                            'guide_high' => $guideHigh,
                            'unit_code' => $element->reporting_unit,
                            'status_code' => 1,
                            'reporting_symbol' => $element->reporting_symbol,
                            'qc' => false,
                            'correct_target' => $target,
                            'standard_target' => $target,
                            'recommendations' => 'None',
                            'initial_result' => $this->capDecimal($numericValue),
                            'initial_reporting_symbol' => $element->reporting_symbol,
                            'very_low_guide' => $this->capDecimal($guideLow * 0.9),
                            'very_high_guide' => $this->capDecimal($guideHigh * 1.1),
                            'analysis_type_id' => $detail->analysis_type_id,
                            'remarks' => 'Result validated by supervisor.',
                            'analyte_status_contracted' => false,
                            'analyte_accredited' => true,
                            'analysis_type_order' => 0,
                            'parameters_order' => 0,
                            'scienctific_result' => substr($resultText, 0, 255),
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]
                    );

                    $targetDeadline = $createdAt->copy()->addHours((int) ($element->reporting_time ?: 48));
                    $finishedDate = $this->finishedDateForTatBucket($targetDeadline, $tatIndex++);
                    $signedOffset = DashboardHelpers::computeSignedTatOffset(
                        $targetDeadline->toDateTimeString(),
                        $finishedDate->toDateTimeString()
                    );

                    DB::connection('pgsql')->table('tat_captured')->updateOrInsert(
                        ['captured_result_id' => $captured->id],
                        [
                            'id' => DB::connection('pgsql')->table('tat_captured')
                                ->where('captured_result_id', $captured->id)
                                ->value('id') ?? (string) Str::uuid(),
                            'analysis_type_id' => $detail->analysis_type_id,
                            'analyte_id' => $element->analyte_id,
                            'sample_type_id' => $header->sample_type_id,
                            'sample_detail_id' => $detail->id,
                            'sample_header_id' => $header->id,
                            'result' => substr($resultText, 0, 100),
                            'analyst_id' => $header->specialist_analyst_id ?: $activeUser->id,
                            'tat_overdue_days' => $signedOffset,
                            'tat_remark' => $this->tatRemarkForOffset($signedOffset),
                            'tat_date' => $targetDeadline,
                            'finished_date' => $finishedDate,
                            'is_complete' => true,
                            'start_date_analysis' => $createdAt,
                            'updated_at' => $createdAt,
                            'created_at' => $createdAt,
                        ]
                    );

                    $monthlyParameterCounts[$finishedDate->format('Y-m')] = ($monthlyParameterCounts[$finishedDate->format('Y-m')] ?? 0) + 1;
                    $signedOffset > 0 ? $delayed++ : $withinTat++;
                    $seededParameters++;

                    $this->consumeReagentForParameter($detail, $element, $createdAt);
                }
            }

            $monthsWithData = count($monthlyParameterCounts);
            $monthsWithMultiple = collect($monthlyParameterCounts)->filter(fn($count) => $count > 1)->count();
            $avgParametersPerSample = $seededSamples > 0 ? round($seededParameters / $seededSamples, 2) : 0;

            $this->command?->info("Seeded or updated analytical results for {$seededSamples} sample details.");
            $this->command?->info("Seeded {$seededParameters} parameter results ({$avgParametersPerSample} avg parameters/sample).");
            $this->command?->info("TAT distribution: {$withinTat} within TAT, {$delayed} delayed.");
            $this->command?->info("Monthly coverage: {$monthsWithData} months with data; {$monthsWithMultiple} months with >1 parameter.");
            $this->command?->info('====================================================');
            $this->command?->info('PHASE 10 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function parameterCountForSample(int $sampleIndex): int
    {
        return match ($sampleIndex % 10) {
            6, 7 => 3,
            8 => 4,
            9 => 5,
            default => 2,
        };
    }

    private function finishedDateForTatBucket(Carbon $targetDeadline, int $tatIndex): Carbon
    {
        return match ($tatIndex % 10) {
            0 => $targetDeadline->copy()->subDays(2),
            1 => $targetDeadline->copy()->subDay(),
            2 => $targetDeadline->copy()->subHours(6),
            3 => $targetDeadline->copy()->addHours(4),
            4 => $targetDeadline->copy()->addHours(10),
            5 => $targetDeadline->copy()->subHours(12),
            6 => $targetDeadline->copy()->addHours(13),
            7 => $targetDeadline->copy()->addDays(3)->addHours(13),
            8 => $targetDeadline->copy()->addDays(6)->addHours(13),
            default => $targetDeadline->copy()->addDays(10)->addHours(13),
        };
    }

    private function tatRemarkForOffset(int $signedOffset): int
    {
        if ($signedOffset <= -2) return 1;
        if ($signedOffset === -1) return 2;
        if ($signedOffset === 0) return 3;
        if ($signedOffset === 1) return 4;
        return 5;
    }

    private function consumeReagentForParameter(SampleDetails $detail, AnalysisElements $element, Carbon $createdAt): void
    {
        $lab = Lab::with('zone')->find($detail->lab_id);
        if (! $lab || ! $lab->zone) {
            return;
        }

        $locationId = $lab->zone->inventory_location_id;
        if (! $locationId) {
            return;
        }

        $reagentItem = DB::connection('pgsql')->table('inventory_items')
            ->where('inventory_store_id', function ($query) use ($locationId) {
                $query->select('id')->from('inventory_stores')->where('inventory_location_id', $locationId)->limit(1);
            })
            ->first();

        if (! $reagentItem) {
            return;
        }

        $drawVolume = 0.05;
        DB::connection('pgsql')->table('inventory_items')
            ->where('id', $reagentItem->id)
            ->increment('stock_out', $drawVolume);

        DB::connection('pgsql')->table('inventory_item_notes')->insert([
            'id' => (string) Str::uuid(),
            'inventory_item_id' => $reagentItem->id,
            'title' => "Reagent Draw: {$element->analyte->code}",
            'comments' => "Consumed {$drawVolume} units for analysis {$element->analyte->code} on Sample {$detail->sample_code}.",
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function makeResult(string $analyteCode, float $lod, float $hod): array
    {
        $safeHigh = max($lod + 0.01, min($hod, 99.0));
        $value = round($lod + (mt_rand(100, 8500) / 10000) * ($safeHigh - $lod), 4);

        return match ($analyteCode) {
            'ALY-STR' => [99.99, 'STR DNA Profile Match: 99.99% Probability'],
            'ALY-WDNA' => [99.5, 'Positive wildlife species DNA match'],
            'ALY-TVC' => [$value, "{$value} CFU/g detected"],
            'ALY-PH' => [$value, "pH {$value}"],
            'ALY-CAL' => [$value, "{$value}% calibration deviation"],
            'ALY-EC' => [$value, "{$value} CFU/g E. coli detected"],
            'ALY-SALM' => [0.0, 'Salmonella: Not Detected in 25g'],
            'ALY-SA' => [0.0, 'Staphylococcus aureus: Not Detected in 25g'],
            'ALY-YM' => [$value, "{$value} CFU/g Yeast & Mold"],
            'ALY-LIST' => [0.0, 'Listeria monocytogenes: Not Detected in 25g'],
            'ALY-PA' => [0.0, 'Pseudomonas aeruginosa: Not Detected in 25g'],
            'ALY-TCC' => [$value, "{$value} CFU/100mL Total Coliforms"],
            'ALY-ENT' => [$value, "{$value} CFU/g Enterobacteriaceae"],
            'ALY-TURB' => [$value, "{$value} NTU Turbidity"],
            'ALY-DO' => [$value, "{$value} mg/L Dissolved Oxygen"],
            'ALY-COD-ENV' => [$value, "{$value} mg/L COD"],
            'ALY-TDS' => [$value, "{$value} mg/L TDS"],
            'ALY-SOC' => [$value, "{$value}% Organic Carbon"],
            'ALY-NIT' => [$value, "{$value} mg/kg Nitrogen"],
            'ALY-CD' => [$value, "{$value} mg/kg Cadmium"],
            'ALY-HG' => [$value, "{$value} mg/kg Mercury"],
            default => [$value, "Detected: {$value}"],
        };
    }

    private function capDecimal(float $value): float
    {
        return round(min(99.0, max(0.0, $value)), 6);
    }
}
