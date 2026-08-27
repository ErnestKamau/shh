<?php

use App\Approvals;
use App\InventoryCategories;
use App\InventoryDepartment;
use App\InventoryItem;
use App\InventoryLocation;
use App\InventoryStore;
use App\InventoryStoreSlot;
use App\InventoryStoreSlotContent;
use App\InventorySubCategories;
use App\ItemBrand;
use App\RequestEntity;
use App\RequestEntityItem;
use App\Role;
use App\StoreToCostCenter;
use App\Supplier;
use App\SupplierCategory;
use App\SupplierContact;
use App\User;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Idempotent demo bootstrap for a complete Inventory site tree:
 *
 *   Location (e.g. Dubai Laboratory)
 *     ├── Departments (org = cost centers + inventory process depts)
 *     ├── Stores → Slots → Stock
 *     ├── Categories → Items (+ brands, supplier links)
 *     ├── Suppliers (5) + contacts
 *     ├── Permissions / roles / approval configs
 *     └── Sample requests stamped with that location
 *
 * Grants 1.kamauernest@gmail.com all workflow roles + location access.
 *
 * Run:  php artisan db:seed --class='\InventoryDemoBootstrapSeeder'
 */
class InventoryDemoBootstrapSeeder extends Seeder
{
    private const TARGET_EMAIL = '1.kamauernest@gmail.com';
    private const LOCATION_NAME = 'Dubai Laboratory';
    /** Previous demo names — renamed on re-seed so existing rows stay linked. */
    private const LEGACY_LOCATION_NAMES = ['SystemAdmin', 'Nairobi Laboratory'];

    private User $user;
    /** Company UUID string (amspec_dubai) — never cast to int. */
    private string $companyId;
    private InventoryLocation $location;

    /** @var array<string, Role> */
    private array $roles = [];

    /** @var array<string, InventoryDepartment> */
    private array $departments = [];

    /** @var array<string, InventoryStore> */
    private array $stores = [];

    /** @var array<string, InventoryStoreSlot> */
    private array $slots = [];

    /** @var array<string, InventoryCategories> */
    private array $categories = [];

    /** @var array<string, InventorySubCategories> */
    private array $items = [];

    /** @var array<string, Supplier> */
    private array $suppliers = [];

    /** Role → Inventory component → actions (Spatie dotted perms). */
    private array $roleBundles = [
        'Requester' => [
            'Purchase Request'    => ['Add', 'Edit', 'View', 'Delete'],
            'General Requisition' => ['Add', 'Edit', 'View', 'Delete'],
            'Request to Store'    => ['Add', 'Edit', 'View', 'Delete'],
            'stage'               => ['View', 'Edit'],
            'Approval-Requests'   => ['View'],
            'Inventory-Movement'  => ['View'],
            'Categories'          => ['View'],
            'Suppliers'           => ['View'],
        ],
        'Line Manager' => [
            'Purchase Request'    => ['View', 'Approve', 'Decline'],
            'Request to Store'    => ['View', 'Approve', 'Decline'],
            'General Requisition' => ['View', 'Approve', 'Decline'],
            'Approval-Requests'   => ['View', 'Approve', 'Decline'],
            'Inventory-Movement'  => ['View'],
            'stage'               => ['View', 'Approve', 'Decline'],
        ],
        'Store' => [
            'Store'               => ['Add', 'Edit', 'View', 'Delete'],
            'Goods Receipt'       => ['Add', 'Edit', 'View', 'Delete'],
            'Goods Return'        => ['Add', 'Edit', 'View', 'Delete'],
            'Material Issuance'   => ['Add', 'Edit', 'View', 'Delete', 'Approve', 'Decline'],
            'Stock-Transfer'      => ['Add', 'Edit', 'View'],
            'Stock-Taking'        => ['View', 'Edit'],
            'Categories'          => ['View'],
            'Suppliers'           => ['View'],
            'Inventory-Movement'  => ['View'],
            'stage'               => ['View', 'Edit', 'Approve', 'Decline'],
        ],
        'Store Manager' => [
            'Store'               => ['Add', 'Edit', 'View', 'Delete'],
            'Goods Receipt'       => ['Add', 'Edit', 'View', 'Delete', 'Approve', 'Decline'],
            'Goods Return'        => ['Add', 'Edit', 'View', 'Delete', 'Approve', 'Decline'],
            'Material Issuance'   => ['Add', 'Edit', 'View', 'Delete', 'Approve', 'Decline'],
            'Stock-Transfer'      => ['Add', 'Edit', 'View', 'Delete', 'Approve', 'Decline'],
            'Stock-Taking'        => ['Add', 'Edit', 'View', 'Delete', 'Approve', 'Decline'],
            'Categories'          => ['Add', 'Edit', 'View', 'Delete'],
            'Departments'         => ['Add', 'Edit', 'View', 'Delete'],
            'Suppliers'           => ['Add', 'Edit', 'View', 'Delete'],
            'Configuration'       => ['Add', 'Edit', 'View', 'Delete'],
            'Purchase Request'    => ['View', 'Approve', 'Decline'],
            'Request to Store'    => ['View', 'Approve', 'Decline'],
            'General Requisition' => ['View', 'Approve', 'Decline'],
            'Approval-Requests'   => ['View', 'Approve', 'Decline'],
            'Inventory-Movement'  => ['View'],
            'stage'               => ['View', 'Edit', 'Delete', 'Approve', 'Decline'],
        ],
        'Procurement' => [
            'Request for Quotation' => ['Add', 'Edit', 'View', 'Delete', 'Approve', 'Decline'],
            'Purchase Orders'       => ['Add', 'Edit', 'View', 'Delete', 'Approve', 'Decline'],
            'Purchase Request'      => ['View'],
            'Goods Receipt'         => ['View'],
            'Goods Return'          => ['View', 'Approve', 'Decline'],
            'Suppliers'             => ['Add', 'Edit', 'View', 'Delete'],
            'Approval-Requests'     => ['View', 'Approve', 'Decline'],
            'Inventory-Movement'    => ['View'],
            'stage'                 => ['View', 'Edit', 'Delete', 'Approve', 'Decline'],
        ],
        'Finance Approver' => [
            'Purchase Orders'     => ['View', 'Approve', 'Decline'],
            'Goods Receipt'       => ['View', 'Approve', 'Decline'],
            'Approval-Requests'   => ['View', 'Approve', 'Decline'],
            'Inventory-Movement'  => ['View'],
            'stage'               => ['View', 'Approve', 'Decline'],
        ],
        'Admin' => [], // granted every Inventory permission below
    ];

