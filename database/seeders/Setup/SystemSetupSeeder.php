<?php

namespace Database\Seeders\Setup;

use App\Company;
use App\InventoryDepartment;
use App\InventoryLocation;
use App\InventoryLocationUser;
use App\ModulePreConfigs;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

class SystemSetupSeeder extends Seeder
{
    public function run(): void
    {
        // Target the pgsql database for all model writes.
        config(['database.default' => 'pgsql']);

        // Flush Spatie's cached permissions so the new connection is used.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Allow mass-assignment across all models in this seeder.
        Model::unguard();

        try {
            $sourceConnection = config('imara_ai.source_connection', config('database.default'));

            DB::connection('pgsql')->transaction(function () use ($sourceConnection) {
                // ----------------------------------------------------------------
                // STEP 1 — Roles & Permissions (Spatie)
                // ----------------------------------------------------------------
                $this->command?->info('Step 1: Creating Spatie roles and permissions...');

                $permissionNames = [
                    // Users
                    'view users',
                    'create users',
                    'edit users',
                    'delete users',
                    // Roles
                    'view roles',
                    'create roles',
                    'edit roles',
                    'delete roles',
                    // Inventory
                    'view inventory',
                    'create inventory',
                    'edit inventory',
                    'delete inventory',
                    // Configurations
                    'view configurations',
                    'create configurations',
                    'edit configurations',
                    'delete configurations',
                    // Companies
                    'view companies',
                    'create companies',
                    'edit companies',
                    'delete companies',
                    // Module access (controls home dashboard visibility)
                    'laboratory.module.access',
                    'inventory.module.access',
                    'equipment.module.access',
                    'crm.module.access',
                    'personnel.module.access',
                    'dms.module.access',
                    'calendar.module.access',
                    'matrix.module.access',
                    'ai.module.access',
                    'ai_analytics.module.access',
                    'risk.module.access',
                    'registry.module.access',
                    'audit.module.access',
                    'tickets.module.access',
                    'settings.module.access',
                ];

                $permissions = [];
                foreach ($permissionNames as $name) {
                    $permissions[] = Permission::firstOrCreate([
                        'name'       => $name,
                        'guard_name' => 'web',
                    ]);
                }

                $adminRole = Role::firstOrCreate([
                    'name'       => 'admin',
                    'guard_name' => 'web',
                ]);

                $adminRole->syncPermissions($permissions);

                $this->command?->info("  Created role '{$adminRole->name}' with " . count($permissions) . ' permissions.');

                // ----------------------------------------------------------------
                // STEP 2 — System Configuration Types
                // ----------------------------------------------------------------
                $this->command?->info("Step 2: Loading system_configuration_types from {$sourceConnection}...");

                /** @var array<string, string> $typeIdMap  old_id => new_id */
                $typeIdMap = [];

                $sourceTypes = DB::connection($sourceConnection)
                    ->table('system_configuration_types')
                    ->get();

                foreach ($sourceTypes as $row) {
                    $type = SystemConfigurationsType::updateOrCreate(
                        ['configuration_type' => $row->configuration_type],
                        [
                            'description' => $row->description ?? '',
                            'status'      => (bool) ($row->status ?? false),
                        ]
                    );

                    $typeIdMap[$row->id] = $type->id;
                }

                $this->command?->info('  Migrated ' . count($typeIdMap) . ' configuration type(s).');

                // ----------------------------------------------------------------
                // STEP 3 — System Configurations
                // ----------------------------------------------------------------
                $this->command?->info("Step 3: Loading system_configurations from {$sourceConnection}...");

                $sourceConfigs = DB::connection($sourceConnection)
                    ->table('system_configurations')
                    ->get();

                $migratedCount = 0;
                $skippedCount  = 0;

                foreach ($sourceConfigs as $row) {
                    // Resolve the new configuration_type_id from the map built above.
                    $newTypeId = isset($row->configuration_type_id)
                        ? ($typeIdMap[$row->configuration_type_id] ?? null)
                        : null;

                    if ($row->configuration_type_id !== null && $newTypeId === null) {
                        Log::warning('SystemSetupSeeder: skipping config row — type not found', [
                            'key'                    => $row->key,
                            'configuration_type_id'  => $row->configuration_type_id,
                        ]);
                        $skippedCount++;
                        continue;
                    }

                    SystemConfiguration::updateOrCreate(
                        ['key' => $row->key],
                        [
                            'value'                 => $row->value ?? '',
                            'configuration_type_id' => $newTypeId,
                            'status'                => (bool) ($row->status ?? false),
                        ]
                    );

                    $migratedCount++;
                }

                $this->command?->info("  Migrated {$migratedCount} configuration(s), skipped {$skippedCount}.");

                // ----------------------------------------------------------------
                // STEP 4 — Migrate All ModulePreConfigs
                // ----------------------------------------------------------------
                $this->command?->info("Step 4: Loading module_pre_configs from {$sourceConnection}...");

                $sourceMpc = DB::connection($sourceConnection)
                    ->table('module_pre_configs')
                    ->get();

                $mpcCount = 0;
                foreach ($sourceMpc as $row) {
                    ModulePreConfigs::updateOrCreate(
                        ['name' => $row->name, 'type' => $row->type],
                        [
                            'description'           => $row->description ?? null,
                            'level'                 => $row->level ?? null,
                            'color'                 => $row->color ?? null,
                            'active'                => isset($row->active) ? (bool) $row->active : true,
                            'module'                => $row->module ?? null,
                            'inventory_location_id' => null,
                            'zoho_id'               => $row->zoho_id ?? null,
                            'code'                  => $row->code ?? null,
                        ]
                    );
                    $mpcCount++;
                }

                $this->command?->info("  Migrated {$mpcCount} module_pre_configs row(s).");

                // ----------------------------------------------------------------
                // STEP 5 — Ensure Tanzania country exists, then create Company: GCLA
                // ----------------------------------------------------------------
                $this->command?->info('Step 5: Creating company GCLA...');

                $tanzaniaRecord = \App\Country::where('name', 'like', '%Tanzania%')->first();

                if (! $tanzaniaRecord) {
                    $sourceTanzania = DB::connection($sourceConnection)
                        ->table('countries')
                        ->where('name', 'like', '%Tanzania%')
                        ->first();

                    if ($sourceTanzania) {
                        $tanzaniaRecord = \App\Country::firstOrCreate(
                            ['name' => $sourceTanzania->name],
                            [
                                'iso_code_2'       => $sourceTanzania->iso_code_2 ?? 'TZ',
                                'iso_code_3'       => $sourceTanzania->iso_code_3 ?? 'TZA',
                                'address_format'   => $sourceTanzania->address_format ?? '{firstname} {lastname}',
                                'postcode_required' => $sourceTanzania->postcode_required ?? 0,
                                'status'           => $sourceTanzania->status ?? 1,
                            ]
                        );
                    } else {
                        $tanzaniaRecord = \App\Country::firstOrCreate(
                            ['name' => 'Tanzania'],
                            [
                                'iso_code_2'       => 'TZ',
                                'iso_code_3'       => 'TZA',
                                'address_format'   => '{firstname} {lastname}',
                                'postcode_required' => 0,
                                'status'           => 1,
                            ]
                        );
                    }
                }

                $countryId = $tanzaniaRecord->id;

                $company = Company::updateOrCreate(
                    ['id' => '019dde3f-07d3-73d0-a0f2-a01ac58346b4'],
                    [
                        'name'           => 'Government Chemist Laboratory Authority',
                        'logo'           => '/images/no-logo.png',
                        'report_logo'    => null,
                        'location'       => 'Dar es Salaam, Tanzania',
                        'address'        => 'P.O. Box 164, Dar es Salaam',
                        'country_id'     => $countryId,
                        'website'        => 'https://www.gcla.go.tz',
                        'email'          => 'gcla@gcla.go.tz',
                        'cell_phone'     => '+255222113383',
                        'telephone'      => '+255222113384',
                        'street'         => 'Luthuli Street',
                        'active'         => true,
                        'show_on_reports' => true,
                    ]
                );

                $this->command?->info("  Company '{$company->name}' ready (id: {$company->id}).");

                // ----------------------------------------------------------------
                // STEP 6 — Inventory Locations: GCLA HQ + SystemAdmin
                // ----------------------------------------------------------------
                $this->command?->info('Step 6: Creating inventory locations...');

                $location = InventoryLocation::firstOrCreate(
                    ['id' => '019dde3f-07d9-73bf-86f3-d4fd6df2eece'],
                    [
                        'name'       => 'GCLA HQ',
                        'company_id' => $company->id,
                        'level'      => 1,
                        'active'     => 1,
                    ]
                );

                $adminLocation = InventoryLocation::firstOrCreate(
                    ['id' => '019dde3f-07dd-739c-9062-165dfd447c1f'],
                    [
                        'name'       => 'SystemAdmin',
                        'company_id' => $company->id,
                        'level'      => 1,
                        'active'     => 1,
                    ]
                );

                $this->command?->info("  Location '{$location->name}' ready (id: {$location->id}).");
                $this->command?->info("  Location '{$adminLocation->name}' ready (id: {$adminLocation->id}).");

                // ----------------------------------------------------------------
                // STEP 7 — Inventory Departments
                // ----------------------------------------------------------------
                $this->command?->info('Step 7: Seeding inventory departments...');

                $departmentNames = ['Procurement', 'Warehouse', 'Quality Control'];

                foreach ($departmentNames as $deptName) {
                    InventoryDepartment::firstOrCreate(
                        ['name' => $deptName, 'company_id' => $company->id],
                        ['module' => 'inventory', 'active' => 1, 'location_id' => $location->id]
                    );
                }

                $adminDept = InventoryDepartment::firstOrCreate(
                    ['name' => 'SystemAdmin', 'company_id' => $company->id],
                    ['module' => 'system', 'active' => 1, 'location_id' => $adminLocation->id]
                );

                $this->command?->info('  Departments: ' . implode(', ', array_merge($departmentNames, ['SystemAdmin'])));

                // ----------------------------------------------------------------
                // STEP 8 — Designations via ModulePreConfigs
                // ----------------------------------------------------------------
                $this->command?->info('Step 8: Seeding designations (ModulePreConfigs)...');

                $designationNames = ['Manager', 'Officer', 'Supervisor', 'System Administrator'];

                foreach ($designationNames as $designation) {
                    ModulePreConfigs::firstOrCreate(
                        ['name' => $designation, 'type' => 'Job Description']
                    );
                }

                $this->command?->info('  Designations: ' . implode(', ', $designationNames));

                // ----------------------------------------------------------------
                // STEP 9 — Default Admin User
                // ----------------------------------------------------------------
                $this->command?->info('Step 9: Creating default admin user...');

                $adminDeptUuid = InventoryDepartment::where('name', 'SystemAdmin')
                    ->where('company_id', $company->id)
                    ->value('id');

                $adminLocationUuid = InventoryLocation::where('name', 'SystemAdmin')
                    ->where('company_id', $company->id)
                    ->value('id');

                $this->command?->info("  Using dept UUID     : {$adminDeptUuid}");
                $this->command?->info("  Using location UUID : {$adminLocationUuid}");

                $user = User::updateOrCreate(
                    ['email' => 'dannyagah13@gmail.com'],
                    [
                        'name'          => 'Danny Agah',
                        'password'      => Hash::make('Admin@2026!'),
                        'company_id'    => $company->id,
                        'department_id' => $adminDeptUuid,
                        'location_id'   => $adminLocationUuid,
                        'active'        => 1,
                        'first_name'    => 'Danny',
                        'last_name'     => 'Agah',
                    ]
                );

                $this->command?->info("  User '{$user->email}' ready (id: {$user->id}).");

                // ----------------------------------------------------------------
                // STEP 10 — Link user to inventory location
                // ----------------------------------------------------------------
                $this->command?->info('Step 10: Linking user to inventory location...');

                InventoryLocationUser::firstOrCreate(
                    [
                        'user_id'               => $user->id,
                        'inventory_location_id' => $adminLocation->id,
                    ]
                );

                $this->command?->info("  User linked to location '{$adminLocation->name}'.");

                // ----------------------------------------------------------------
                // STEP 11 — Assign admin role to the default user
                // ----------------------------------------------------------------
                $this->command?->info('Step 11: Assigning admin role to default user...');

                $user->assignRole('admin');

                $this->command?->info("  Role 'admin' assigned to '{$user->email}'.");
            });

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->command?->info('SystemSetupSeeder completed successfully.');
        } finally {
            Model::reguard();
        }
    }
}
