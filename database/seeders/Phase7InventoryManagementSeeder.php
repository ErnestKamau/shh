<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Phase7InventoryManagementSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('====================================================');
        $this->command?->info('STARTING PHASE 7 SEEDING: Inventory Management');
        $this->command?->info('====================================================');

        $this->seedInventoryOperationalData();

        $this->command?->info('====================================================');
        $this->command?->info('PHASE 7 SEEDING COMPLETED SUCCESSFULLY!');
        $this->command?->info('====================================================');
    }

    private function seedInventoryOperationalData(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function () {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING INVENTORY OPERATIONAL SEEDING: Reagent & Supply Inventory');
            $this->command?->info('====================================================');

            $now = now();

            // 1. Fetch Company and Locations
            $company = DB::connection('pgsql')->table('companies')->first();
            if (!$company) {
                $this->command?->error('No company found! Run core seeders first.');
                return;
            }
            $companyId = $company->id;

            $locations = DB::connection('pgsql')->table('inventory_locations')->where('active', 1)->get();
            if ($locations->isEmpty()) {
                $this->command?->error('No active locations found! Run Phase 2 first.');
                return;
            }

            $department = DB::connection('pgsql')->table('inventory_departments')->first();
            if (!$department) {
                $departmentId = Str::uuid()->toString();
                DB::connection('pgsql')->table('inventory_departments')->insert([
                    'id' => $departmentId,
                    'name' => 'Main Laboratory Department',
                    'company_id' => $companyId,
                    'location_id' => (string) $locations->first()->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $departmentId = $department->id;
            }

            // 2. Clear old inventory records to avoid duplicates
            DB::connection('pgsql')->table('inventory_order_items')->delete();
            DB::connection('pgsql')->table('inventory_orders')->delete();
            DB::connection('pgsql')->table('inventory_items')->delete();
            DB::connection('pgsql')->table('inventory_store_slots')->delete();
            DB::connection('pgsql')->table('inventory_stores')->delete();
            DB::connection('pgsql')->table('inventory_sub_categories')->delete();
            DB::connection('pgsql')->table('inventory_categories')->delete();
            DB::connection('pgsql')->table('suppliers')->delete();
            
            $this->command?->info('Cleared existing operational inventory and supplier records.');

            // Loop through each physical location
            foreach ($locations as $loc) {
                $locationId = $loc->id;
                $this->command?->info("Seeding inventory structures for location: {$loc->name}");

                // 2b. Seed Store and Slot per location
                $storeId = Str::uuid()->toString();
                DB::connection('pgsql')->table('inventory_stores')->insert([
                    'id' => $storeId,
                    'name' => "{$loc->name} Main Store",
                    'company_id' => $companyId,
                    'inventory_location_id' => $locationId,
                    'type_of_store' => 'inventory_store',
                    'is_frozen' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $slotId = Str::uuid()->toString();
                DB::connection('pgsql')->table('inventory_store_slots')->insert([
                    'id' => $slotId,
                    'name' => 'Cold Storage Shelf C-1',
                    'inventory_store_id' => $storeId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $this->command?->info("  Seeded Store and Slot for {$loc->name}.");

                // 3. Seed Suppliers per location (Sigma & Fisher)
                $suppliersList = [
                    [
                        'name' => 'Sigma-Aldrich Chemical Ltd - ' . $loc->name,
                        'email' => 'sales@sigmaaldrich.com',
                        'phone' => '+254-20-889922',
                        'building' => 'Chemical Block A',
                        'street' => 'Industrial Area',
                        'town' => $loc->name,
                        'address' => 'P.O. Box 4022',
                    ],
                    [
                        'name' => 'Fisher Scientific Corp - ' . $loc->name,
                        'email' => 'info@fishersci.com',
                        'phone' => '+254-20-443311',
                        'building' => 'Fisher Towers',
                        'street' => 'Commercial Street',
                        'town' => $loc->name,
                        'address' => 'P.O. Box 8023',
                    ]
                ];

                $seededSuppliers = [];
                foreach ($suppliersList as $sup) {
                    $id = Str::uuid()->toString();
                    DB::connection('pgsql')->table('suppliers')->insert([
                        'id' => $id,
                        'name' => $sup['name'],
                        'email' => $sup['email'],
                        'phone' => $sup['phone'],
                        'building' => $sup['building'],
                        'street' => $sup['street'],
                        'town' => $sup['town'],
                        'address' => $sup['address'],
                        'active' => 1,
                        'vat_number' => 'P0512' . rand(1000, 9999) . 'Z',
                        'logo' => '/images/no-logo.png',
                        'company_id' => $companyId,
                        'inventory_location_id' => $locationId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $seededSuppliers[] = $id;
                }

                // 4. Seed Inventory Categories
                $categories = [
                    [
                        'name' => 'Chemical Reagents - ' . $loc->name,
                        'description' => 'Chemical solutions, analytical standards, indicators, and raw solvents used in testing.',
                    ],
                    [
                        'name' => 'Laboratory Consumables - ' . $loc->name,
                        'description' => 'Disposable labware, pipette tips, filtration paper, petri dishes, and micro-tubes.',
                    ]
                ];

                foreach ($categories as $cat) {
                    $catId = Str::uuid()->toString();
                    DB::connection('pgsql')->table('inventory_categories')->insert([
                        'id' => $catId,
                        'name' => $cat['name'],
                        'description' => $cat['description'],
                        'image' => 'no-logo.png',
                        'category_type' => 'normal',
                        'is_lab' => true,
                        'active' => true,
                        'company_id' => $companyId,
                        'inventory_location_id' => $locationId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // 5. Seed Sub-Categories (Item Specifications)
                    if (str_contains($cat['name'], 'Chemical Reagents')) {
                        $subCats = [
                            [
                                'name' => 'Acetonitrile HPLC Grade - ' . $loc->name,
                                'description' => 'HPLC/UV gradient grade mobile phase solvent.',
                                'minimum_level' => 10.0,
                                'unit_type' => 'Liters',
                                'price' => 45.50,
                            ],
                            [
                                'name' => 'Hydrochloric Acid 37% ACS - ' . $loc->name,
                                'description' => 'ACS certified analytical titration reagent.',
                                'minimum_level' => 5.0,
                                'unit_type' => 'Liters',
                                'price' => 22.00,
                            ],
                            [
                                'name' => 'Drift QC Standard Compound - ' . $loc->name,
                                'description' => 'Standard calibration analyte matrix.',
                                'minimum_level' => 15.0,
                                'unit_type' => 'Vials',
                                'price' => 120.00,
                            ]
                        ];
                    } else {
                        $subCats = [
                            [
                                'name' => 'Eppendorf 200uL Pipette Tips - ' . $loc->name,
                                'description' => 'Sterile, DNAse/RNAse-free filtered pipette tips.',
                                'minimum_level' => 20.0,
                                'unit_type' => 'Boxes',
                                'price' => 18.00,
                            ],
                            [
                                'name' => 'Whatman Grade 1 Filter Paper - ' . $loc->name,
                                'description' => 'Qualitative filter paper, 110mm diameter.',
                                'minimum_level' => 8.0,
                                'unit_type' => 'Packs',
                                'price' => 12.50,
                            ]
                        ];
                    }

                    foreach ($subCats as $sub) {
                        $subId = Str::uuid()->toString();
                        DB::connection('pgsql')->table('inventory_sub_categories')->insert([
                            'id' => $subId,
                            'name' => $sub['name'],
                            'description' => $sub['description'],
                            'image' => '',
                            'inventory_category_id' => $catId,
                            'minimum_level' => $sub['minimum_level'],
                            'unit_type' => $sub['unit_type'],
                            'delivery_days' => 7,
                            'internal_lead_time' => 3,
                            'external_lead_time' => 7,
                            'is_lab' => true,
                            'maximum_order_quantity' => 100.0,
                            'working_days' => 365,
                            'active' => 1,
                            'company_id' => $companyId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);

                        // 6. Seed Inventory Items (Actual Physical Batches)
                        // Seed specific volumes: some low stock alerts for titration reagents to trigger warnings
                        $isLowStock = (str_contains($sub['name'], 'Hydrochloric Acid') || str_contains($sub['name'], 'Whatman'));
                        $qtyOnHand = $isLowStock ? ($sub['minimum_level'] - 3.0) : ($sub['minimum_level'] + 25.0);

                        $itemId = Str::uuid()->toString();
                        DB::connection('pgsql')->table('inventory_items')->insert([
                            'id' => $itemId,
                            'inventory_category_id' => $catId,
                            'inventory_sub_category_id' => $subId,
                            'inventory_store_id' => $storeId,
                            'inventory_store_slot_id' => $slotId,
                            'stock_in' => $qtyOnHand,
                            'stock_out' => 0.0,
                            'supplier_id' => $seededSuppliers[rand(0, 1)],
                            'inventory_department_id' => $departmentId,
                            'status' => 'in_inventory',
                            'expiry' => date('Y-m-d', strtotime('+18 months')),
                            'price' => $sub['price'],
                            'batch_code' => 'BATCH-' . rand(1000, 9999),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);

                        // 7. Seed Supply Purchase Orders (Inventory Orders)
                        if ($isLowStock) {
                            $orderId = Str::uuid()->toString();
                            $locCode = strtoupper(substr(str_replace(' ', '', $loc->name), 0, 4));
                            $poNo = 'PO-' . $locCode . '-2026-' . rand(1000, 9999);

                            DB::connection('pgsql')->table('inventory_orders')->insert([
                                'id' => $orderId,
                                'order_number' => $poNo,
                                'supplier_id' => $seededSuppliers[0],
                                'status' => 'not_fulfilled',
                                'company_id' => $companyId,
                                'comments' => "Emergency restock for low level LIMS laboratory items at {$loc->name}.",
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);

                            DB::connection('pgsql')->table('inventory_order_items')->insert([
                                'id' => Str::uuid()->toString(),
                                'inventory_order_id' => $orderId,
                                'inventory_category_id' => $catId,
                                'inventory_sub_category_id' => $subId,
                                'quantity' => 50.0,
                                'fulfilled' => false,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    }
                }
            }

            // Clear cache
            \Illuminate\Support\Facades\Cache::flush();

            $this->command?->info('====================================================');
            $this->command?->info('INVENTORY OPERATIONAL SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}