    public function run(): void
    {
        $this->resolveUser();

        $this->command?->info('Seeding Inventory demo under location "'.self::LOCATION_NAME.'" for '.$this->user->email);

        DB::transaction(function () {
            $this->seedLocation();
            $this->seedDepartments();
            $this->seedStoresAndSlots();
            $this->seedCostCenterMappings();
            $this->seedCategoriesAndItems();
            $this->seedSuppliers();
            $this->seedStock();
            $this->seedPermissionsAndRoles();
            $this->seedApprovals();
            $this->grantUserAccess();
            $this->seedSampleRequests();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('InventoryDemoBootstrapSeeder complete.');
    }

    private function resolveUser(): void
    {
        $user = User::where('email', self::TARGET_EMAIL)->first();
        if (! $user) {
            throw new \RuntimeException(
                'User '.self::TARGET_EMAIL.' not found. Create the user first, then re-run this seeder.'
            );
        }

        $companyId = $this->uuidOrNull($user->company_id);
        if ($companyId === null) {
            throw new \RuntimeException(
                'User '.self::TARGET_EMAIL.' has no valid UUID company_id (got: '.var_export($user->company_id, true).').'
            );
        }

        $this->user = $user;
        $this->companyId = $companyId;
    }

    /** Empty / non-UUID FK values must be null on Postgres uuid columns (never 0). */
    private function uuidOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return null;
        }

        $value = is_string($value) ? trim($value) : (string) $value;

        return Str::isUuid($value) ? $value : null;
    }

    private function columnAcceptsUuid(string $table, string $column): bool
    {
        if (! Schema::hasColumn($table, $column)) {
            return false;
        }

        try {
            $type = Schema::getColumnType($table, $column);
        } catch (\Throwable) {
            return false;
        }

        // Native uuid, or varchar/string used to hold UUID text (users.location_id pattern).
        return in_array($type, ['uuid', 'guid', 'string', 'text'], true);
    }

