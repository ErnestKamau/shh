<?php

namespace Database\Seeders;

use App\Company;
use App\Lab;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanyUnit;
use App\SampleType;
use App\AnalysisType;
use App\SampleAnalysisStage;
use App\SampleHeader;
use App\SampleDetails;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Phase2LaboratoryWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds a complete, standard LIMS sample lifecycle:
     *
     *   1. Workflow Stages  (LIMS pipeline steps)
     *   2. Sample Submission Requests  (client-facing intake forms)
     *   3. Sample Headers  (laboratory batch records, one per request)
     *   4. Sample Details  (individual test items inside each batch,
     *                       routed to the correct forensic lab)
     *
     * Analysts (seeded in Phase 6) are linked retrospectively via
     * specialist_analyst_id once they exist. Here we bind the base
     * admin user as placeholder; Phase 6 redistributes.
     */
    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function () {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 2 SEEDING: Standard LIMS Workflow');
            $this->command?->info('====================================================');

            // ----------------------------------------------------------------
            // 0. Base Context
            // ----------------------------------------------------------------
            $company = Company::first();
            if (!$company) {
                $this->command?->error('Base company not found! Run Phase 1 first.');
                return;
            }

            // All active users serve as placeholders; Phase 6 will redistribute
            $users = User::where('active', 1)->get();
            if ($users->isEmpty()) {
                $users = User::all();
            }
            if ($users->isEmpty()) {
                $this->command?->error('No users found! Restore users from dump first.');
                return;
            }

            $customers    = CRMCustomer::all();
            $sampleTypes  = SampleType::all();
            $analysisTypes = AnalysisType::all();

            if ($customers->isEmpty() || $sampleTypes->isEmpty()) {
                $this->command?->error('CRM Customers or Sample Types missing! Run Phase 1 first.');
                return;
            }

            // Labs indexed by code for deterministic routing
            $labs = Lab::where('company_id', $company->id)->get()->keyBy('code');

            $labChm = $labs->get('LAB-CHM');
            $labDna = $labs->get('LAB-DNA');
            $labTox = $labs->get('LAB-TOX');

            if (!$labChm || !$labDna || !$labTox) {
                $this->command?->error('Expected lab departments (LAB-CHM, LAB-DNA, LAB-TOX) not found! Run Phase 1 first.');
                return;
            }

            // ----------------------------------------------------------------
            // 1. Seed 5 core lab workflow stages
            //    These stage records are intended to mirror the lab module's
            //    sample workflow sidebar stages and status progression.
            // ----------------------------------------------------------------
            $stagesData = [
                ['name' => 'Samples Receiving',   'code' => 'REC',  'level' => 1, 'workflow' => 'Samples Receiving'],
                ['name' => 'Samples Request Review', 'code' => 'PREP', 'level' => 2, 'workflow' => 'Samples Request Review'],
                ['name' => 'Samples In Lab',     'code' => 'TEST', 'level' => 3, 'workflow' => 'Samples In Lab'],
                ['name' => 'Sample Verification','code' => 'QCR',  'level' => 4, 'workflow' => 'Sample Verification'],
                ['name' => 'Sample Approval',    'code' => 'APP',  'level' => 5, 'workflow' => 'Sample Approval'],
            ];

            $stages = [];
            foreach ($stagesData as $index => $sd) {
                // Rotate lab association across labs for representational breadth
                $labOptions = [$labChm->id, $labDna->id, $labTox->id];
                $associatedLabId = $labOptions[$index % 3];

                $stage = SampleAnalysisStage::updateOrCreate(
                    ['code' => $sd['code'], 'company_id' => $company->id],
                    [
                        'name'            => $sd['name'],
                        'title'           => $sd['name'],
                        'active'          => true,
                        'level'           => $sd['level'],
                        'is_system'       => true,
                        'is_sample_stage' => true,
                        'sample_workflow' => $sd['workflow'],
                        'lab_id'          => $associatedLabId,
                    ]
                );
                $stages[$sd['code']] = $stage;
                $this->command?->info("  ✓ Stage [{$stage->level}] {$stage->name}");
            }

            // ----------------------------------------------------------------
            // 2. Map sample type codes → correct lab & analysis type
            //    so each sample detail lands in the right department.
            // ----------------------------------------------------------------

            // Chemistry sample type codes (SMP-CAN … SMP-FCO = indices 0–12 in Phase1)
            $chemCodes = ['SMP-CAN','SMP-CAT','SMP-COC','SMP-HER','SMP-AMP','SMP-MET',
                          'SMP-FEN','SMP-FDD','SMP-DKD','SMP-FBL','SMP-FUR','SMP-MCH','SMP-FCO'];
            // DNA codes
            $dnaCodes  = ['SMP-RAP','SMP-MDN','SMP-RDN','SMP-AMDN','SMP-AHDN','SMP-DVI',
                          'SMP-WLP','SMP-WLT','SMP-AAT','SMP-MDNA','SMP-FDO'];
            // Toxicology codes
            $toxCodes  = ['SMP-MTO','SMP-AMTO','SMP-AHTO','SMP-MTOX','SMP-FTO'];

            // Build a helper: sampleTypeCode → [lab_id, analysis_type_id]
            $sampleTypeRouting = [];
            foreach ($sampleTypes as $st) {
                if (in_array($st->code, $chemCodes)) {
                    $lab       = $labChm;
                    $anaCode   = 'ANA-CAN'; // fallback chemistry test
                } elseif (in_array($st->code, $dnaCodes)) {
                    $lab       = $labDna;
                    // Wildlife types use wildlife analysis; human types use STR
                    $anaCode   = in_array($st->code, ['SMP-WLP','SMP-WLT','SMP-AAT'])
                                    ? 'ANA-WLD' : 'ANA-STR';
                } elseif (in_array($st->code, $toxCodes)) {
                    $lab       = $labTox;
                    $anaCode   = 'ANA-TOX';
                } else {
                    $lab       = $labChm;
                    $anaCode   = 'ANA-CAN';
                }

                $ana = $analysisTypes->firstWhere('code', $anaCode)
                    ?? $analysisTypes->where('lab_id', $lab->id)->first()
                    ?? $analysisTypes->first();

                $sampleTypeRouting[$st->id] = [
                    'lab_id'           => $lab->id,
                    'analysis_type_id' => $ana?->id,
                ];
            }

            // ----------------------------------------------------------------
            // 3. Build a deterministic case catalogue with offence details
            //    and a full status progression across 50 cases.
            // ----------------------------------------------------------------
            $offences = [
                'Possession with Intent to Supply',
                'Drug Trafficking',
                'Wildlife Poaching',
                'Homicide',
                'Sexual Assault',
                'Armed Robbery',
                'Attempted Murder',
                'Drug-facilitated Sexual Assault',
                'Illicit Wildlife Trade',
                'Suspected Poisoning',
                'Arson Investigation',
                'Armed Robbery with Violence',
                'Importation of Controlled Substances',
                'Ivory Trafficking',
            ];

            $seizureRegions = [
                'Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret',
                'Malindi', 'Garissa', 'Wajir', 'Marsabit', 'Lamu',
            ];

            /*
             * Status progression: map i%5 → (status, stage_code)
             * This gives an even distribution across core lab workflow stages.
             */
            $progressionMap = [
                0 => ['status' => 'Samples Receiving',      'stage' => 'REC',  'is_active' => false],
                1 => ['status' => 'Samples Request Review', 'stage' => 'PREP', 'is_active' => true],
                2 => ['status' => 'Samples In Lab',        'stage' => 'TEST', 'is_active' => true],
                3 => ['status' => 'Sample Verification',   'stage' => 'QCR',  'is_active' => true],
                4 => ['status' => 'Sample Approval',       'stage' => 'APP',  'is_active' => true],
            ];

            $this->command?->info('');
            $this->command?->info('Seeding 50 case batches with full LIMS workflow...');

            $headers = [];
            $submissionRequests = [];

            for ($i = 1; $i <= 50; $i++) {
                $batchCode = 'GFSL-2026-CASE-' . str_pad($i, 4, '0', STR_PAD_LEFT);
                $caseNo    = 'GCLA/' . now()->year . '/' . str_pad($i, 5, '0', STR_PAD_LEFT);

                $customer    = $customers->values()[$i % $customers->count()];
                $sampleType  = $sampleTypes->values()[$i % $sampleTypes->count()];
                $routing     = $sampleTypeRouting[$sampleType->id];
                $progression = $progressionMap[$i % 5];
                $stage       = $stages[$progression['stage']];

                $unit     = CRMCompanyUnit::where('crm_customer_id', $customer->id)->first();
                $unitId   = $unit?->id;
                $unitName = $unit?->name ?? 'Nairobi Headquarters';

                // Assign users cyclically as receiving / specialist officers
                $receivingUser   = $users->values()[$i % $users->count()];
                $specialistUser  = $users->values()[($i + 1) % $users->count()];
                $verifyUser      = $users->values()[($i + 2) % $users->count()];

                // Timeline — spread over 90 days
                $daysAgo        = 90 - ($i * 1.8);
                $createdAt      = now()->subDays((int)$daysAgo);
                $receiptDate    = (clone $createdAt)->addHours(24);
                $processingDate = null;
                $approvalDate   = null;

                if (in_array($progression['stage'], ['TEST', 'QCR', 'APP'])) {
                    $processingDate = (clone $receiptDate)->addDays(1);
                }
                if ($progression['stage'] === 'APP') {
                    $approvalDate = (clone $processingDate)->addDays(3);
                }

                $seizureRegion = $seizureRegions[$i % count($seizureRegions)];
                $offence       = $offences[$i % count($offences)];

                // ── 3a. Sample Submission Request (the intake form) ──────────
                $submissionId = (string) Str::uuid();
                DB::connection('pgsql')->table('sample_submission_requests')->insert([
                    'id'                          => $submissionId,
                    'crm_customer_id'             => $customer->id,
                    'submitting_agency'           => $customer->name,
                    'submitting_officer_full_name'=> $receivingUser->name,
                    'submitting_officer_title'    => 'Investigating Officer',
                    'physical_address'            => $customer->physical_address ?? $seizureRegion . ' Police Station',
                    'region'                      => $seizureRegion,
                    'district'                    => $seizureRegion . ' Central',
                    'working_station'             => $seizureRegion . ' Police HQ',
                    'office_telephone_no'         => '+25420' . rand(1000000, 9999999),
                    'mobile_telephone_no'         => '+2547'  . rand(10000000, 99999999),
                    'case_no'                     => $caseNo,
                    'offence'                     => $offence,
                    'date_of_seizure'             => (clone $createdAt)->subDays(rand(1, 7))->format('Y-m-d'),
                    'seizure_region'              => $seizureRegion,
                    'seizure_district'            => $seizureRegion . ' District',
                    'seizure_ward'                => 'Central Ward',
                    'seizure_village_street'      => 'Exhibit Street ' . $i,
                    'submitted_by_full_name'      => $receivingUser->name,
                    'submitted_by_title'          => 'Officer-in-Charge',
                    'submitted_by_date'           => $createdAt->format('Y-m-d'),
                    'submitted_by_time'           => $createdAt->format('H:i'),
                    'received_by_full_name'       => $specialistUser->name,
                    'received_by_title'           => 'Laboratory Reception Officer',
                    'received_by_date'            => $receiptDate->format('Y-m-d'),
                    'received_by_time'            => $receiptDate->format('H:i'),
                    'status'                      => $progression['status'],
                    'submission_date'             => $createdAt->format('Y-m-d'),
                    'number_of_samples'           => rand(1, 4),
                    'description_of_samples'      => "Exhibits recovered from {$seizureRegion} — {$offence} investigation.",
                    'gcla_file_reference_number'  => $caseNo,
                    'is_police_sample'            => true,
                    'group_of_samples'            => $sampleType->name,
                    'request_number'              => $i,
                    'created_at'                  => $createdAt,
                    'updated_at'                  => $createdAt,
                ]);
                $submissionRequests[$batchCode] = $submissionId;

                // ── 3b. Sample Header (the lab batch record) ─────────────────
                $header = SampleHeader::create([
                    'batch_code'                  => $batchCode,
                    'case_id'                     => $caseNo,
                    'crm_customer_id'             => $customer->id,
                    'crm_unit_id'                 => $unitId,
                    'crm_unit_name'               => $unitName,
                    'sample_type_id'              => $sampleType->id,
                    'lab_id'                      => $routing['lab_id'],
                    'status'                      => $progression['status'],
                    'sample_tracking_stage'       => $stage->id,
                    'has_method_deviation'        => false,
                    'sample_detail_processed'     => in_array($progression['stage'], ['TEST', 'QCR', 'APP']),
                    'is_routine'                  => false,
                    'routine_frequency'           => 0.0,
                    'priority'                    => $i % 8 === 0 ? 'High' : 'Normal',
                    'receiving_officer'           => $receivingUser->id,
                    'sampling_officer'            => $receivingUser->id,
                    'specialist_analyst_id'       => $specialistUser->id,
                    'verify_user_id'              => in_array($progression['stage'], ['QCR', 'APP'])
                                                        ? $verifyUser->id : null,
                    'approve_user_id'             => $progression['stage'] === 'APP'
                                                        ? $verifyUser->id : null,
                    'date_collected'              => $createdAt,
                    'receipt_date'               => $receiptDate,
                    'processing_date'             => $processingDate?->format('Y-m-d'),
                    'approval_date'               => $approvalDate?->format('Y-m-d'),
                    'date_expected'               => (clone $receiptDate)->addDays(5)->format('Y-m-d'),
                    'isactive'                    => $progression['is_active'],
                    'begin_process'               => in_array($progression['stage'], ['TEST', 'QCR', 'APP']),
                    'description'                 => "Case: {$caseNo} | Offence: {$offence} | Region: {$seizureRegion}",
                    'reason_for_submission'       => $offence,
                    'where_sample_was_obtained'   => "{$seizureRegion} — {$seizureRegion} District",
                    'created_at'                  => $createdAt,
                    'updated_at'                  => $createdAt,
                ]);

                $headers[] = $header;
                $this->command?->info("  ✓ [{$progression['status']}] {$header->batch_code} → {$sampleType->name}");
            }

            // ----------------------------------------------------------------
            // 4. Seed Sample Details (2 exhibits per batch)
            //    Each detail is linked to the correct lab, analysis type,
            //    and the submission request exhibit table.
            // ----------------------------------------------------------------
            $this->command?->info('');
            $this->command?->info('Seeding sample details and exhibit records...');

            $detailCount = 0;
            foreach ($headers as $index => $header) {
                $routing       = $sampleTypeRouting[$header->sample_type_id] ?? [
                    'lab_id'           => $labChm->id,
                    'analysis_type_id' => $analysisTypes->first()?->id,
                ];

                // Pull the best matching analysis type for this lab
                $labAnalysisTypes = $analysisTypes->where('lab_id', $routing['lab_id']);
                $submissionId     = $submissionRequests[$header->batch_code] ?? null;

                $suffixes = ['A', 'B'];
                $sectionIds = [];

                foreach ($suffixes as $sIdx => $suffix) {
                    $sampleCode      = $header->batch_code . '-' . $suffix;
                    $targetAnaType   = $labAnalysisTypes->values()->isNotEmpty()
                        ? $labAnalysisTypes->values()[$sIdx % $labAnalysisTypes->count()]
                        : $analysisTypes->first();

                    $sd = SampleDetails::create([
                        'sample_code'           => $sampleCode,
                        'sample_header_id'      => $header->id,
                        'analysis_type_id'      => $targetAnaType?->id,
                        'lab_id'                => $routing['lab_id'],
                        'crm_unit_id'           => $header->crm_unit_id,
                        'has_no_result_capture' => false,
                        'is_ammendment'         => false,
                        'sample_no'             => 'SMP-' . str_pad($index * 2 + $sIdx + 1, 6, '0', STR_PAD_LEFT),
                        'quantity'              => ($sIdx === 0 ? '250g' : '100ml'),
                        'barcode'               => 'BC-' . strtoupper(Str::random(10)),
                        'short_code'            => 'EXH-' . str_pad($sIdx + 1, 2, '0', STR_PAD_LEFT),
                        'material_status'       => 'Received',
                        'comments'              => "Exhibit {$suffix}: Properly sealed and chain-of-custody verified.",
                        'created_at'            => $header->created_at,
                        'updated_at'            => $header->created_at,
                    ]);

                    // Link exhibit to submission request
                    if ($submissionId) {
                        DB::connection('pgsql')->table('sample_submission_request_exhibits')->insert([
                            'id'                          => (string) Str::uuid(),
                            'sample_submission_request_id'=> $submissionId,
                            'sample_detail_id'            => $sd->id,
                            'serial_number'               => $sIdx + 1,
                            'number_of_items'             => rand(1, 3),
                            'item_description'            => "Exhibit {$suffix} — {$sd->quantity} of suspect material",
                            'suspected_item'              => SampleType::find($header->sample_type_id)?->name ?? 'Unknown substance',
                            'created_at'                  => $header->created_at,
                            'updated_at'                  => $header->created_at,
                        ]);
                    }

                    // Collect stage IDs for lab_section_ids on the header
                    $sec = SampleAnalysisStage::where('lab_id', $routing['lab_id'])->first();
                    if ($sec) {
                        $sectionIds[] = $sec->id;
                    }

                    $detailCount++;
                }

                // Update lab_section_ids on the header
                if (!empty($sectionIds)) {
                    $header->update(['lab_section_ids' => implode(',', array_unique($sectionIds))]);
                }
            }

            $this->command?->info("  ✓ Seeded {$detailCount} sample detail exhibits across " . count($headers) . " batches.");

            // ----------------------------------------------------------------
            // 5. Seed 1 Suspect per case (for DNA and drug cases)
            //    Only for submission requests that involve assault / DNA offences
            // ----------------------------------------------------------------
            $dnaOffences = [
                'Sexual Assault', 'Homicide', 'Armed Robbery',
                'Attempted Murder', 'Armed Robbery with Violence',
            ];

            $suspectCount = 0;
            foreach ($headers as $i => $header) {
                $submissionId = $submissionRequests[$header->batch_code] ?? null;
                if (!$submissionId) continue;

                $offence = $header->reason_for_submission ?? '';
                if (!in_array($offence, $dnaOffences)) continue;

                DB::connection('pgsql')->table('sample_submission_request_suspects')->insert([
                    'id'                          => (string) Str::uuid(),
                    'sample_submission_request_id'=> $submissionId,
                    'serial_number'               => 1,
                    'first_name'                  => 'Unknown',
                    'middle_name'                 => 'Suspect',
                    'last_name'                   => 'No.' . ($i + 1),
                    'sex'                         => $i % 2 === 0 ? 'Male' : 'Female',
                    'nationality'                 => 'Kenyan',
                    'id_passport_number'          => 'NA-' . rand(10000000, 99999999),
                    'created_at'                  => $header->created_at,
                    'updated_at'                  => $header->created_at,
                ]);
                $suspectCount++;
            }

            $this->command?->info("  ✓ Seeded {$suspectCount} suspect records for forensic DNA / assault cases.");

            // ----------------------------------------------------------------
            // 6. Update labs.analyst_ids with the current active users
            //    Phase 6 will overwrite this with dedicated analyst UUIDs.
            // ----------------------------------------------------------------
            $userIds = $users->pluck('id')->values()->all();
            foreach ([$labChm, $labDna, $labTox] as $lab) {
                $lab->update(['analyst_ids' => json_encode($userIds)]);
            }

            $this->command?->info('  ✓ Linked ' . count($userIds) . ' active users as analysts to all 3 lab departments.');

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 2 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
