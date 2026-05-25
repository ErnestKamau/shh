<?php

namespace Database\Seeders;

use App\AnalysisType;
use App\Company;
use App\Lab;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\SampleAnalysisStage;
use App\SampleDetails;
use App\SampleHeader;
use App\User;
use App\Zone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Phase9SampleWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 9 SEEDING: 3-Year Sample Workflow');
            $this->command?->info('====================================================');

            $company = Company::query()->first();
            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');
                return;
            }

            $zones = Zone::query()->orderBy('key')->get()->keyBy('key');
            $customers = CRMCustomer::query()->orderBy('is_internal', 'desc')->orderBy('name')->get();
            $analysisTypes = AnalysisType::query()->with('lab')->where('company_id', $company->id)->get();
            $users = User::query()->where('active', 1)->get();

            if ($zones->isEmpty() || $customers->isEmpty() || $analysisTypes->isEmpty() || $users->isEmpty()) {
                $this->command?->error('Missing zones, CRM customers, analysis types, or users. Run earlier phases first.');
                return;
            }

            $stages = $this->seedWorkflowStages($company, $analysisTypes);
            $sequence = $this->initialiseBatchSequences();

            $progression = [
                ['status' => 'Completed Sample', 'stage' => 'APP', 'processed' => true, 'active' => false],
                ['status' => 'Sample Approval', 'stage' => 'APP', 'processed' => true, 'active' => true],
                ['status' => 'Sample Verification', 'stage' => 'QCR', 'processed' => true, 'active' => true],
                ['status' => 'Samples In Lab', 'stage' => 'TEST', 'processed' => true, 'active' => true],
                ['status' => 'Samples Request Review', 'stage' => 'PREP', 'processed' => false, 'active' => true],
                ['status' => 'Samples Receiving', 'stage' => 'REC', 'processed' => false, 'active' => true],
            ];

            $offences = [
                'Possession with Intent to Supply',
                'Drug Trafficking',
                'Food Product Compliance',
                'Water Quality Surveillance',
                'Environmental Contamination Investigation',
                'Court Ordered Independent Analysis',
                'Port Entry Product Verification',
                'Suspected Poisoning',
                'Wildlife Trafficking',
                'Calibration Service Request',
            ];

            $created = 0;
            $activeBatchIndex = 0;
            $years = [now()->year - 2, now()->year - 1, now()->year];

            foreach ($years as $yearIndex => $year) {
                foreach (range(1, 12) as $month) {
                    foreach ($zones as $zoneCode => $zone) {
                        for ($caseNo = 1; $caseNo <= 2; $caseNo++) {
                            $createdAt = Carbon::create($year, $month, min(25, 4 + ($caseNo * 7)), 9 + $caseNo, 0, 0);
                            if ($createdAt->isFuture()) {
                                continue;
                            }

                            $sequenceKey = "{$zoneCode}-{$year}";
                            $sequence[$sequenceKey] = ($sequence[$sequenceKey] ?? 0) + 1;
                            $batchCode = $zoneCode.$year.'-'.str_pad((string) $sequence[$sequenceKey], 5, '0', STR_PAD_LEFT);

                            $analysisType = $analysisTypes->values()[($created + $caseNo + $month) % $analysisTypes->count()];
                            $lab = $analysisType->lab ?? Lab::query()->find($analysisType->lab_id);
                            $customer = $customers->values()[($created + $month) % $customers->count()];
                            $unit = CRMCompanyUnit::query()->where('crm_customer_id', $customer->id)->first();
                            $offence = $offences[$created % count($offences)];
                            $status = $this->statusFor($yearIndex, $created, $progression);
                            $stage = $stages[$status['stage']] ?? $stages['REC'];
                            $receivingUser = $users->values()[$created % $users->count()];
                            $specialistUser = $users->values()[($created + 1) % $users->count()];
                            $verifyUser = $users->values()[($created + 2) % $users->count()];
                            $samplePointId = $this->zoneSamplePointId($unit, $zoneCode);
                            $receiptDate = $createdAt->copy()->addDay();
                            $processingDate = $status['processed'] ? $receiptDate->copy()->addDay() : null;
                            $approvalDate = in_array($status['status'], ['Sample Approval', 'Completed Sample'], true)
                                ? $receiptDate->copy()->addDays(4)
                                : null;

                            $caseReference = 'GCLA/'.$year.'/'.$zoneCode.'/'.str_pad((string) $sequence[$sequenceKey], 5, '0', STR_PAD_LEFT);
                            $dateExpected = $status['active']
                                ? $this->dateExpectedForActiveBatch($activeBatchIndex++)
                                : $receiptDate->copy()->addDays(5);

                            $submissionId = DB::connection('pgsql')->table('sample_submission_requests')
                                ->where('case_no', $caseReference)
                                ->value('id') ?? (string) Str::uuid();

                            DB::connection('pgsql')->table('sample_submission_requests')->updateOrInsert(
                                ['id' => $submissionId],
                                [
                                    'crm_customer_id' => $customer->id,
                                    'submitting_agency' => $customer->name,
                                    'submitting_officer_full_name' => $receivingUser->name,
                                    'submitting_officer_title' => $customer->is_internal ? 'Investigating Officer' : 'Submitting Representative',
                                    'physical_address' => $customer->physical_address ?? $zone->value,
                                    'region' => $zone->value,
                                    'district' => $zone->value.' District',
                                    'working_station' => $zone->value.' Office',
                                    'office_telephone_no' => '+25420'.random_int(1000000, 9999999),
                                    'mobile_telephone_no' => '+2547'.random_int(10000000, 99999999),
                                    'case_no' => $caseReference,
                                    'offence' => $offence,
                                    'date_of_seizure' => $createdAt->copy()->subDays(3)->format('Y-m-d'),
                                    'seizure_region' => $zone->value,
                                    'seizure_district' => $zone->value.' District',
                                    'seizure_ward' => 'Central Ward',
                                    'seizure_village_street' => 'Evidence Street',
                                    'submitted_by_full_name' => $receivingUser->name,
                                    'submitted_by_title' => 'Submitting Officer',
                                    'submitted_by_date' => $createdAt->format('Y-m-d'),
                                    'submitted_by_time' => $createdAt->format('H:i'),
                                    'received_by_full_name' => $specialistUser->name,
                                    'received_by_title' => 'Laboratory Reception Officer',
                                    'received_by_date' => $receiptDate->format('Y-m-d'),
                                    'received_by_time' => $receiptDate->format('H:i'),
                                    'submission_date' => $createdAt->format('Y-m-d'),
                                    'group_of_samples' => $analysisType->sample_type?->name,
                                    'number_of_samples' => 2,
                                    'description_of_samples' => "{$offence} samples submitted through {$zone->value}.",
                                    'gcla_file_reference_number' => $caseReference,
                                    'is_police_sample' => (bool) $customer->is_internal,
                                    'status' => $status['status'],
                                    'updated_at' => $createdAt,
                                    'created_at' => $createdAt,
                                ]
                            );

                            $headerPayload = [
                                'case_id' => $caseReference,
                                'crm_customer_id' => $customer->id,
                                'crm_unit_id' => $unit?->id,
                                'crm_unit_name' => $unit?->name ?? $customer->name,
                                'sample_type_id' => $analysisType->sample_type_id,
                                'lab_id' => $lab?->id,
                                'status' => $status['status'],
                                'sample_tracking_stage' => $stage->id,
                                'has_method_deviation' => false,
                                'sample_detail_processed' => $status['processed'],
                                'is_routine' => false,
                                'routine_frequency' => 0.0,
                                'priority' => $created % 11 === 0 ? 'High' : 'Normal',
                                'receiving_officer' => $receivingUser->id,
                                'sampling_officer' => $receivingUser->id,
                                'specialist_analyst_id' => $specialistUser->id,
                                'verify_user_id' => in_array($status['stage'], ['QCR', 'APP'], true) ? $verifyUser->id : null,
                                'approve_user_id' => $status['stage'] === 'APP' ? $verifyUser->id : null,
                                'date_collected' => $createdAt,
                                'receipt_date' => $receiptDate,
                                'processing_date' => $processingDate?->format('Y-m-d'),
                                'approval_date' => $approvalDate?->format('Y-m-d'),
                                'date_expected' => $dateExpected->format('Y-m-d H:i:s'),
                                'isactive' => $status['active'],
                                'begin_process' => $status['processed'],
                                'description' => "Case: {$caseReference} | {$offence} | {$zone->value}",
                                'reason_for_submission' => $offence,
                                'where_sample_was_obtained' => $zone->value,
                                'year' => $year,
                                'week' => (int) $createdAt->format('W'),
                                'updated_at' => $createdAt,
                                'created_at' => $createdAt,
                            ];

                            if (Schema::hasColumn('sample_headers', 'zone_id')) {
                                $headerPayload['zone_id'] = $zone->id;
                                $headerPayload['processing_zone_id'] = $lab?->zone_id ?: $zone->id;
                                $headerPayload['reporting_zone_id'] = $lab?->zone_id ?: $zone->id;
                            }

                            $header = SampleHeader::query()->updateOrCreate(
                                ['batch_code' => $batchCode],
                                $headerPayload
                            );

                            DB::connection('pgsql')->table('sample_submission_requests')
                                ->where('id', $submissionId)
                                ->update(['sample_header_id' => $header->id]);

                            foreach (['A', 'B'] as $detailIndex => $suffix) {
                                $detailPayload = [
                                    'sample_header_id' => $header->id,
                                    'analysis_type_id' => $analysisType->id,
                                    'lab_id' => $lab?->id,
                                    'crm_unit_id' => $unit?->id,
                                    'has_no_result_capture' => false,
                                    'is_ammendment' => false,
                                    'sample_no' => 'SMP-'.str_pad((string) (($created * 2) + $detailIndex + 1), 8, '0', STR_PAD_LEFT),
                                    'quantity' => $detailIndex === 0 ? '250g' : '100ml',
                                    'barcode' => 'BC-'.strtoupper(Str::random(10)),
                                    'short_code' => 'EXH-'.($detailIndex + 1),
                                    'material_status' => 'Received',
                                    'comments' => "Exhibit {$suffix}: sealed and chain-of-custody verified.",
                                    'updated_at' => $createdAt,
                                    'created_at' => $createdAt,
                                ];

                                if (Schema::hasColumn('sample_details', 'processing_zone_id')) {
                                    $detailPayload['processing_zone_id'] = $lab?->zone_id ?: $zone->id;
                                }

                                if ($samplePointId && Schema::hasColumn('sample_details', 'sample_point_id')) {
                                    $detailPayload['sample_point_id'] = $samplePointId;
                                }

                                $detail = SampleDetails::query()->updateOrCreate(
                                    ['sample_code' => $batchCode.'-'.$suffix],
                                    $detailPayload
                                );

                                DB::connection('pgsql')->table('sample_submission_request_exhibits')->updateOrInsert(
                                    [
                                        'sample_submission_request_id' => $submissionId,
                                        'sample_detail_id' => $detail->id,
                                    ],
                                    [
                                        'id' => DB::connection('pgsql')->table('sample_submission_request_exhibits')
                                            ->where('sample_submission_request_id', $submissionId)
                                            ->where('sample_detail_id', $detail->id)
                                            ->value('id') ?? (string) Str::uuid(),
                                        'serial_number' => $detailIndex + 1,
                                        'number_of_items' => random_int(1, 3),
                                        'item_description' => "Exhibit {$suffix} for {$analysisType->name}",
                                        'suspected_item' => $analysisType->sample_type?->name ?? 'Submitted sample',
                                        'updated_at' => $createdAt,
                                        'created_at' => $createdAt,
                                    ]
                                );
                            }

                            $created++;
                        }
                    }
                }
            }

            $this->command?->info("Seeded or updated {$created} sample batches across three years.");
            $this->command?->info('====================================================');
            $this->command?->info('PHASE 9 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function seedWorkflowStages(Company $company, $analysisTypes): array
    {
        $firstLabId = $analysisTypes->first()?->lab_id;
        $stageData = [
            'REC' => ['name' => 'Samples Receiving', 'level' => 1],
            'PREP' => ['name' => 'Samples Request Review', 'level' => 2],
            'TEST' => ['name' => 'Samples In Lab', 'level' => 3],
            'QCR' => ['name' => 'Sample Verification', 'level' => 4],
            'APP' => ['name' => 'Sample Approval', 'level' => 5],
        ];

        $stages = [];
        foreach ($stageData as $code => $data) {
            $stages[$code] = SampleAnalysisStage::query()->updateOrCreate(
                ['code' => $code, 'company_id' => $company->id],
                [
                    'name' => $data['name'],
                    'title' => $data['name'],
                    'active' => true,
                    'level' => $data['level'],
                    'is_system' => true,
                    'is_sample_stage' => true,
                    'sample_workflow' => $data['name'],
                    'lab_id' => $firstLabId,
                ]
            );
        }

        // Bridge: Seed sample_to_sample_analysis_stages pivots
        $sampleTypes = \App\SampleType::query()->where('company_id', $company->id)->get();
        foreach ($stages as $stage) {
            foreach ($sampleTypes as $st) {
                DB::connection('pgsql')->table('sample_to_sample_analysis_stages')->updateOrInsert(
                    [
                        'sample_type_id' => $st->id,
                        'sample_analysis_stage_id' => $stage->id,
                    ],
                    [
                        'id' => DB::connection('pgsql')->table('sample_to_sample_analysis_stages')
                            ->where('sample_type_id', $st->id)
                            ->where('sample_analysis_stage_id', $stage->id)
                            ->value('id') ?? (string) Str::uuid(),
                        'active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return $stages;
    }

    private function initialiseBatchSequences(): array
    {
        $sequences = [];
        $existing = SampleHeader::query()
            ->where('batch_code', '~', '^[A-Z]+[0-9]{4}-[0-9]{5}$')
            ->pluck('batch_code');

        foreach ($existing as $batchCode) {
            if (! preg_match('/^([A-Z]+)([0-9]{4})-([0-9]{5})$/', $batchCode, $matches)) {
                continue;
            }

            $key = $matches[1].'-'.$matches[2];
            $sequences[$key] = max($sequences[$key] ?? 0, (int) $matches[3]);
        }

        return $sequences;
    }

    private function statusFor(int $yearIndex, int $recordIndex, array $progression): array
    {
        if ($yearIndex === 0) {
            return $progression[0];
        }

        if ($yearIndex === 1) {
            return $progression[$recordIndex % 4];
        }

        return $progression[$recordIndex % count($progression)];
    }

    private function dateExpectedForActiveBatch(int $activeBatchIndex): Carbon
    {
        $bucket = $activeBatchIndex % 10;

        if ($bucket <= 5) {
            return now()->addDays(($bucket % 5) + 1)->startOfDay();
        }

        return match ($bucket) {
            6 => now()->subDays(1)->subHours(13),
            7 => now()->subDays(3)->subHours(13),
            8 => now()->subDays(6)->subHours(13),
            default => now()->subDays(10)->subHours(13),
        };
    }

    private function zoneSamplePointId(?CRMCompanyUnit $unit, string $zoneCode): ?string
    {
        if (! $unit || ! Schema::hasTable('sample_points')) {
            return null;
        }

        $location = Phase2LocationSeeder::ZONE_LOCATIONS[$zoneCode] ?? null;
        if (! $location) {
            return null;
        }

        $pointName = "{$location['name']} - {$unit->name}";
        $existing = DB::connection('pgsql')
            ->table('sample_points')
            ->where('crm_company_unit_id', $unit->id)
            ->where('name', $pointName)
            ->first();

        if ($existing) {
            return (string) $existing->id;
        }

        $pointId = (string) Str::uuid();
        $payload = [
            'id' => $pointId,
            'crm_company_unit_id' => $unit->id,
            'crm_customer_id' => $unit->crm_customer_id,
            'name' => $pointName,
            'description' => sprintf(
                '%s office at %s. Regions served: %s.',
                $location['name'],
                $location['office'],
                implode(', ', $location['regions'])
            ),
            'gps' => $location['gps'],
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('sample_points', 'code')) {
            $payload['code'] = 'GCLA-'.$zoneCode.'-'.substr(md5((string) $unit->id), 0, 8);
        }

        DB::connection('pgsql')->table('sample_points')->insert($payload);

        return $pointId;
    }
}