    /** Drop attributes whose columns are missing on this DB (schema differs by branch). */
    private function onlyExistingColumns(string $table, array $attributes): array
    {
        return array_filter(
            $attributes,
            fn ($value, $column) => Schema::hasColumn($table, $column),
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function seedLocation(): void
    {
        $legacy = InventoryLocation::where('company_id', $this->companyId)
            ->whereIn('name', self::LEGACY_LOCATION_NAMES)
            ->first();

        if ($legacy) {
            $legacy->name = self::LOCATION_NAME;
            $legacy->active = 1;
            $legacy->save();
            $this->location = $legacy;
        } else {
            $this->location = InventoryLocation::firstOrCreate(
                [
                    'name' => self::LOCATION_NAME,
                    'company_id' => $this->companyId,
                ],
                [
                    'level' => 1,
                    'inventory_location_id' => null, // root site — never 0 on uuid column
                    'active' => 1,
                ]
            );
        }

        $this->command?->info("Location ready: {$this->location->name} ({$this->location->id})");
    }

    private function seedDepartments(): void
    {
        $org = [
            'Laboratory',
            'Procurement',
            'Finance',
            'Stores',
            'Technical',
            'Administration',
        ];

        $inventory = [
            'Stock Taking',
            'Item Disposal',
            'Inter-store Transfer',
            'Returned 2 Store',
        ];

        foreach ($org as $name) {
            $attrs = ['active' => 1];
            // department_head_id may still be integer on some DBs; only set when UUID-safe.
            if ($this->columnAcceptsUuid('inventory_departments', 'department_head_id')) {
                $attrs['department_head_id'] = $this->user->id;
            }

            $this->departments[$name] = InventoryDepartment::updateOrCreate(
                [
                    'name' => $name,
                    'company_id' => $this->companyId,
                    'location_id' => $this->location->id,
                    'module' => 'organizational',
                ],
                $attrs
            );
        }

        foreach ($inventory as $name) {
            $this->departments[$name] = InventoryDepartment::updateOrCreate(
                [
                    'name' => $name,
                    'company_id' => $this->companyId,
                    'location_id' => $this->location->id,
                    'module' => 'inventory',
                ],
                [
                    'active' => 1,
                ]
            );
        }

        $this->command?->info('Departments ready: '.count($this->departments));
    }

    private function seedStoresAndSlots(): void
    {
        $layout = [
            'Main Store' => [
                'type' => 'inventory_store',
                'slots' => ['Cold Storage Shelf C-1', 'General Shelf A-1', 'Receiving Bay R-1'],
            ],
            'Lab Store' => [
                'type' => 'lab_store',
                'slots' => ['Flammables Cabinet F-1', 'Reagent Rack L-1', 'Glassware Shelf G-1'],
            ],
        ];

        foreach ($layout as $storeName => $meta) {
            $store = InventoryStore::updateOrCreate(
                [
                    'name' => $storeName,
                    'company_id' => $this->companyId,
                    'inventory_location_id' => $this->location->id,
                ],
                [
                    'type_of_store' => $meta['type'],
                    'is_frozen' => 0,
                ]
            );
            $this->stores[$storeName] = $store;

            foreach ($meta['slots'] as $slotName) {
                $slot = InventoryStoreSlot::firstOrCreate(
                    [
                        'name' => $slotName,
                        'inventory_store_id' => $store->id,
                    ]
                );
                $this->slots[$slotName] = $slot;
            }
        }

        $this->command?->info('Stores/slots ready: '.count($this->stores).' stores, '.count($this->slots).' slots');
    }

    private function seedCostCenterMappings(): void
    {
        $map = [
            'Main Store' => ['Laboratory', 'Stores', 'Procurement', 'Finance', 'Administration'],
            'Lab Store' => ['Technical', 'Laboratory'],
        ];

        foreach ($map as $storeName => $costCenters) {
            $store = $this->stores[$storeName];
            foreach ($costCenters as $cc) {
                StoreToCostCenter::firstOrCreate(
                    [
                        'store_id' => $store->id,
                        'cost_center' => $cc,
                    ]
                );
            }
        }

        $this->command?->info('Cost-center → store mappings ready.');
    }

    private function seedCategoriesAndItems(): void
    {
        $catalog = [
            'Chemical Reagents' => [
                'description' => 'Analytical and ACS-grade chemical reagents.',
                'items' => [
                    [
                        'name' => 'Hydrochloric Acid 37% ACS',
                        'code' => 'HCL-37-ACS',
                        'description' => 'ACS certified analytical titration reagent.',
                        'manufacturer' => 'Merck',
                        'unit_type' => 'g',
                        'reporting_unit_of_measure' => 'g',
                        'unit_price' => 12.5,
                        'minimum_level' => 50,
                        'annual_consumption' => 70,
                        'working_days' => 365,
                        'internal_lead_time' => 2,
                        'external_lead_time' => 8,
                        'estimated_variation_in_demand_average_consumption' => 2,
                        'maximum_order_quantity' => 500,
                        'delivery_days' => 10,
                        'item_classification' => 1,
                        'brand' => 'Merck',
                        'stock' => [
                            ['slot' => 'Cold Storage Shelf C-1', 'qty' => 2, 'batch' => 'HCL-DEMO-001'],
                        ],
                    ],
                    [
                        'name' => 'Sodium Hydroxide Pellets AR',
                        'code' => 'NAOH-AR',
                        'description' => 'Analytical reagent grade NaOH pellets.',
                        'manufacturer' => 'Sigma-Aldrich',
                        'unit_type' => 'g',
                        'reporting_unit_of_measure' => 'g',
                        'unit_price' => 8.0,
                        'minimum_level' => 100,
                        'annual_consumption' => 200,
                        'working_days' => 365,
                        'internal_lead_time' => 1,
                        'external_lead_time' => 7,
                        'estimated_variation_in_demand_average_consumption' => 5,
                        'maximum_order_quantity' => 1000,
                        'delivery_days' => 7,
                        'item_classification' => 1,
                        'brand' => 'Sigma',
                        'stock' => [
                            ['slot' => 'General Shelf A-1', 'qty' => 250, 'batch' => 'NAOH-DEMO-001'],
                        ],
                    ],
                ],
            ],
            'Solvents' => [
                'description' => 'HPLC and general laboratory solvents.',
                'items' => [
                    [
                        'name' => 'Acetonitrile HPLC Grade',
                        'code' => 'ACN-HPLC',
                        'description' => 'High-purity acetonitrile for chromatography.',
                        'manufacturer' => 'Fisher Scientific',
                        'unit_type' => 'g',
                        'reporting_unit_of_measure' => 'g',
                        'unit_price' => 45.0,
                        'minimum_level' => 500,
                        'annual_consumption' => 2000,
                        'working_days' => 365,
                        'internal_lead_time' => 2,
                        'external_lead_time' => 14,
                        'estimated_variation_in_demand_average_consumption' => 10,
                        'maximum_order_quantity' => 5000,
                        'delivery_days' => 14,
                        'item_classification' => 1,
                        'brand' => 'Fisher',
                        'stock' => [
                            ['slot' => 'Flammables Cabinet F-1', 'qty' => 1000, 'batch' => 'ACN-DEMO-001'],
                        ],
                    ],
                    [
                        'name' => 'Methanol Absolute',
                        'code' => 'MEOH-ABS',
                        'description' => 'Anhydrous methanol for sample prep.',
                        'manufacturer' => 'Honeywell',
                        'unit_type' => 'ml',
                        'reporting_unit_of_measure' => 'ml',
                        'unit_price' => 18.0,
                        'minimum_level' => 200,
                        'annual_consumption' => 800,
                        'working_days' => 365,
                        'internal_lead_time' => 1,
                        'external_lead_time' => 10,
                        'estimated_variation_in_demand_average_consumption' => 8,
                        'maximum_order_quantity' => 2000,
                        'delivery_days' => 10,
                        'item_classification' => 1,
                        'brand' => 'Honeywell',
                        'stock' => [
                            ['slot' => 'Flammables Cabinet F-1', 'qty' => 500, 'batch' => 'MEOH-DEMO-001'],
                        ],
                    ],
                ],
            ],
            'Glassware' => [
                'description' => 'Laboratory glassware and volumetric ware.',
                'items' => [
                    [
                        'name' => 'Volumetric Flask 100ml Class A',
                        'code' => 'VF-100-A',
                        'description' => 'Borosilicate Class A volumetric flask.',
                        'manufacturer' => 'Pyrex',
                        'unit_type' => 'pcs',
                        'reporting_unit_of_measure' => 'pcs',
                        'unit_price' => 15.0,
                        'minimum_level' => 10,
                        'annual_consumption' => 40,
                        'working_days' => 365,
                        'internal_lead_time' => 1,
                        'external_lead_time' => 21,
                        'estimated_variation_in_demand_average_consumption' => 5,
                        'maximum_order_quantity' => 100,
                        'delivery_days' => 21,
                        'item_classification' => 1,
                        'brand' => 'Pyrex',
                        'stock' => [
                            ['slot' => 'Glassware Shelf G-1', 'qty' => 24, 'batch' => 'VF-DEMO-001'],
                        ],
                    ],
                ],
            ],
        ];

        $mainStore = $this->stores['Main Store'];
        $defaultSlot = $this->slots['General Shelf A-1'];

        foreach ($catalog as $catName => $catData) {
            $category = InventoryCategories::updateOrCreate(
                [
                    'name' => $catName,
                    'company_id' => $this->companyId,
                    'inventory_location_id' => $this->location->id,
                ],
                $this->onlyExistingColumns('inventory_categories', [
                    'description' => $catData['description'],
                    'image' => '',
                    'category_type' => 'normal',
                    'is_lab' => 1,
                    'default_store_id' => $mainStore->id,
                    'default_slot_id' => $defaultSlot->id,
                    'active' => 1,
                ])
            );
            $this->categories[$catName] = $category;

            foreach ($catData['items'] as $itemData) {
                $item = InventorySubCategories::updateOrCreate(
                    [
                        'code' => $itemData['code'],
                        'company_id' => $this->companyId,
                        'location_id' => $this->location->id,
                    ],
                    $this->onlyExistingColumns('inventory_sub_categories', [
                        'name' => $itemData['name'],
                        'description' => $itemData['description'],
                        'image' => '',
                        'inventory_category_id' => $category->id,
                        'manufacturer' => $itemData['manufacturer'],
                        'minimum_level' => $itemData['minimum_level'],
                        'unit_type' => $itemData['unit_type'],
                        'reporting_unit_of_measure' => $itemData['reporting_unit_of_measure'],
                        'unit_price' => $itemData['unit_price'],
                        'annual_consumption' => $itemData['annual_consumption'],
                        'working_days' => $itemData['working_days'],
                        'internal_lead_time' => $itemData['internal_lead_time'],
                        'external_lead_time' => $itemData['external_lead_time'],
                        'estimated_variation_in_demand_average_consumption' => $itemData['estimated_variation_in_demand_average_consumption'],
                        'maximum_order_quantity' => $itemData['maximum_order_quantity'],
                        'delivery_days' => $itemData['delivery_days'],
                        'item_classification' => $itemData['item_classification'],
                        'is_lab' => 1,
                        'active' => 1,
                        'reaorder_level' => 0,
                        'requires_reorder' => 0,
                        'available_stock' => 0,
                    ])
                );

                // Persist stock plan on the instance for seedStock().
                $item->setAttribute('_demo_stock', $itemData['stock']);
                $item->setAttribute('_demo_brand', $itemData['brand']);
                $this->items[$itemData['code']] = $item;

                ItemBrand::updateOrCreate(
                    [
                        'inventory_sub_category_id' => $item->id,
                        'name' => $itemData['brand'],
                    ],
                    [
                        'image' => '',
                        'status' => 1,
                    ]
                );
            }
        }

        $this->command?->info('Categories/items ready: '.count($this->categories).' categories, '.count($this->items).' items');
    }

    private function seedSuppliers(): void
    {
        $suppliers = [
            [
                'name' => 'Merck Life Science KE',
                'email' => 'demo.merck@amspec.local',
                'phone' => '+254700000001',
                'building' => 'Industrial Area Block A',
                'street' => 'Enterprise Road',
                'town' => 'Nairobi',
                'address' => 'P.O. Box 1001-00100 Nairobi',
                'pin_number' => 'P051000001A',
                'vat_number' => 'VAT-MERCK-001',
                'supplier_code' => 'SUP-MERCK',
                'payment_terms' => 'Net 30',
                'payment_method' => 'Bank Transfer',
                'default_currency' => 'KES',
                'contact' => [
                    'name' => 'Jane Wanjiku',
                    'email' => 'jane.wanjiku@amspec.local',
                    'phone' => '+254711000001',
                    'type' => 'Sales',
                    'pin' => 'A123456789Z',
                    'id_number' => '12345678',
                ],
                'item_codes' => ['HCL-37-ACS', 'NAOH-AR'],
            ],
            [
                'name' => 'Fisher Scientific EA',
                'email' => 'demo.fisher@amspec.local',
                'phone' => '+254700000002',
                'building' => 'Science Park Unit 4',
                'street' => 'Mombasa Road',
                'town' => 'Nairobi',
                'address' => 'P.O. Box 2002-00100 Nairobi',
                'pin_number' => 'P051000002B',
                'vat_number' => 'VAT-FISHER-002',
                'supplier_code' => 'SUP-FISHER',
                'payment_terms' => 'Net 45',
                'payment_method' => 'Bank Transfer',
                'default_currency' => 'USD',
                'contact' => [
                    'name' => 'Peter Otieno',
                    'email' => 'peter.otieno@amspec.local',
                    'phone' => '+254711000002',
                    'type' => 'Account Manager',
                    'pin' => 'B223456789Y',
                    'id_number' => '22345678',
                ],
                'item_codes' => ['ACN-HPLC'],
            ],
            [
                'name' => 'Honeywell Lab Supplies',
                'email' => 'demo.honeywell@amspec.local',
                'phone' => '+254700000003',
                'building' => 'Westlands Plaza',
                'street' => 'Waiyaki Way',
                'town' => 'Nairobi',
                'address' => 'P.O. Box 3003-00100 Nairobi',
                'pin_number' => 'P051000003C',
                'vat_number' => 'VAT-HON-003',
                'supplier_code' => 'SUP-HON',
                'payment_terms' => 'Net 30',
                'payment_method' => 'Cheque',
                'default_currency' => 'KES',
                'contact' => [
                    'name' => 'Grace Njeri',
                    'email' => 'grace.njeri@amspec.local',
                    'phone' => '+254711000003',
                    'type' => 'Technical Sales',
                    'pin' => 'C323456789X',
                    'id_number' => '32345678',
                ],
                'item_codes' => ['MEOH-ABS'],
            ],
            [
                'name' => 'Pyrex Distributors Ltd',
                'email' => 'demo.pyrex@amspec.local',
                'phone' => '+254700000004',
                'building' => 'Glass House',
                'street' => 'Lunga Lunga Road',
                'town' => 'Nairobi',
                'address' => 'P.O. Box 4004-00500 Nairobi',
                'pin_number' => 'P051000004D',
                'vat_number' => 'VAT-PYREX-004',
                'supplier_code' => 'SUP-PYREX',
                'payment_terms' => 'Net 15',
                'payment_method' => 'Bank Transfer',
                'default_currency' => 'KES',
                'contact' => [
                    'name' => 'Samuel Kariuki',
                    'email' => 'samuel.kariuki@amspec.local',
                    'phone' => '+254711000004',
                    'type' => 'Sales',
                    'pin' => 'D423456789W',
                    'id_number' => '42345678',
                ],
                'item_codes' => ['VF-100-A'],
            ],
            [
                'name' => 'Sigma Lab Partners',
                'email' => 'demo.sigma@amspec.local',
                'phone' => '+254700000005',
                'building' => 'Biotech Hub',
                'street' => 'Ngong Road',
                'town' => 'Nairobi',
                'address' => 'P.O. Box 5005-00200 Nairobi',
                'pin_number' => 'P051000005E',
                'vat_number' => 'VAT-SIGMA-005',
                'supplier_code' => 'SUP-SIGMA',
                'payment_terms' => 'Net 30',
                'payment_method' => 'Bank Transfer',
                'default_currency' => 'USD',
                'contact' => [
                    'name' => 'Amina Hassan',
                    'email' => 'amina.hassan@amspec.local',
                    'phone' => '+254711000005',
                    'type' => 'Key Account',
                    'pin' => 'E523456789V',
                    'id_number' => '52345678',
                ],
                'item_codes' => ['HCL-37-ACS', 'NAOH-AR', 'ACN-HPLC'],
            ],
        ];

        foreach ($suppliers as $data) {
            $supplier = Supplier::updateOrCreate(
                [
                    'email' => $data['email'],
                ],
                $this->onlyExistingColumns('suppliers', [
                    'name' => $data['name'],
                    'logo' => '',
                    'phone' => $data['phone'],
                    'building' => $data['building'],
                    'street' => $data['street'],
                    'town' => $data['town'],
                    'address' => $data['address'],
                    'company_id' => $this->companyId,
                    'active' => 1,
                    'inventory_location_id' => $this->location->id,
                    'pin_number' => $data['pin_number'],
                    'vat_number' => $data['vat_number'],
                    'supplier_code' => $data['supplier_code'],
                    'payment_terms' => $data['payment_terms'],
                    'payment_method' => $data['payment_method'],
                    'default_currency' => $data['default_currency'],
                ])
            );
            $this->suppliers[$data['supplier_code']] = $supplier;

            $contact = $data['contact'];
            SupplierContact::updateOrCreate(
                [
                    'supplier_id' => $supplier->id,
                    'email' => $contact['email'],
                ],
                [
                    'name' => $contact['name'],
                    'phone' => $contact['phone'],
                    'type' => $contact['type'],
                    'pin' => $contact['pin'],
                    'id_number' => $contact['id_number'],
                ]
            );

            foreach ($data['item_codes'] as $code) {
                if (! isset($this->items[$code])) {
                    continue;
                }
                $item = $this->items[$code];
                $brand = ItemBrand::where('inventory_sub_category_id', $item->id)->first();

                SupplierCategory::updateOrCreate(
                    [
                        'supplier_id' => $supplier->id,
                        'inventory_sub_category_id' => $item->id,
                        'inventory_item_brand_id' => $this->uuidOrNull($brand->id ?? null),
                    ],
                    [
                        'supplier_image' => '',
                        'status' => 1,
                    ]
                );
            }
        }

        $this->command?->info('Suppliers ready: '.count($this->suppliers));
    }

    private function seedStock(): void
    {
        $defaultSupplier = reset($this->suppliers) ?: null;
        $deptId = $this->uuidOrNull($this->departments['Stores']->id ?? null);

        foreach ($this->items as $code => $item) {
            $plans = $item->getAttribute('_demo_stock') ?? [];
            $brandName = $item->getAttribute('_demo_brand');
            $brand = ItemBrand::where('inventory_sub_category_id', $item->id)
                ->where('name', $brandName)
                ->first();

            foreach ($plans as $plan) {
                $slot = $this->slots[$plan['slot']] ?? null;
                if (! $slot) {
                    continue;
                }

                $storeId = $slot->inventory_store_id;
                $batch = $plan['batch'];

                $stockAttrs = $this->onlyExistingColumns('inventory_items', [
                    'inventory_category_id' => $item->inventory_category_id,
                    'stock_in' => $plan['qty'],
                    'stock_out' => 0,
                    'created_by' => $this->user->id,
                    'supplier_id' => $this->uuidOrNull($defaultSupplier->id ?? null),
                    'inventory_department_id' => $deptId,
                    'status' => 'approved',
                    'expiry' => now()->addYears(2)->toDateString(),
                    'price' => $item->unit_price,
                    'barcode' => strtoupper(substr(md5($batch), 0, 12)),
                    'item_brand_id' => $this->uuidOrNull($brand->id ?? null),
                    'unit_of_measure' => $item->unit_type,
                    'lot_no' => $batch,
                    'date_of_manufacture' => now()->subMonths(2)->toDateString(),
                ]);

                // Only write edited_by when the column is UUID-safe (null); skip legacy integer default 0.
                if ($this->columnAcceptsUuid('inventory_items', 'edited_by')) {
                    $stockAttrs['edited_by'] = null;
                }

                $stock = InventoryItem::updateOrCreate(
                    [
                        'inventory_sub_category_id' => $item->id,
                        'inventory_location_id' => $this->location->id,
                        'inventory_store_id' => $storeId,
                        'inventory_store_slot_id' => $slot->id,
                        'batch_code' => $batch,
                    ],
                    $stockAttrs
                );

                InventoryStoreSlotContent::firstOrCreate(
                    [
                        'inventory_store_slot_id' => $slot->id,
                        'inventory_item_id' => $stock->id,
                    ],
                    [
                        'inventory_sub_category_id' => $item->id,
                    ]
                );
            }

            calculateAvailableStock($item->id);
        }

        $this->command?->info('Stock lots seeded and available_stock refreshed.');
    }

    private function seedPermissionsAndRoles(): void
    {
        // Ensure Inventory permission catalog exists.
        $this->call(InventoryPermissionsSeeder::class);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->companyId);

        foreach (array_keys($this->roleBundles) as $roleName) {
            $role = Role::firstOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'company_id' => $this->companyId,
                ],
                [
                    'description' => $roleName.' (Inventory demo)',
                    'active' => 1,
                    'level' => $roleName === 'Admin' ? 100 : 10,
                ]
            );
            $this->roles[$roleName] = $role;
        }

