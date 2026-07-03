<?php

namespace Database\Seeders\Concerns;

use App\Country;

class AmSpecSeedData
{
    public const DUBAI_COMPANY_ID = '019dde3f-07d3-73d0-a0f2-a01ac58346b4';

    public const BRAZIL_COMPANY_ID = '019dde3f-07d3-73d0-a0f2-a01ac58346b5';

    public const DUBAI_HQ_LOCATION_ID = '019dde3f-07d9-73bf-86f3-d4fd6df2eece';

    public const REFERENCE_PREFIX = 'AMSPEC';

    public const LAB_EMAIL_DOMAIN = 'amspecgroup.com';

    public const PERSONNEL_EMAIL_DOMAIN = 'amspec-labs.com';

    public const SEED_USER_EMAIL = '1.kamauernest@gmail.com';

    public static function seedUserEmail(?string $suffix = null): string
    {
        if ($suffix === null || $suffix === '') {
            return self::SEED_USER_EMAIL;
        }

        [$local, $domain] = explode('@', self::SEED_USER_EMAIL, 2);

        return "{$local}+{$suffix}@{$domain}";
    }

    /**
     * @return array<string, string>
     */
    public static function legacyCompanyNames(): array
    {
        return [
            'Government Chemist Laboratory Authority',
            'GCLA Authority HQ',
        ];
    }

    /**
     * @return list<string>
     */
    public static function legacyCrmCustomerNames(): array
    {
        return [
            'Tanzania Police Force',
            'Directorate of Criminal Investigations',
            'High Court of Tanzania',
            'Drug Control and Enforcement Authority',
            'Civilian Evidence Submission Desk',
            'Tanzania Private Industries Consortium',
            'Dar es Salaam Port Health Authority',
            'Tanzania Advocates Forensic Liaison Group',
            'National Research Institutions Forum',
            'Ministry of Health Tanzania',
        ];
    }

    /**
     * @return list<string>
     */
    public static function legacyCrmCustomerCodes(): array
    {
        return [
            'INT-POL-001',
            'INT-DCI-002',
            'INT-CRT-003',
            'INT-DCEA-004',
            'EXT-CIV-001',
            'EXT-IND-002',
            'EXT-PRT-003',
            'EXT-ADV-004',
            'EXT-RES-005',
            'CUST-TPF',
            'CUST-MOH',
        ];
    }

    public static function resolveUaeCountry(): ?Country
    {
        return Country::query()
            ->where('iso_code_2', 'AE')
            ->orWhere('name', 'like', '%United Arab Emirates%')
            ->orWhere('name', 'like', '%UAE%')
            ->first();
    }

