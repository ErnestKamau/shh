<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Phase9InventoryManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function () {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 9 SEEDING: Reagent & Supply Inventory');
            $this->command?->info('====================================================');

            $now = now();

            // 1. Fetch Company and Location
            $company = DB::connection('pgsql')->table('companies')->first();
            if (!$company) {
                $this->command?->error('No company found! Run core seeders first.');
                return;
            }
            $companyId = $company->id;

            $location = DB::connection('pgsql')->table('inventory_locations')->first();
            $locationId = $location?->id ?? '019dde3f-07d9-73bf-86f3-d4fd6df2eece';

            $department = DB::connection('pgsql')->table('inventory_departments')->first();
            $departmentId = $department?->id ?? '019dde3f-07e8-7286-a09e-1483410114f6';

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

            // 2b. Seed Store and Slot
            $storeId = Str::uuid()->toString();
            DB::connection('pgsql')->table('inventory_stores')->insert([
                'id' => $storeId,
                'name' => 'Main Reagent Store',
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
            $this->command?->info("Seeded Store 'Main Reagent Store' and Slot 'Cold Storage Shelf C-1'.");

            // 3. Seed Suppliers
            $suppliersList = [
                [
                    'name' => 'Sigma-Aldrich Chemical Ltd',
                    'email' => 'sales@sigmaaldrich.com',
                    'phone' => '+254-20-889922',
                    'building' => 'Chemical Block A',
                    'street' => 'Industrial Area',
                    'town' => 'Nairobi',
                    'address' => 'P.O. Box 4022-00100',
                ],
                [
                    'name' => 'Fisher Scientific Corp',
                    'email' => 'info@fishersci.com',
                    'phone' => '+254-20-443311',
                    'building' => 'Fisher Towers',
                    'street' => 'Commercial Street',
                    'town' => 'Mombasa',
                    'address' => 'P.O. Box 80234-00200',
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
                $this->command?->info("Seeded Supplier: {$sup['name']}");
            }

            // 4. Seed Inventory Categories
            $categories = [
                [
                    'name' => 'Chemical Reagents',
                    'description' => 'Chemical solutions, analytical standards, indicators, and raw solvents used in testing.',
                ],
                [
                    'name' => 'Laboratory Consumables',
                    'description' => 'Disposable labware, pipette tips, filtration paper, petri dishes, and micro-tubes.',
                ]
            ];

            $now = now();
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
                $this->command?->info("Seeded Category: {$cat['name']}");

                // 5. Seed Sub-Categories (Item Specifications)
                if ($cat['name'] === 'Chemical Reagents') {
                    $subCats = [
                        [
                            'name' => 'Acetonitrile HPLC Grade',
                            'description' => 'HPLC/UV gradient grade mobile phase solvent.',
                            'minimum_level' => 10.0,
                            'unit_type' => 'Liters',
                            'price' => 45.50,
                        ],
                        [
                            'name' => 'Hydrochloric Acid 37% ACS',
                            'description' => 'ACS certified analytical titration reagent.',
                            'minimum_level' => 5.0,
                            'unit_type' => 'Liters',
                            'price' => 22.00,
                        ],
                        [
                            'name' => 'Drift QC Standard Compound',
                            'description' => 'Standard calibration analyte matrix.',
                            'minimum_level' => 15.0,
                            'unit_type' => 'Vials',
                            'price' => 120.00,
                        ]
                    ];
                } else {
                    $subCats = [
                        [
                            'name' => 'Eppendorf 200uL Pipette Tips',
                            'description' => 'Sterile, DNAse/RNAse-free filtered pipette tips.',
                            'minimum_level' => 20.0,
                            'unit_type' => 'Boxes',
                            'price' => 18.00,
                        ],
                        [
                            'name' => 'Whatman Grade 1 Filter Paper',
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

                    $this->command?->info("  Seeded Sub-Category: {$sub['name']} (Min SLA: {$sub['minimum_level']} {$sub['unit_type']})");

                    // 6. Seed Inventory Items (Actual Physical Batches)
                    // We seed multiple batches: some healthy stock and one "low stock" trigger to verify analytical monitoring dashboards
                    $isLowStock = ($sub['name'] === 'Hydrochloric Acid 37% ACS' || $sub['name'] === 'Whatman Grade 1 Filter Paper');
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

                    $this->command?->info("    Seeded Stock Batch: Qty={$qtyOnHand} {$sub['unit_type']} (" . ($isLowStock ? 'LOW STOCK ALERT' : 'HEALTHY') . ")");

                    // 7. Seed Supply Purchase Orders (Inventory Orders)
                    if ($isLowStock) {
                        $orderId = Str::uuid()->toString();
                        $poNo = 'PO-2026-' . rand(1000, 9999);

                        DB::connection('pgsql')->table('inventory_orders')->insert([
                            'id' => $orderId,
                            'order_number' => $poNo,
                            'supplier_id' => $seededSuppliers[0], // Sigma-Aldrich
                            'status' => 'not_fulfilled',
                            'company_id' => $companyId,
                            'comments' => 'Emergency restock for low level LIMS laboratory items.',
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

                        $this->command?->info("    Created Supply Reorder Purchase Order: {$poNo} for 50 {$sub['unit_type']}");
                    }
                }
            }

            // Clear cache
            \Illuminate\Support\Facades\Cache::flush();

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 9 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
