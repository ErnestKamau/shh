<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ModuleReportsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Resolve active structural identifiers dynamically to preserve constraints
        $company = \DB::table('companies')->first();
        $companyId = $company ? $company->id : (string) Str::uuid();
        if (!$company) {
            \DB::table('companies')->insert([
                'id' => $companyId,
                'name' => 'GCLA Authority HQ',
                'address' => 'Baraza la Mitihani Rd, Dar es Salaam',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $country = \DB::table('countries')->first();
        $countryId = $country ? $country->id : (string) Str::uuid();
        if (!$country) {
            \DB::table('countries')->insert([
                'id' => $countryId,
                'code' => 'TZ',
                'name' => 'Tanzania',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // 2. Clean up previous test seed records for complete idempotency, keeping those in Sample Approval
        $seededHeaderIds = [40001, 40002, 40003, 40004, 40005, 40006];
        $approvingHeaderIds = \DB::table('sample_headers')
            ->whereIn('id', $seededHeaderIds)
            ->where('status', 'Sample Approval')
            ->pluck('id')
            ->toArray();

        $headersToDelete = array_diff($seededHeaderIds, $approvingHeaderIds);

        $detailsToDelete = \DB::table('sample_details')
            ->whereIn('sample_header_id', $headersToDelete)
            ->pluck('id')
            ->toArray();

        $cocsToDelete = \DB::table('chain_of_custodies')
            ->whereIn('sample_header_id', $headersToDelete)
            ->pluck('id')
            ->toArray();

        \DB::table('sample_headers')->whereIn('id', $headersToDelete)->delete();
        \DB::table('sample_details')->whereIn('id', $detailsToDelete)->delete();
        \DB::table('chain_of_custodies')->whereIn('id', $cocsToDelete)->delete();
        \DB::table('request_workflow_forms')->whereIn('sample_header_id', $headersToDelete)->where('form_type', 'sample_rejection')->delete();
        \DB::table('batch_ammendments')->whereIn('batch_id', $headersToDelete)->where('reason', 'like', 'Seeded:%')->delete();
        \DB::table('captured_results')->whereIn('sample_header_id', $headersToDelete)->where('result', 'like', 'Seeded:%')->delete();

        $risksToDelete = [];
        $ncsToDelete = [];
        $auditsToDelete = [];
        for ($i = 0; $i < 6; $i++) {
            $headerId = 40001 + $i;
            if (!in_array($headerId, $approvingHeaderIds)) {
                $risksToDelete[] = 'RSK-SEED-00' . ($i + 1);
                $ncsToDelete[] = 'NC-SEED-00' . ($i + 1);
                $auditsToDelete[] = 'AUD-SEED-00' . ($i + 1);
            }
        }
        
        if (!empty($risksToDelete)) {
            \DB::table('risks')->whereIn('risk_number', $risksToDelete)->delete();
        }
        if (!empty($ncsToDelete)) {
            \DB::table('non_conformances')->whereIn('nc_number', $ncsToDelete)->delete();
        }
        if (!empty($auditsToDelete)) {
            \DB::table('iso_audits')->whereIn('audit_number', $auditsToDelete)->delete();
        }

        // 3. Seed Master & Filterable GCLA LIMS Entities
        // Seed users/analysts/directors
        $users = [
            [
                'id' => 'u1001',
                'name' => 'Dkt. John Doe (Chief Forensic Analyst)',
                'email' => 'john.forensic@gcla.go.tz',
                'password' => bcrypt('password123'),
                'company_id' => $companyId,
                'active' => 1,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 'u1002',
                'name' => 'Prof. Jane Smith (Director of Quality)',
                'email' => 'jane.quality@gcla.go.tz',
                'password' => bcrypt('password123'),
                'company_id' => $companyId,
                'active' => 1,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        foreach ($users as $user) {
            \DB::table('users')->updateOrInsert(['id' => $user['id']], $user);
        }

        // Seed GCLA Laboratories/Sections
        $labs = [
            [
                'id' => 'l10001',
                'name' => 'Forensic Biology Laboratory',
                'code' => 'LAB-FB',
                'company_id' => $companyId,
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 'l10002',
                'name' => 'Toxicology & Drugs Lab',
                'code' => 'LAB-TX',
                'company_id' => $companyId,
                'active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        foreach ($labs as $lab) {
            \DB::table('labs')->updateOrInsert(['id' => $lab['id']], $lab);
        }

        // Seed Customers/Clients
        $customers = [
            [
                'id' => 'c1001',
                'code' => 'CUST-TPF',
                'name' => 'Tanzania Police Force',
                'email' => 'tpf@police.go.tz',
                'telephone1' => '+255222111222',
                'country_id' => $countryId,
                'company_id' => $companyId,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 'c1002',
                'code' => 'CUST-MOH',
                'name' => 'Ministry of Health Tanzania',
                'email' => 'info@moh.go.tz',
                'telephone1' => '+255222333444',
                'country_id' => $countryId,
                'company_id' => $companyId,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        foreach ($customers as $cust) {
            \DB::table('crm_customers')->updateOrInsert(['id' => $cust['id']], $cust);
        }

        // Seed Sample/Analysis Types
        $sampleTypes = [
            [
                'id' => 'st10001',
                'code' => 'ST-DNA',
                'name' => 'Human DNA Specimen',
                'company_id' => $companyId,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 'st10002',
                'code' => 'ST-TOX',
                'name' => 'Toxicological Blood Sample',
                'company_id' => $companyId,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        foreach ($sampleTypes as $st) {
            \DB::table('sample_types')->updateOrInsert(['id' => $st['id']], $st);
        }

        // Seed Analytes (Parameters)
        $analytes = [
            [
                'id' => 'a10001',
                'code' => 'DNA-STR',
                'name' => 'STR Loci Genetic Profile',
                'decimal_places' => 2,
                'method' => 'ABI PCR Amplification',
                'non_detectable' => false,
                'non_accredited' => false,
                'active' => true,
                'company_id' => $companyId,
                'show_on_report' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 'a10002',
                'code' => 'ALC-GC',
                'name' => 'Methanol / Ethanol Concentration',
                'decimal_places' => 3,
                'method' => 'GC-MS Headspace',
                'non_detectable' => false,
                'non_accredited' => false,
                'active' => true,
                'company_id' => $companyId,
                'show_on_report' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        foreach ($analytes as $an) {
            \DB::table('analytes')->updateOrInsert(['id' => $an['id']], $an);
        }

        // Seed Equipment
        $equipments = [
            [
                'id' => 'e10001',
                'name' => 'ABI 3500 DNA Genetic Analyzer',
                'equipment_number' => 'EQ-DNA-3500',
                'description' => 'Forensic DNA Sequencer and Analyzer',
                'make' => 'Thermo Fisher Scientific',
                'model' => 'ABI 3500',
                'date_purchased' => '2024-01-15',
                'maintainance_days' => 180,
                'calibration_days' => 365,
                'company_id' => $companyId,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 'e10002',
                'name' => 'Quantum Wave GC-MS System',
                'equipment_number' => 'EQ-GCMS-03',
                'description' => 'Gas Chromatography-Mass Spectrometer for Drug screens',
                'make' => 'Agilent Technologies',
                'model' => 'Agilent 7890B / 5977B',
                'date_purchased' => '2024-03-20',
                'maintainance_days' => 90,
                'calibration_days' => 180,
                'company_id' => $companyId,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        foreach ($equipments as $eq) {
            \DB::table('equipment')->updateOrInsert(['id' => $eq['id']], $eq);
        }

        // Seed Standards
        $standards = [
            [
                'id' => 's10001',
                'code' => 'STD-E2',
                'name' => 'E2 Weight Reference Standard Set',
                'status' => true,
                'main_standard' => true,
                'is_qc_standard' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 's10002',
                'code' => 'STD-NIST-HM',
                'name' => 'NIST Heavy Metal Calibration Standard',
                'status' => true,
                'main_standard' => true,
                'is_qc_standard' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        foreach ($standards as $std) {
            \DB::table('standards')->updateOrInsert(['id' => $std['id']], $std);
        }

        // 4. Seed Multi-Month Transactional Data
        // Generating 6 distinct batches spanning the last 6 months
        for ($i = 0; $i < 6; $i++) {
            $monthOffset = 5 - $i;
            $targetDate = Carbon::now()->subMonths($monthOffset);
            
            $headerId = 40001 + $i;
            
            // Skip insertion/overwrite if this batch is in Sample Approval
            if (in_array($headerId, $approvingHeaderIds)) {
                continue;
            }
            
            $batchCode = 'GCLA-B-' . $targetDate->format('Y') . '-' . sprintf('%04d', $i + 1);
            
            // Insert Sample Header
            \DB::table('sample_headers')->insert([
                'id' => $headerId,
                'batch_code' => $batchCode,
                'receipt_date' => $targetDate->format('Y-m-d H:i:s'),
                'date_collected' => $targetDate->copy()->subDays(2)->format('Y-m-d'),
                'crm_customer_id' => ($i % 2 == 0) ? 'c1001' : 'c1002',
                'crm_unit_name' => 'GCLA Head Office',
                'sample_type_id' => ($i % 2 == 0) ? 'st10001' : 'st10002',
                'reference_number' => 'REF-' . $targetDate->format('Y') . '-' . sprintf('%03d', $i + 1),
                'status' => ($i % 2 == 0) ? 'Samples In Lab' : 'Reports',
                'priority' => ($i % 2 == 0) ? 'High' : 'Normal',
                'created_at' => $targetDate,
                'updated_at' => $targetDate,
            ]);

            // Insert Sample Details (Disposed and Active)
            $detailId1 = 40030 + ($i * 2);
            $detailId2 = 40030 + ($i * 2) + 1;
            
            $sampleCode1 = 'SMP-' . $targetDate->format('Y') . '-' . sprintf('%04d', ($i * 2) + 1);
            $sampleCode2 = 'SMP-' . $targetDate->format('Y') . '-' . sprintf('%04d', ($i * 2) + 2);

            // Disposed sample (every alternate month has a disposed sample)
            $disposalDate = ($i % 2 == 0) ? $targetDate->copy()->addDays(20)->format('Y-m-d') : null;

            \DB::table('sample_details')->insert([
                'id' => $detailId1,
                'sample_code' => $sampleCode1,
                'sample_header_id' => $headerId,
                'analysis_type_id' => ($i % 2 == 0) ? 'st10001' : 'st10002',
                'lab_id' => ($i % 2 == 0) ? 'l10001' : 'l10002',
                'disposal_date' => $disposalDate,
                'created_at' => $targetDate,
                'updated_at' => $targetDate,
            ]);

            \DB::table('sample_details')->insert([
                'id' => $detailId2,
                'sample_code' => $sampleCode2,
                'sample_header_id' => $headerId,
                'analysis_type_id' => ($i % 2 == 0) ? 'st10001' : 'st10002',
                'lab_id' => ($i % 2 == 0) ? 'l10001' : 'l10002',
                'disposal_date' => null,
                'created_at' => $targetDate,
                'updated_at' => $targetDate,
            ]);

            // Insert Chain of Custody
            \DB::table('chain_of_custodies')->insert([
                'id' => $headerId,
                'workflow_stage' => 'Samples Reception',
                'tracking_stage_id' => '10007',
                'moved_in_by' => 'u1001',
                'moved_out_by' => 'u1002',
                'moved_out_date' => $targetDate->copy()->addHours(2)->format('Y-m-d H:i:s'),
                'sample_header_id' => $headerId,
                'comments' => 'Handover complete and secured.',
                'created_at' => $targetDate,
                'updated_at' => $targetDate,
            ]);

            // Seed Request Rejections in Request Workflow Forms
            if ($i % 2 == 0) {
                \DB::table('request_workflow_forms')->insert([
                    'id' => (string) Str::uuid(),
                    'form_type' => 'sample_rejection',
                    'sample_header_id' => $headerId,
                    'batch_code' => $batchCode,
                    'request_reference' => 'REJ-' . $targetDate->format('Y') . '-' . sprintf('%03d', $i + 1),
                    'payload' => json_encode([
                        'rejection_reason' => 'Container seal broken upon transit receipt',
                        'rejected_by' => 'Dkt. John Doe',
                        'comments' => 'Request resampling immediately from source.',
                    ]),
                    'created_by' => 'u1001',
                    'submitted_at' => $targetDate->copy()->addHours(1),
                    'created_at' => $targetDate,
                    'updated_at' => $targetDate,
                ]);
            }

            // Seed Amendments (Batch Re-issues)
            if ($i % 3 == 0) {
                \DB::table('batch_ammendments')->insert([
                    'id' => (string) Str::uuid(),
                    'batch_id' => $headerId,
                    'created_by_id' => 'u1002',
                    'reason' => 'Seeded: Customer requested updated parameter detail inclusion',
                    'samples' => $sampleCode1,
                    'report_url' => '/reports/ammended/' . $headerId,
                    'version_number' => 2,
                    'created_at' => $targetDate->copy()->addDays(5),
                    'updated_at' => $targetDate->copy()->addDays(5),
                ]);
            }

            // Seed Captured Results
            \DB::table('captured_results')->insert([
                'id' => (string) Str::uuid(),
                'has_no_result_capture' => false,
                'sample_detail_code' => $sampleCode1,
                'sample_detail_id' => $detailId1,
                'sample_header_id' => $headerId,
                'analyte_id' => ($i % 2 == 0) ? 'a10001' : 'a10002',
                'analyte_code' => ($i % 2 == 0) ? 'DNA-STR' : 'ALC-GC',
                'equipment_id' => ($i % 2 == 0) ? 'e10001' : 'e10002',
                'result' => 'Seeded: ' . (98.4 + $i) . '% accuracy profile',
                'user_id' => 'u1001',
                'analysis_type_id' => ($i % 2 == 0) ? 'st10001' : 'st10002',
                'operator_id' => 1,
                'machine_update_date' => $targetDate->copy()->addDays(3)->format('Y-m-d H:i:s'),
                'created_at' => $targetDate,
                'updated_at' => $targetDate,
            ]);

            // Seed Risks (Risk module reports)
            \DB::table('risks')->insert([
                'id' => (string) Str::uuid(),
                'risk_number' => 'RSK-SEED-00' . ($i + 1),
                'title' => 'Potential calibration drift on ' . (($i % 2 == 0) ? 'ABI 3500' : 'GC-MS'),
                'description' => 'Uncontrolled temperature fluctuations in Room A',
                'date_identified' => $targetDate->format('Y-m-d'),
                'risk_level' => ($i % 3 == 0) ? 'High' : (($i % 3 == 1) ? 'Medium' : 'Low'),
                'status_name' => ($i % 2 == 0) ? 'Active' : 'Closed',
                'equipment_id' => ($i % 2 == 0) ? 'e10001' : 'e10002',
                'created_at' => $targetDate,
                'updated_at' => $targetDate,
            ]);

            // Seed Non-Conformances (Quality assurance audits)
            \DB::table('non_conformances')->insert([
                'id' => (string) Str::uuid(),
                'nc_number' => 'NC-SEED-00' . ($i + 1),
                'title' => 'Weekly Verification Fail on ' . (($i % 2 == 0) ? 'DNA Genetic Analyzer' : 'GC-MS System'),
                'description' => 'Standard baseline run deviated slightly beyond E2 tolerances',
                'date_identified' => $targetDate->format('Y-m-d'),
                'risk_level_name' => ($i % 2 == 0) ? 'Major' : 'Minor',
                'status_name' => ($i % 2 == 0) ? 'Open' : 'Closed',
                'equipment_id' => ($i % 2 == 0) ? 'e10001' : 'e10002',
                'created_at' => $targetDate,
                'updated_at' => $targetDate,
            ]);

            // Seed ISO Audits
            \DB::table('iso_audits')->insert([
                'id' => (string) Str::uuid(),
                'audit_number' => 'AUD-SEED-00' . ($i + 1),
                'title' => 'ISO 17025 Compliance Audit - ' . $targetDate->format('F Y'),
                'objective' => 'Verify standard laboratory calibration records',
                'scope' => 'All DNA and Chemical analytical columns',
                'scheduled_date' => $targetDate->format('Y-m-d'),
                'status_name' => ($i % 2 == 0) ? 'INPROG' : 'CLOSED',
                'company_id' => $companyId,
                'created_at' => $targetDate,
                'updated_at' => $targetDate,
            ]);
        }
    }
}
