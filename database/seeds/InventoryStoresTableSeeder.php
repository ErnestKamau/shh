<?php

use Illuminate\Database\Seeder;

class InventoryStoresTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_stores')->delete();
        
        \DB::table('inventory_stores')->insert(array (
            0 => 
            array (
                'id' => '2',
                'name' => 'In-Organic',
                'company_id' => '1',
                'inventory_location_id' => '3',
                'created_at' => '2020-06-21 06:26:15.237',
                'updated_at' => '2020-06-21 06:26:15.237',
                'type_of_store' => 'inventory_store',
            ),
            1 => 
            array (
                'id' => '3',
                'name' => 'Organic',
                'company_id' => '1',
                'inventory_location_id' => '3',
                'created_at' => '2020-06-21 06:26:21.887',
                'updated_at' => '2020-06-21 06:26:21.887',
                'type_of_store' => 'inventory_store',
            ),
            2 => 
            array (
                'id' => '10002',
                'name' => 'Project Store',
                'company_id' => '1',
                'inventory_location_id' => '5',
                'created_at' => '2020-07-03 12:44:56.190',
                'updated_at' => '2020-07-03 12:44:56.190',
                'type_of_store' => 'inventory_store',
            ),
            3 => 
            array (
                'id' => '10003',
                'name' => 'Food Store',
                'company_id' => '1',
                'inventory_location_id' => '3',
                'created_at' => '2020-07-21 12:16:48.843',
                'updated_at' => '2020-07-21 12:16:48.843',
                'type_of_store' => 'inventory_store',
            ),
            4 => 
            array (
                'id' => '10005',
                'name' => 'Lab Storage',
                'company_id' => '1',
                'inventory_location_id' => '3',
                'created_at' => '2020-07-23 18:01:47.680',
                'updated_at' => '2020-07-23 18:01:47.680',
                'type_of_store' => 'lab_store',
            ),
        ));
        
        
    }
}