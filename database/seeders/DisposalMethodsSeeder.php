<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipments\DisposalMethod;

class DisposalMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $methods = [
            [
                'method' => 'scrap',
                'display_name' => 'Scrap',
                'description' => 'Equipment disposed as scrap metal or materials. Suitable for non-hazardous mechanical equipment.',
                'applicable_categories' => ['mechanical', 'non-hazardous'],
                'regulatory_requirements' => [
                    'permits' => [],
                    'certifications' => [],
                    'approvals' => ['Quality Manager'],
                ],
                'required_documentation' => [
                    'Scrap receipt',
                    'Weight certificate',
                ],
                'approved_vendors' => [],
                'safety_requirements' => 'Ensure all fluids are drained and parts are properly dismantled.',
                'environmental_compliance' => 'Must comply with local waste management regulations.',
                'is_active' => true,
            ],
            [
                'method' => 'donation',
                'display_name' => 'Donation',
                'description' => 'Functional equipment donated to educational or charitable organizations.',
                'applicable_categories' => ['mechanical', 'electronic', 'non-hazardous'],
                'regulatory_requirements' => [
                    'permits' => [],
                    'certifications' => [],
                    'approvals' => ['Quality Manager', 'Finance Manager'],
                ],
                'required_documentation' => [
                    'Donation agreement',
                    'Recipient acknowledgment',
                    'Equipment valuation',
                    'Tax receipt (if applicable)',
                ],
                'approved_vendors' => [],
                'safety_requirements' => 'Equipment must be in safe, working condition.',
                'environmental_compliance' => 'N/A',
                'is_active' => true,
            ],
            [
                'method' => 'auction',
                'display_name' => 'Auction',
                'description' => 'High-value equipment sold through public or private auction.',
                'applicable_categories' => ['mechanical', 'electronic', 'high-value'],
                'regulatory_requirements' => [
                    'permits' => [],
                    'certifications' => [],
                    'approvals' => ['Quality Manager', 'Finance Manager'],
                ],
                'required_documentation' => [
                    'Equipment appraisal',
                    'Auction receipt',
                    'Bill of sale',
                ],
                'approved_vendors' => [],
                'safety_requirements' => 'Disclose all safety concerns to buyers.',
                'environmental_compliance' => 'N/A',
                'is_active' => true,
            ],
            [
                'method' => 'recycling',
                'display_name' => 'Recycling',
                'description' => 'E-waste recycling through certified recyclers. For electronic equipment with recoverable materials.',
                'applicable_categories' => ['electronic', 'e-waste'],
                'regulatory_requirements' => [
                    'permits' => ['E-waste handler permit'],
                    'certifications' => ['ISO 14001', 'R2 Certification'],
                    'approvals' => ['Safety Officer', 'Quality Manager'],
                ],
                'required_documentation' => [
                    'Recycling certificate',
                    'Data destruction certificate',
                    'Material recovery report',
                ],
                'approved_vendors' => [],
                'safety_requirements' => 'All data must be wiped. Remove batteries and hazardous components.',
                'environmental_compliance' => 'Must use certified e-waste recycler. Comply with WEEE directive.',
                'is_active' => true,
            ],
            [
                'method' => 'destruction',
                'display_name' => 'Destruction',
                'description' => 'Physical destruction of sensitive equipment. For equipment containing proprietary technology or sensitive data.',
                'applicable_categories' => ['electronic', 'sensitive', 'proprietary'],
                'regulatory_requirements' => [
                    'permits' => [],
                    'certifications' => ['Certified destruction service'],
                    'approvals' => ['Safety Officer', 'Quality Manager', 'Security Manager'],
                ],
                'required_documentation' => [
                    'Certificate of destruction',
                    'Photographic evidence',
                    'Witness statement',
                ],
                'approved_vendors' => [],
                'safety_requirements' => 'Follow secure destruction protocols. Witness required.',
                'environmental_compliance' => 'Destroyed materials must be disposed according to type (metal, plastic, etc.)',
                'is_active' => true,
            ],
            [
                'method' => 'hazardous_disposal',
                'display_name' => 'Hazardous Disposal',
                'description' => 'Disposal of hazardous equipment (chemical, radioactive, biological). Requires licensed hazardous waste handler.',
                'applicable_categories' => ['hazardous', 'chemical', 'radioactive', 'biological'],
                'regulatory_requirements' => [
                    'permits' => ['Hazardous waste transport permit', 'Disposal facility permit'],
                    'certifications' => ['Licensed hazardous waste handler'],
                    'approvals' => ['Safety Officer', 'Quality Manager', 'Environmental Manager'],
                ],
                'required_documentation' => [
                    'Hazardous waste manifest',
                    'Transport documentation',
                    'Disposal certificate from licensed facility',
                    'Environmental impact assessment',
                ],
                'approved_vendors' => [],
                'safety_requirements' => 'Follow hazardous material handling protocols. Use appropriate PPE. Contain all hazardous materials.',
                'environmental_compliance' => 'Must comply with EPA/local environmental regulations. Use licensed hazardous waste facility only.',
                'is_active' => true,
            ],
        ];

        foreach ($methods as $method) {
            DisposalMethod::updateOrCreate(
                ['method' => $method['method']],
                $method
            );
        }

        $this->command->info('Disposal methods seeded successfully.');
    }
}