    public static function resolveBrazilCountry(): ?Country
    {
        return Country::query()
            ->where('iso_code_2', 'BR')
            ->orWhere('name', 'like', '%Brazil%')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public static function dubaiCompanyAttributes(string $countryId): array
    {
        return [
            'name' => 'AmSpec Middle East Inspection & Testing Services',
            'logo' => '/images/no-logo.png',
            'report_logo' => null,
            'location' => 'Dubai, United Arab Emirates',
            'address' => '3801 U-bora office tower, Marasi Drive, Business Bay, Dubai',
            'country_id' => $countryId,
            'website' => 'https://www.amspecgroup.com',
            'email' => 'info@amspecgroup.com',
            'cell_phone' => '+971 4 323 0399',
            'telephone' => '+971 4 323 0399',
            'street' => 'Marasi Drive, Business Bay',
            'active' => true,
            'show_on_reports' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function brazilCompanyAttributes(string $countryId): array
    {
        return [
            'name' => 'AmSpec Rio Crude Oil Center',
            'logo' => '/images/no-logo.png',
            'report_logo' => null,
            'location' => 'Rio de Janeiro, Brazil',
            'address' => 'Rio de Janeiro, Brazil',
            'country_id' => $countryId,
            'website' => 'https://www.amspecgroup.com',
            'email' => 'brazilcrude@amspecgroup.com',
            'cell_phone' => '+55 21 2116 4695',
            'telephone' => '+55 21 2116 4695',
            'street' => null,
            'active' => false,
            'show_on_reports' => false,
        ];
    }

    /** @var array<string, array{name: string, office: string, gps: string, regions: list<string>}> */
    public const ZONE_LOCATIONS = [
            'CZO' => [
                'name' => 'Dubai HQ Zone',
                'office' => 'Business Bay, Marasi Drive',
                'gps' => '25.1867, 55.2667',
                'regions' => ['Dubai', 'Business Bay', 'Downtown Dubai'],
            ],
            'EZO' => [
                'name' => 'Dubai Metro Zone',
                'office' => 'Sheikh Zayed Road',
                'gps' => '25.2048, 55.2708',
                'regions' => ['Dubai Marina', 'JLT', 'Al Barsha'],
            ],
            'LZO' => [
                'name' => 'Northern Emirates Zone',
                'office' => 'Sharjah Industrial Area',
                'gps' => '25.3463, 55.4209',
                'regions' => ['Sharjah', 'Ajman', 'Umm Al Quwain'],
            ],
            'NZO' => [
                'name' => 'Abu Dhabi Zone',
                'office' => 'Khalifa Industrial Zone',
                'gps' => '24.4539, 54.3773',
                'regions' => ['Abu Dhabi', 'Mussafah', 'KIZAD'],
            ],
            'SZO' => [
                'name' => 'Western Region Zone',
                'office' => 'Al Dhafra',
                'gps' => '23.6577, 53.7223',
                'regions' => ['Al Dhafra', 'Madinat Zayed', 'Liwa'],
            ],
            'SHZO' => [
                'name' => 'Eastern Coast Zone',
                'office' => 'Fujairah Port',
                'gps' => '25.1288, 56.3265',
                'regions' => ['Fujairah', 'Kalba', 'Khor Fakkan'],
            ],
        ];

    /** @var array<string, string> */
    public const ZONE_NAMES = [
        'CZO' => 'Dubai HQ Zone',
        'EZO' => 'Dubai Metro Zone',
        'LZO' => 'Northern Emirates Zone',
        'NZO' => 'Abu Dhabi Zone',
        'SZO' => 'Western Region Zone',
        'SHZO' => 'Eastern Coast Zone',
    ];

    /**
     * @return array<string, array{name: string, office: string, gps: string, regions: list<string>}>
     */
    public static function zoneLocations(): array
    {
        return self::ZONE_LOCATIONS;
    }

    /**
     * @return array<string, string>
     */
    public static function zoneNames(): array
    {
        return self::ZONE_NAMES;
    }

    /**
     * @return list<array{name: string, code: string, type: string, internal: bool, postal_address: string, physical_address: string, contacts: list<array{first_name: string, last_name: string, job: string}>}>
     */
    public static function crmCustomers(): array
    {
        return [
            [
                'name' => 'ADNOC Group',
                'code' => 'INT-ENRG-001',
                'type' => 'Energy',
                'internal' => true,
                'postal_address' => 'P.O. Box 898, Abu Dhabi, UAE',
                'physical_address' => 'ADNOC Headquarters, Corniche Road, Abu Dhabi',
                'contacts' => [
                    ['first_name' => 'Khalid', 'last_name' => 'Al-Mansoori', 'job' => 'Quality Manager'],
                    ['first_name' => 'Sarah', 'last_name' => 'Mitchell', 'job' => 'Lab Coordinator'],
                ],
            ],
            [
                'name' => 'DP World UAE',
                'code' => 'INT-MAR-001',
                'type' => 'Marine',
                'internal' => true,
                'postal_address' => 'P.O. Box 17000, Dubai, UAE',
                'physical_address' => 'Jebel Ali Port, Dubai',
                'contacts' => [
                    ['first_name' => 'Omar', 'last_name' => 'Hassan', 'job' => 'Cargo Superintendent'],
                    ['first_name' => 'Elena', 'last_name' => 'Vasquez', 'job' => 'Operations Liaison'],
                ],
            ],
            [
                'name' => 'Emirates National Oil Company (ENOC)',
                'code' => 'EXT-ENRG-001',
                'type' => 'Energy',
                'internal' => false,
                'postal_address' => 'P.O. Box 6692, Dubai, UAE',
                'physical_address' => 'ENOC Building, Sheikh Zayed Road, Dubai',
                'contacts' => [
                    ['first_name' => 'James', 'last_name' => 'Whitfield', 'job' => 'Fuels Quality Manager'],
                    ['first_name' => 'Fatima', 'last_name' => 'Al-Zahra', 'job' => 'Supply Chain Coordinator'],
                ],
            ],
            [
                'name' => 'Shell Trading Middle East',
                'code' => 'EXT-ENRG-002',
                'type' => 'Energy',
                'internal' => false,
                'postal_address' => 'P.O. Box 4650, Dubai, UAE',
                'physical_address' => 'Dubai International Financial Centre',
                'contacts' => [
                    ['first_name' => 'Michael', 'last_name' => 'Okafor', 'job' => 'Trading Operations Manager'],
                    ['first_name' => 'Priya', 'last_name' => 'Sharma', 'job' => 'Inspection Coordinator'],
                ],
            ],
            [
                'name' => 'Petrobras International',
                'code' => 'EXT-ENRG-003',
                'type' => 'Energy',
                'internal' => false,
                'postal_address' => 'Dubai Multi Commodities Centre, Dubai, UAE',
                'physical_address' => 'Jumeirah Lakes Towers, Cluster Y, Dubai',
                'contacts' => [
                    ['first_name' => 'Ricardo', 'last_name' => 'Silva', 'job' => 'Crude Oil Specialist'],
                    ['first_name' => 'Anna', 'last_name' => 'Kowalski', 'job' => 'Commercial Manager'],
                ],
            ],
            [
                'name' => 'Al Dahra Agriculture LLC',
                'code' => 'EXT-AGRI-001',
                'type' => 'Agriculture',
                'internal' => false,
                'postal_address' => 'P.O. Box 128822, Dubai, UAE',
                'physical_address' => 'Khalifa City, Abu Dhabi',
                'contacts' => [
                    ['first_name' => 'Hassan', 'last_name' => 'Ibrahim', 'job' => 'Agri Quality Manager'],
                    ['first_name' => 'Laura', 'last_name' => 'Bennett', 'job' => 'Food Safety Coordinator'],
                ],
            ],
            [
                'name' => 'Gulftainer Company',
                'code' => 'EXT-MAR-001',
                'type' => 'Marine',
                'internal' => false,
                'postal_address' => 'P.O. Box 17000, Sharjah, UAE',
                'physical_address' => 'Khorfakkan Container Terminal, Sharjah',
                'contacts' => [
                    ['first_name' => 'David', 'last_name' => 'Chen', 'job' => 'Terminal Operations Manager'],
                    ['first_name' => 'Aisha', 'last_name' => 'Rahman', 'job' => 'Bunker Fuel Coordinator'],
                ],
            ],
            [
                'name' => 'BASF Middle East LLC',
                'code' => 'EXT-CHEM-001',
                'type' => 'Chemicals',
                'internal' => false,
                'postal_address' => 'P.O. Box 37672, Dubai, UAE',
                'physical_address' => 'Jebel Ali Free Zone, Dubai',
                'contacts' => [
                    ['first_name' => 'Thomas', 'last_name' => 'Mueller', 'job' => 'Chemical Compliance Manager'],
                    ['first_name' => 'Nadia', 'last_name' => 'Farouk', 'job' => 'Technical Liaison'],
                ],
            ],
            [
                'name' => 'Maersk Oil Trading',
                'code' => 'EXT-MAR-002',
                'type' => 'Marine',
                'internal' => false,
                'postal_address' => 'Dubai Maritime City, Dubai, UAE',
                'physical_address' => 'Dubai Maritime City, Dubai',
                'contacts' => [
                    ['first_name' => 'Henrik', 'last_name' => 'Larsen', 'job' => 'Marine Fuels Manager'],
                    ['first_name' => 'Zainab', 'last_name' => 'Al-Khalil', 'job' => 'Vessel Operations Coordinator'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{name: string, primary_zone: string}>
     */
    public static function directorates(): array
    {
        return [
            'DIR-ENR' => ['name' => 'Energy Testing', 'primary_zone' => 'CZO'],
            'DIR-AGF' => ['name' => 'Agriculture & Food', 'primary_zone' => 'EZO'],
            'DIR-ENC' => ['name' => 'Environmental & Chemical', 'primary_zone' => 'CZO'],
        ];
    }

    /**
     * @return list<array{code: string, name: string, directorate: string}>
     */
    public static function labTypes(): array
    {
        return [
            ['code' => 'LAB-FUEL', 'name' => 'Fuels & LPG Lab', 'directorate' => 'DIR-ENR'],
            ['code' => 'LAB-CRD', 'name' => 'Crude Oil Lab', 'directorate' => 'DIR-ENR'],
            ['code' => 'LAB-BNK', 'name' => 'Marine Bunker Lab', 'directorate' => 'DIR-ENR'],
            ['code' => 'LAB-AGF', 'name' => 'Agri & Food Lab', 'directorate' => 'DIR-AGF'],
            ['code' => 'LAB-ENV', 'name' => 'Environmental Lab', 'directorate' => 'DIR-ENC'],
            ['code' => 'LAB-CHM', 'name' => 'Chemicals Lab', 'directorate' => 'DIR-ENC'],
            ['code' => 'LAB-TSU', 'name' => 'Technical Service Unit Lab', 'directorate' => 'DIR-ENC'],
        ];
    }

    public static function dubaiPhone(): string
    {
        return '+971 4 '.random_int(2000000, 3999999);
    }

    public static function dubaiMobile(): string
    {
        return '+971 50 '.random_int(1000000, 9999999);
    }
}
