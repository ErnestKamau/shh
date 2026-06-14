<?php

namespace Database\Seeders;

use App\Company;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\SampleType;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestRequestFormDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('active', 1)->first()
            ?? Company::query()->first();

        if (! $company) {
            $company = Company::query()->create([
                'id' => (string) Str::uuid7(),
                'name' => 'AmSpec Middle East Inspection & Testing Services L.L.C',
                'active' => 1,
                'address' => 'Office 101, Building A, Dubai Industrial City',
                'street' => 'Dubai Industrial City',
                'location' => 'Dubai, UAE',
                'telephone' => '+971 4 123 4567',
                'fax' => '+971 4 123 4568',
                'email' => 'info@amspec.test',
                'website' => 'www.amspec.test',
            ]);
        }

        TestRequestForm::seedDefaults();

        $user = User::query()->first();

        $foodType = SampleType::query()
            ->where('code', 'SMP-FOOD')
            ->orWhere('name', 'like', '%Food%')
            ->first();

        $waterType = SampleType::query()
            ->where('code', 'SMP-WTR')
            ->orWhere(function ($q) {
                $q->where('name', 'like', '%Water%')
                    ->where('name', 'not like', '%Waste%');
            })
            ->first();

        $wasteWaterType = SampleType::query()
            ->where('code', 'SMP-WWTR')
            ->orWhere('name', 'like', '%Waste Water%')
            ->first();

        if (! $foodType) {
            $foodType = SampleType::query()->create([
                'id' => (string) Str::uuid7(),
                'name' => 'Food Product Compliance',
                'code' => 'SMP-FOOD',
                'active' => true,
                'company_id' => $company->id,
            ]);
            TestRequestForm::seedDefaults();
        }

        if (! $waterType) {
            $waterType = SampleType::query()->create([
                'id' => (string) Str::uuid7(),
                'name' => 'Water Quality',
                'code' => 'SMP-WTR',
                'active' => true,
                'company_id' => $company->id,
            ]);
            TestRequestForm::seedDefaults();
        }

        $foodTrf = TestRequestForm::query()->where('sample_type_id', $foodType->id)->first();
        $waterTrf = TestRequestForm::query()->where('sample_type_id', $waterType->id)->first();
        $wasteWaterTrf = $wasteWaterType
            ? TestRequestForm::query()->where('sample_type_id', $wasteWaterType->id)->first()
            : null;

        if (! $foodTrf || ! $waterTrf) {
            $this->command?->warn('Test request form templates not found after seedDefaults.');

            return;
        }

        $submissionForm = SubmissionForm::query()->firstOrCreate(
            ['document_code' => 'TRF-DEMO'],
            [
                'id' => (string) Str::uuid7(),
                'name' => 'Test Request Form Demo Submissions',
                'description' => 'Demo submission form for TRF testing',
                'naming_convention_prefix' => 'TRF',
                'naming_convention_format' => '{prefix}{sequence}',
                'is_published' => true,
                'is_active' => true,
                'is_customer_portal_form' => false,
                'form_type' => 'template',
                'placement_mode' => 'button_trigger',
                'display_mode' => 'expanded',
                'target_pages' => [],
                'lims_destination_pages' => ['sample-workflow'],
                'version' => '1.0',
                'issue_date' => now()->toDateString(),
            ]
        );

        $this->seedFoodDemo($submissionForm, $foodTrf, $user);
        $this->seedWaterDemo($submissionForm, $waterTrf, $user);

        if ($wasteWaterType && $wasteWaterTrf) {
            $this->seedWasteWaterDemo($submissionForm, $wasteWaterTrf, $user);
        }

        $this->command?->info('Test Request Form demo instances seeded (Food: 2600001, Water: 2500001).');
    }

    private function seedFoodDemo(SubmissionForm $form, TestRequestForm $trf, ?User $user): void
    {
        $instance = SubmissionFormInstance::query()->updateOrCreate(
            ['form_number' => '2600001'],
            [
                'submission_form_id' => $form->id,
                'title' => 'Food TRF Demo - ABC COMPANY',
                'status' => 'received',
                'submitted_at' => now(),
                'submitted_by' => $user?->id,
                'priority' => 'normal',
            ]
        );

        TestRequestFormInstance::query()->updateOrCreate(
            ['submission_form_instance_id' => $instance->id],
            [
                'test_request_form_id' => $trf->id,
                'status' => 'submitted',
                'created_by' => $user?->id,
                'form_data' => [
                    'job_number' => 'JOB-2026-001',
                    'customer_name' => 'ABC COMPANY',
                    'customer_address' => 'International City, Dubai',
                    'customer_phone' => '+971 50 123 4567',
                    'contact_person' => 'John Smith',
                    'mobile_number' => '+971 55 987 6543',
                    'sampling_date' => '2026-06-03',
                    'sampling_time' => '10:30 AM',
                    'sampling_location' => 'Kitchen Prep Area',
                    'sampling_apparatus' => ['STERILE SWAB', 'OTHERS'],
                    'method_of_sampling' => ['APHA'],
                    'reason_of_collection' => ['CONTRACT'],
                    'transport_condition' => ['CHILLER VEHICLE'],
                    'thermometer_id' => 'AMS/C/INS/116',
                    'statement_of_conformity' => 'YES',
                    'sampled_by' => 'Ahmed Ali / EMP-001',
                    'customer_rep_name' => 'John Smith',
                    'customer_rep_contact' => '+971 55 987 6543',
                    'remarks' => 'Samples collected per contract schedule.',
                    'lab_received_datetime' => '2026-06-03T14:00',
                    'lab_received_by' => 'Lab Receiver',
                    'lab_sample_condition' => 'Acceptable',
                    'sample_rows' => [
                        [
                            'sample_no' => '1',
                            'sample_description' => 'Chicken Salad',
                            'sampling_point' => 'Cold Storage',
                            'qty' => '1',
                            'sample_type' => 'Ready To Eat',
                            'sample_condition' => 'Chilled',
                            'production_date' => '2026-06-01',
                            'expiration_date' => '2026-06-05',
                            'batch_number' => 'CS-001',
                            'sample_temp' => '4',
                            'parameters' => 'Salmonella, E.coli, TVC',
                            'state_of_sample' => 'Semi Solid',
                        ],
                        [
                            'sample_no' => '2',
                            'sample_description' => 'Grilled Salmon',
                            'sampling_point' => 'Hot Kitchen',
                            'qty' => '1',
                            'sample_type' => 'Cooked',
                            'sample_condition' => 'Acceptable',
                            'production_date' => '2026-06-03',
                            'expiration_date' => '2026-06-04',
                            'batch_number' => 'GS-002',
                            'sample_temp' => '65',
                            'parameters' => 'Listeria, TVC',
                            'state_of_sample' => 'Solid',
                        ],
                        [
                            'sample_no' => '3',
                            'sample_description' => 'Hand Swab',
                            'sampling_point' => 'Prep Station',
                            'qty' => '1',
                            'sample_type' => 'Raw',
                            'sample_condition' => 'Acceptable',
                            'production_date' => '',
                            'expiration_date' => '',
                            'batch_number' => '',
                            'sample_temp' => '25',
                            'parameters' => 'TVC, Coliforms',
                            'state_of_sample' => 'Solid',
                        ],
                        [
                            'sample_no' => '4',
                            'sample_description' => 'Surface Swab',
                            'sampling_point' => 'Cutting Board',
                            'qty' => '1',
                            'sample_type' => 'Raw',
                            'sample_condition' => 'Acceptable',
                            'production_date' => '',
                            'expiration_date' => '',
                            'batch_number' => '',
                            'sample_temp' => '24',
                            'parameters' => 'TVC, Salmonella',
                            'state_of_sample' => 'Solid',
                        ],
                        [
                            'sample_no' => '5',
                            'sample_description' => 'Tap Water',
                            'sampling_point' => 'Kitchen Tap',
                            'qty' => '1',
                            'sample_type' => 'Raw',
                            'sample_condition' => 'Ambient',
                            'production_date' => '',
                            'expiration_date' => '',
                            'batch_number' => '',
                            'sample_temp' => '22',
                            'parameters' => 'TVC, Coliforms',
                            'state_of_sample' => 'Liquid',
                        ],
                    ],
                ],
            ]
        );
    }

    private function seedWaterDemo(SubmissionForm $form, TestRequestForm $trf, ?User $user): void
    {
        $instance = SubmissionFormInstance::query()->updateOrCreate(
            ['form_number' => '2500001'],
            [
                'submission_form_id' => $form->id,
                'title' => 'Water TRF Demo',
                'status' => 'received',
                'submitted_at' => now(),
                'submitted_by' => $user?->id,
                'priority' => 'normal',
            ]
        );

        TestRequestFormInstance::query()->updateOrCreate(
            ['submission_form_instance_id' => $instance->id],
            [
                'test_request_form_id' => $trf->id,
                'status' => 'submitted',
                'created_by' => $user?->id,
                'form_data' => [
                    'job_number' => 'JOB-2025-042',
                    'customer_name' => 'ABC COMPANY',
                    'customer_address' => 'International City, Dubai',
                    'customer_phone' => '+971 50 123 4567',
                    'contact_person' => 'Jane Doe',
                    'mobile_number' => '+971 55 987 6543',
                    'sampling_date' => '2025-08-15',
                    'sampling_time' => '09:00 AM',
                    'sampling_location' => 'Building Rooftop',
                    'sampling_apparatus' => ['GRABBER', 'STERILE BOTTLE'],
                    'method_of_sampling' => ['APHA'],
                    'reason_of_collection' => ['CONTRACT'],
                    'transport_condition' => ['CHILLER VEHICLE'],
                    'thermometer_id' => 'AMS/C/INS/116',
                    'statement_of_conformity' => 'As per Contract',
                    'sampled_by' => 'Mohammed Hassan / EMP-002',
                    'customer_rep_name' => 'Jane Doe',
                    'customer_rep_contact' => '+971 55 987 6543',
                    'remarks' => 'Routine water quality monitoring.',
                    'lab_received_datetime' => '2025-08-15T12:30',
                    'lab_received_by' => 'Lab Receiver',
                    'lab_sample_condition' => 'Acceptable',
                    'sample_rows' => [
                        [
                            'sample_no' => '1',
                            'sample_description' => 'Shower Head Water',
                            'location' => 'Floor 3 Bathroom',
                            'qty' => '1',
                            'sampling_point' => 'Shower Head',
                            'ph' => '7.2',
                            'appearance' => 'Clear',
                            'residual_chlorine' => '0.5',
                            'odor' => 'None',
                            'sample_temp' => '28',
                            'microbiology' => true,
                            'legionella' => true,
                            'chemical_analysis' => false,
                        ],
                        [
                            'sample_no' => '2',
                            'sample_description' => 'Tap Hot Water',
                            'location' => 'Kitchen',
                            'qty' => '1',
                            'sampling_point' => 'Tap',
                            'ph' => '7.0',
                            'appearance' => 'Clear',
                            'residual_chlorine' => '0.3',
                            'odor' => 'None',
                            'sample_temp' => '45',
                            'microbiology' => true,
                            'legionella' => false,
                            'chemical_analysis' => false,
                        ],
                        [
                            'sample_no' => '3',
                            'sample_description' => 'Rooftop Tank',
                            'location' => 'Rooftop',
                            'qty' => '1',
                            'sampling_point' => 'Tank',
                            'ph' => '7.4',
                            'appearance' => 'Clear',
                            'residual_chlorine' => '0.8',
                            'odor' => 'None',
                            'sample_temp' => '32',
                            'microbiology' => true,
                            'legionella' => true,
                            'chemical_analysis' => true,
                        ],
                        [
                            'sample_no' => '4',
                            'sample_description' => 'Swimming Pool',
                            'location' => 'Ground Floor',
                            'qty' => '1',
                            'sampling_point' => 'Pool',
                            'ph' => '7.6',
                            'appearance' => 'Clear',
                            'residual_chlorine' => '1.2',
                            'odor' => 'Chlorine',
                            'sample_temp' => '30',
                            'microbiology' => true,
                            'legionella' => false,
                            'chemical_analysis' => true,
                        ],
                    ],
                ],
            ]
        );
    }

    private function seedWasteWaterDemo(SubmissionForm $form, TestRequestForm $trf, ?User $user): void
    {
        $instance = SubmissionFormInstance::query()->updateOrCreate(
            ['form_number' => '2700001'],
            [
                'submission_form_id' => $form->id,
                'title' => 'Waste Water TRF Demo',
                'status' => 'received',
                'submitted_at' => now(),
                'submitted_by' => $user?->id,
                'priority' => 'normal',
            ]
        );

        TestRequestFormInstance::query()->updateOrCreate(
            ['submission_form_instance_id' => $instance->id],
            [
                'test_request_form_id' => $trf->id,
                'status' => 'submitted',
                'created_by' => $user?->id,
                'form_data' => [
                    'job_number' => 'JOB-WW-2026-001',
                    'customer_name' => 'ABC COMPANY',
                    'customer_address' => 'Industrial Zone, Dubai',
                    'customer_phone' => '+971 50 123 4567',
                    'contact_person' => 'Operations Manager',
                    'mobile_number' => '+971 55 987 6543',
                    'sample_number' => 'WW-001',
                    'sampling_date' => '2026-06-10',
                    'sampling_time' => '08:30 AM',
                    'sampling_location' => 'STP Outlet - Building C',
                    'sample_description' => 'Final effluent discharge before marine outfall',
                    'sampling_apparatus' => ['STERILE BOTTLE'],
                    'thermometer_id' => 'AMS/C/INS/136',
                    'ph_meter_id' => 'AMS/C/INS/134',
                    'chlorine_meter_id' => 'AMS/C/INS/079',
                    'method_of_sampling' => ['APHA'],
                    'reason_of_collection' => ['CONTRACT'],
                    'sampling_technique' => ['GRAB'],
                    'sampling_source' => ['STP'],
                    'sample_types_ww' => ['LIQUID'],
                    'transport_condition' => ['CHILLER VEHICLE'],
                    'field_data_quantity' => '2',
                    'field_data_appearance' => 'Turbid',
                    'field_data_color' => 'Grey',
                    'field_data_odor' => 'Mild',
                    'field_data_ph' => '7.1',
                    'field_data_temperature' => '29',
                    'field_data_free_chlorine' => '0.2',
                    'field_data_requirements' => ['MICROBIOLOGY + CHEMISTRY'],
                    'statement_of_conformity' => 'As per Contract',
                    'sampled_by' => 'Field Tech / EMP-003',
                    'customer_rep_name' => 'Operations Manager',
                    'customer_rep_contact' => '+971 55 987 6543',
                    'remarks' => 'Routine STP monitoring sample.',
                    'lab_received_datetime' => '2026-06-10T11:00',
                    'lab_received_by' => 'Lab Receiver',
                    'lab_sample_condition' => 'Acceptable',
                ],
            ]
        );
    }
}
