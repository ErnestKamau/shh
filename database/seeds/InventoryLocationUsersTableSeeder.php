<?php

use Illuminate\Database\Seeder;

class InventoryLocationUsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_location_users')->delete();
        
        \DB::table('inventory_location_users')->insert(array (
            0 => 
            array (
                'id' => '3',
                'user_id' => '1',
                'inventory_location_id' => '2',
                'created_at' => '2020-06-21 02:15:37.333',
                'updated_at' => '2020-06-21 02:15:37.333',
            ),
            1 => 
            array (
                'id' => '5',
                'user_id' => '1',
                'inventory_location_id' => '1',
                'created_at' => '2020-06-21 03:38:34.303',
                'updated_at' => '2020-06-21 03:38:34.303',
            ),
            2 => 
            array (
                'id' => '10002',
                'user_id' => '2',
                'inventory_location_id' => '4',
                'created_at' => '2020-08-03 16:59:45.047',
                'updated_at' => '2020-08-03 16:59:45.047',
            ),
            3 => 
            array (
                'id' => '10003',
                'user_id' => '2',
                'inventory_location_id' => '3',
                'created_at' => '2020-08-03 17:02:18.717',
                'updated_at' => '2020-08-03 17:02:18.717',
            ),
            4 => 
            array (
                'id' => '10004',
                'user_id' => '7',
                'inventory_location_id' => '1',
                'created_at' => '2020-08-05 09:13:36.113',
                'updated_at' => '2020-08-05 09:13:36.113',
            ),
            5 => 
            array (
                'id' => '10005',
                'user_id' => '7',
                'inventory_location_id' => '2',
                'created_at' => '2020-08-05 09:13:47.887',
                'updated_at' => '2020-08-05 09:13:47.887',
            ),
        ));
        
        
    }
}