        foreach ($this->roleBundles as $roleName => $bundle) {
            $role = $this->roles[$roleName];
            $registrar->setPermissionsTeamId($role->company_id);

            if ($roleName === 'Admin') {
                $permissions = Permission::where('guard_name', 'web')
                    ->where('name', 'like', 'Inventory.%')
                    ->get();
            } else {
                $names = $this->expandBundle($bundle);
                $names[] = 'Inventory.permission';
                $permissions = Permission::where('guard_name', 'web')
                    ->whereIn('name', $names)
                    ->get();
            }

            foreach ($permissions as $permission) {
                if (! $role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }

            $this->command?->info("Role bundled: {$roleName} ({$permissions->count()} perms)");
        }

        // Also apply existing InventoryRolesSeeder bundles onto matching roles.
        $this->call(InventoryRolesSeeder::class);
    }

    private function expandBundle(array $componentActions): array
    {
        $names = [];
        foreach ($componentActions as $component => $actions) {
            foreach ($actions as $action) {
                $names[] = "Inventory.components.{$component}.{$action}";
            }
        }

        return $names;
    }

    private function seedApprovals(): void
    {
        // stage => [[title, roleName, level], ...]
        $configs = [
            'Purchase Request' => [
                ['Line Manager Approval', 'Line Manager', 1],
                ['Store Manager Approval', 'Store Manager', 2],
            ],
            'Request for Quotation' => [
                ['Procurement Acceptance', 'Procurement', 1],
            ],
            'Purchase Orders' => [
                ['Finance Approval', 'Finance Approver', 1],
                ['Admin Final Approval', 'Admin', 2],
            ],
            'Goods Receipt' => [
                ['Store Manager Approval', 'Store Manager', 1],
            ],
            'Goods Return' => [
                ['Store Manager Approval', 'Store Manager', 1],
                ['Procurement Head Approval', 'Procurement', 2],
            ],
            'Request to Store' => [
                ['Departmental Head Approval', 'Line Manager', 1],
            ],
            'Material Issuance' => [
                ['Store Issuance Approval', 'Store Manager', 1],
            ],
            'Stock-Taking' => [
                ['Store Manager Approval', 'Store Manager', 1],
            ],
            'Stock-Transfer' => [
                ['Store Manager Approval', 'Store Manager', 1],
            ],
        ];

        foreach ($configs as $stage => $steps) {
            foreach ($steps as [$title, $roleName, $level]) {
                $role = $this->roles[$roleName] ?? null;
                if (! $role) {
                    continue;
                }

                Approvals::updateOrCreate(
                    [
                        'for' => 'Requisition',
                        'stage' => $stage,
                        'level' => $level,
                        'inventory_location_id' => $this->location->id,
                        'role_id' => $role->id,
                    ],
                    [
                        'title' => $title,
                    ]
                );
            }
        }

        $this->command?->info('Approval configurations ready for '.$this->location->name.' workflows.');
    }

