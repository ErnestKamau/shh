<?php

use Illuminate\Database\Seeder;

class InventoryCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_categories')->delete();
        
        \DB::table('inventory_categories')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Sugar',
                'description' => 'This is the sweetest sweetener.',
                'image' => '/storage/categories/JQEzPElwX2SmEUWcM7Fol6KE8xCwImq1vgZPVhTJ.jpeg',
                'created_at' => '2020-04-14 22:48:09.440',
                'updated_at' => '2020-06-21 08:52:17.803',
                'company_id' => '1',
                'inventory_location_id' => '3',
                'category_type' => 'normal',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Test Tubes',
                'description' => 'Them test tubes',
                'image' => '/storage/categories/Lfim88Lukq390n3dh5jod4FmB1J9vudD4gRl4P7Z.jpeg',
                'created_at' => '2020-05-25 14:04:55.483',
                'updated_at' => '2020-06-22 01:29:14.073',
                'company_id' => '1',
                'inventory_location_id' => '3',
                'category_type' => 'normal',
            ),
            2 => 
            array (
                'id' => '10002',
                'name' => 'Stock',
                'description' => 'All stock',
                'image' => '/storage/categories/mG3WvWa1CbfUrp00R2A1fBc20unMkSLOAwqsz0Dt.png',
                'created_at' => '2020-06-22 01:31:15.573',
                'updated_at' => '2020-06-22 01:31:15.573',
                'company_id' => '1',
                'inventory_location_id' => '5',
                'category_type' => 'normal',
            ),
            3 => 
            array (
                'id' => '10003',
                'name' => 'Non Stock',
                'description' => 'PPE and Engineering Consumables',
                'image' => '/storage/categories/W97LJOHDXiZx4DUaYigfkZufB2ysIxnaKsLGzHt0.png',
                'created_at' => '2020-06-22 01:32:18.217',
                'updated_at' => '2020-06-22 01:32:18.217',
                'company_id' => '1',
                'inventory_location_id' => '5',
                'category_type' => 'normal',
            ),
            4 => 
            array (
                'id' => '10004',
                'name' => 'Project Materials',
                'description' => 'All Project Materials.',
                'image' => '/storage/categories/eKGkoQK6RqLffdgZ0V2TwIbAkRMVroqVWZQOGxIU.png',
                'created_at' => '2020-06-22 01:33:19.777',
                'updated_at' => '2020-06-22 01:33:19.777',
                'company_id' => '1',
                'inventory_location_id' => '5',
                'category_type' => 'normal',
            ),
            5 => 
            array (
                'id' => '20002',
                'name' => 'Apparatus',
                'description' => 'Apparatus',
                'image' => '/storage/categories/79TWOvsNTIXV6tFR1xncIOEMKZLq5cd5JyqjRmAP.jpeg',
                'created_at' => '2020-07-21 12:12:12.027',
                'updated_at' => '2020-07-21 12:12:12.027',
                'company_id' => '1',
                'inventory_location_id' => '3',
                'category_type' => 'normal',
            ),
            6 => 
            array (
                'id' => '20003',
                'name' => 'Laboratory Samples',
                'description' => 'Inventory of Laboratory Samples Storage',
                'image' => '/storage/categories/UhiO6brfGDXbwte5XP68xg3pagk9xrgAE4UqJ9m1.jpeg',
                'created_at' => '2020-07-24 12:42:13.090',
                'updated_at' => '2020-07-24 12:42:13.090',
                'company_id' => '1',
                'inventory_location_id' => '3',
                'category_type' => 'is_lab_samples',
            ),
            7 => 
            array (
                'id' => '20004',
                'name' => 'Laboratory Reagents',
                'description' => 'Holds all the reagents required for sample analysis',
                'image' => '/storage/categories/PJj4sc41oqgP4Pp46s99GdibKsxAc6gzBGeXnRo6.webp',
                'created_at' => '2020-07-31 19:01:56.187',
                'updated_at' => '2020-07-31 19:01:56.187',
                'company_id' => '1',
                'inventory_location_id' => '3',
                'category_type' => 'normal',
            ),
        ));
        
        
    }
}