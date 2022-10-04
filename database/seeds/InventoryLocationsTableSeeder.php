<?php

use Illuminate\Database\Seeder;

class InventoryLocationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_locations')->delete();
        
        \DB::table('inventory_locations')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Kenya',
                'level' => '1',
                'inventory_location_id' => '0',
                'created_at' => '2020-06-20 01:15:52.897',
                'updated_at' => '2020-06-20 01:15:52.897',
                'active' => NULL,
                'company_id' => '1',
                'currency' => NULL,
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Ethiopia',
                'level' => '1',
                'inventory_location_id' => '0',
                'created_at' => '2020-06-20 01:20:12.937',
                'updated_at' => '2020-06-20 01:20:12.937',
                'active' => NULL,
                'company_id' => '1',
                'currency' => NULL,
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'KECU',
                'level' => '2',
                'inventory_location_id' => '1',
                'created_at' => '2020-06-20 01:21:08.537',
                'updated_at' => '2020-06-20 01:21:08.537',
                'active' => NULL,
                'company_id' => '1',
                'currency' => NULL,
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'KEPO',
                'level' => '2',
                'inventory_location_id' => '1',
                'created_at' => '2020-06-20 01:21:28.593',
                'updated_at' => '2020-06-20 01:21:28.593',
                'active' => NULL,
                'company_id' => '1',
                'currency' => NULL,
            ),
            4 => 
            array (
                'id' => '5',
                'name' => 'L&G',
                'level' => '3',
                'inventory_location_id' => '4',
                'created_at' => '2020-06-20 01:21:53.183',
                'updated_at' => '2020-06-20 01:21:53.183',
                'active' => NULL,
                'company_id' => '1',
                'currency' => NULL,
            ),
            5 => 
            array (
                'id' => '6',
                'name' => 'P&S',
                'level' => '3',
                'inventory_location_id' => '4',
                'created_at' => '2020-06-20 01:22:09.053',
                'updated_at' => '2020-06-20 01:27:50.653',
                'active' => NULL,
                'company_id' => '1',
                'currency' => NULL,
            ),
            6 => 
            array (
                'id' => '8',
                'name' => 'R&D',
                'level' => '3',
                'inventory_location_id' => '4',
                'created_at' => '2020-06-20 03:36:09.133',
                'updated_at' => '2020-06-20 03:36:09.133',
                'active' => '1',
                'company_id' => '1',
                'currency' => NULL,
            ),
        ));
        
        
    }
}