    private function grantUserAccess(): void
    {
        $registrar = app(PermissionRegistrar::class);

        // Location access
        DB::table('inventory_location_users')->updateOrInsert(
            [
                'user_id' => $this->user->id,
                'inventory_location_id' => $this->location->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->user->location_id = $this->location->id;
        if (empty($this->user->department_id) && isset($this->departments['Technical'])) {
            $this->user->department_id = $this->departments['Technical']->id;
        }
        $this->user->save();

        // All demo roles → legacy user_roles + Spatie
        foreach ($this->roles as $role) {
            UserRole::firstOrCreate(
                [
                    'user_id' => $this->user->id,
                    'role_id' => $role->id,
                ]
            );

            $registrar->setPermissionsTeamId($role->company_id);
            if (! $this->user->hasRole($role)) {
                $this->user->assignRole($role);
            }
        }

        $this->command?->info(
            'Granted '.count($this->roles).' roles + location access to '.$this->user->email
        );
    }

    private function seedSampleRequests(): void
    {
        $hcl = $this->items['HCL-37-ACS'] ?? null;
        $mainSlot = $this->slots['Cold Storage Shelf C-1'] ?? null;
        if (! $hcl || ! $mainSlot) {
            return;
        }

        $defs = [
            [
                'type' => 'Request to Store',
                'code' => 'RTS-DEMO-DXB-LAB',
                'legacy_code' => 'RTS-DEMO-NBO-LAB',
                'cost_center' => 'Laboratory',
                'description' => 'Demo RTS for Dubai Laboratory — Hydrochloric Acid draw.',
                'qty' => 1,
            ],
            [
                'type' => 'Purchase Request',
                'code' => 'PR-DEMO-DXB-LAB',
                'legacy_code' => 'PR-DEMO-NBO-LAB',
                'cost_center' => 'Laboratory,Stores',
                'description' => 'Demo PR for Dubai Laboratory — replenish Hydrochloric Acid.',
                'qty' => 100,
            ],
        ];

        foreach ($defs as $def) {
            $legacyCodes = array_filter([
                $def['code'],
                $def['legacy_code'] ?? null,
                'RTS-DEMO-SYSTEMADMIN',
                'PR-DEMO-SYSTEMADMIN',
                'RTS-DEMO-NBO-LAB',
                'PR-DEMO-NBO-LAB',
            ]);

            $req = RequestEntity::where('inventory_location_id', $this->location->id)
                ->where(function ($q) use ($def, $legacyCodes) {
                    $q->whereIn('request_code', $legacyCodes)
                        ->orWhere(function ($q2) use ($def) {
                            $q2->where('request_type', $def['type'])
                                ->where('request_code', 'like', '%-DEMO-%');
                        });
                })
                ->where('request_type', $def['type'])
                ->first();

            if (! $req) {
                $req = new RequestEntity;
                $req->inventory_location_id = $this->location->id;
            }

            $req->fill([
                'request_code' => $def['code'],
                'priority' => 'Normal',
                'currency' => 'KES',
                'status' => 'In Preparation',
                'request_type' => $def['type'],
                'approval_count' => 0,
                'required_approvals' => 0,
                'created_by' => $this->user->id,
                'description' => $def['description'],
                'nature_of_purchase' => 'Stock',
                'request_initiator' => $this->user->id,
                'cost_center' => $def['cost_center'],
                'due_date' => now()->addDays(14)->toDateString(),
            ]);
            $req->save();

            RequestEntityItem::updateOrCreate(
                [
                    'request_id' => $req->id,
                    'inventory_sub_category_id' => $hcl->id,
                ],
                [
                    'store_id' => $mainSlot->inventory_store_id,
                    'slot_id' => $mainSlot->id,
                    'quantity' => $def['qty'],
                    'net_value' => 0,
                    'currency' => 'KES',
                    'action' => 'normal',
                    'status' => 'pending',
                    'uom' => $hcl->unit_type,
                    'comments' => 'Seeded demo line',
                ]
            );
        }

        $this->command?->info('Sample requests stamped on '.$this->location->name.'.');
    }
}
