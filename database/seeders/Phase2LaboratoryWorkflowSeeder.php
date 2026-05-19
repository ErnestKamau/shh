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
     */
    public function run(): void
    {
        // Enforce the correct pgsql connection.
        config(['database.default' => 'pgsql']);

        // Allow mass assignment on all models.
        Model::unguard();

        DB::connection('pgsql')->transaction(function () {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 2 SEEDING: Laboratory Workflows');
            $this->command?->info('====================================================');

            // ----------------------------------------------------------------
            // 1. Retrieve Base Contexts
            // ----------------------------------------------------------------
            $company = Company::first();
            if (!$company) {
                $this->command?->error('Base company not found! Run Phase 1 seeder first.');
                return;
            }

            $activeUser = User::where('active', 1)->first() ?? User::first();
            $activeUserId = $activeUser ? $activeUser->id : null;

            $customers = CRMCustomer::all();
            $sampleTypes = SampleType::all();

            if ($customers->isEmpty() || $sampleTypes->isEmpty()) {
                $this->command?->error('CRM Customers or Sample Types are missing! Run Phase 1 seeder first.');
                return;
            }

            // Retrieve all seeded labs
            $labs = Lab::where('company_id', $company->id)->get();
            $labMap = $labs->pluck('id')->toArray();

            // ----------------------------------------------------------------
            // 2. Seed 5 Workflow Tracking Stages
            // ----------------------------------------------------------------
            $stagesData = [
                ['name' => 'Sample Reception', 'code' => 'REC', 'level' => 1],
                ['name' => 'Sample Preparation', 'code' => 'PREP', 'level' => 2],
                ['name' => 'Testing & Analysis', 'code' => 'TEST', 'level' => 3],
                ['name' => 'QC Review', 'code' => 'QCR', 'level' => 4],
                ['name' => 'Final Approval', 'code' => 'APP', 'level' => 5],
            ];

            $stages = [];
            foreach ($stagesData as $index => $sd) {
                // Link each stage to a lab round-robin
                $associatedLabId = count($labMap) > 0 ? $labMap[$index % count($labMap)] : null;

                $stage = SampleAnalysisStage::updateOrCreate(
                    ['code' => $sd['code'], 'company_id' => $company->id],
                    [
                        'name'            => $sd['name'],
                        'title'           => $sd['name'],
                        'active'          => true,
                        'level'           => $sd['level'],
                        'is_system'       => true,
                        'is_sample_stage' => true,
                        'lab_id'          => $associatedLabId,
                    ]
                );
                $stages[$sd['code']] = $stage;
                $this->command?->info("Seeded Workflow Stage: {$stage->name} (Level: {$stage->level})");
            }

            // ----------------------------------------------------------------
            // 3. Seed 50 Sample Headers (Batches)
            // ----------------------------------------------------------------
            $statuses = ['Draft', 'In Lab', 'Pending Review', 'Approved'];

            $headers = [];
            for ($i = 1; $i <= 50; $i++) {
                $batchCode = 'GCLA-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT);

                // Select context values
                $customer = $customers->random();
                $sampleType = $sampleTypes->random();

                // Find customer unit if exists
                $unit = CRMCompanyUnit::where('crm_customer_id', $customer->id)->first();
                $unitId = $unit ? $unit->id : null;
                $unitName = $unit ? $unit->name : 'Nairobi HQ Site';

                // Determine workflow status & stage
                $status = $statuses[$i % count($statuses)];
                $stageCode = 'REC';
                if ($status === 'In Lab') {
                    $stageCode = 'TEST';
                } elseif ($status === 'Pending Review') {
                    $stageCode = 'QCR';
                } elseif ($status === 'Approved') {
                    $stageCode = 'APP';
                }
                $stage = $stages[$stageCode];

                // Timeline logic
                $daysAgo = 60 - $i; // Spreads timeline back 60 days
                $createdAt = now()->subDays($daysAgo);
                $receiptDate = (clone $createdAt)->addHours(12);

                $processingDate = null;
                $approvalDate = null;

                if (in_array($status, ['In Lab', 'Pending Review', 'Approved'])) {
                    $processingDate = (clone $receiptDate)->addDays(1);
                }
                if ($status === 'Approved') {
                    $approvalDate = (clone $processingDate)->addDays(2);
                }

                $header = SampleHeader::create([
                    'batch_code'              => $batchCode,
                    'crm_customer_id'         => $customer->id,
                    'crm_unit_id'             => $unitId,
                    'crm_unit_name'           => $unitName,
                    'sample_type_id'          => $sampleType->id,
                    'status'                  => $status,
                    'sample_tracking_stage'   => $stage->id,
                    'has_method_deviation'    => false,
                    'sample_detail_processed' => true,
                    'is_routine'              => false,
                    'routine_frequency'       => 0.0,
                    'priority'                => $i % 10 === 0 ? 'High' : 'Normal',
                    'receiving_officer'       => $activeUserId,
                    'sampling_officer'        => $activeUserId,
                    'specialist_analyst_id'   => $activeUserId,
                    'verify_user_id'          => $status === 'Approved' || $status === 'Pending Review' ? $activeUserId : null,
                    'approve_user_id'         => $status === 'Approved' ? $activeUserId : null,
                    'date_collected'          => $createdAt,
                    'receipt_date'            => $receiptDate,
                    'processing_date'         => $processingDate?->format('Y-m-d'),
                    'approval_date'           => $approvalDate?->format('Y-m-d'),
                    'date_expected'           => (clone $receiptDate)->addDays(5)->format('Y-m-d'),
                    'created_at'              => $createdAt,
                    'updated_at'              => $createdAt,
                    'lab_id'                  => $stage->lab_id,
                ]);

                $headers[] = $header;
                $this->command?->info("Seeded Sample Batch: {$header->batch_code} (Status: {$header->status})");
            }

            // ----------------------------------------------------------------
            // 4. Seed 100 Sample Details
            // ----------------------------------------------------------------
            foreach ($headers as $header) {
                // Find all analysis types bound to this sample type
                $analysisTypes = AnalysisType::where('sample_type_id', $header->sample_type_id)->get();
                if ($analysisTypes->isEmpty()) {
                    $analysisTypes = AnalysisType::all();
                }

                // Create exactly 2 sample details for each header
                $suffixes = ['A', 'B'];
                foreach ($suffixes as $index => $suffix) {
                    $sampleCode = $header->batch_code . '-' . $suffix;
                    $targetAnalysisType = $analysisTypes->random();

                    SampleDetails::create([
                        'sample_code'           => $sampleCode,
                        'sample_header_id'      => $header->id,
                        'analysis_type_id'      => $targetAnalysisType->id,
                        'lab_id'                => $targetAnalysisType->lab_id ?? $header->lab_id,
                        'crm_unit_id'           => $header->crm_unit_id,
                        'has_no_result_capture' => false,
                        'is_ammendment'         => false,
                        'sample_no'             => 'SMP-' . rand(100000, 999999),
                        'quantity'              => '250ml / 500g',
                        'barcode'               => 'BC-' . rand(1000000000, 9999999999),
                        'comments'              => 'Prinstine sample ingestion, correct temperature seal.',
                    ]);
                }
                $this->command?->info("Seeded 2 Sample Details for Batch: {$header->batch_code}");
            }

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 2 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
