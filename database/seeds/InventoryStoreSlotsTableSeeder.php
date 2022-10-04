<?php

use Illuminate\Database\Seeder;

class InventoryStoreSlotsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_store_slots')->delete();
        
        \DB::table('inventory_store_slots')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'A1',
                'inventory_store_id' => '2',
                'created_at' => '2020-06-21 06:26:32.570',
                'updated_at' => '2020-06-21 06:26:32.570',
            ),
            1 => 
            array (
                'id' => '3',
                'name' => 'A3',
                'inventory_store_id' => '2',
                'created_at' => '2020-06-21 06:49:01.203',
                'updated_at' => '2020-06-21 06:49:01.203',
            ),
            2 => 
            array (
                'id' => '10002',
                'name' => 'OA1',
                'inventory_store_id' => '3',
                'created_at' => '2020-06-22 10:34:26.917',
                'updated_at' => '2020-06-22 10:34:26.917',
            ),
            3 => 
            array (
                'id' => '10003',
                'name' => 'OA2',
                'inventory_store_id' => '3',
                'created_at' => '2020-06-22 10:34:50.423',
                'updated_at' => '2020-06-22 10:34:50.423',
            ),
            4 => 
            array (
                'id' => '10004',
                'name' => '0A3',
                'inventory_store_id' => '3',
                'created_at' => '2020-06-22 10:35:00.913',
                'updated_at' => '2020-06-22 10:35:00.913',
            ),
            5 => 
            array (
                'id' => '10005',
                'name' => 'OA4',
                'inventory_store_id' => '3',
                'created_at' => '2020-06-22 10:35:07.990',
                'updated_at' => '2020-06-22 10:35:07.990',
            ),
            6 => 
            array (
                'id' => '10006',
                'name' => 'P1',
                'inventory_store_id' => '10002',
                'created_at' => '2020-07-03 12:46:27.453',
                'updated_at' => '2020-07-03 12:46:27.453',
            ),
            7 => 
            array (
                'id' => '10007',
                'name' => 'FS01',
                'inventory_store_id' => '10003',
                'created_at' => '2020-07-21 12:17:02.663',
                'updated_at' => '2020-07-21 12:17:02.663',
            ),
            8 => 
            array (
                'id' => '10008',
                'name' => 'Sample Storage - Organic',
                'inventory_store_id' => '10005',
                'created_at' => '2020-07-23 18:06:35.940',
                'updated_at' => '2020-07-23 18:06:35.940',
            ),
            9 => 
            array (
                'id' => '10009',
                'name' => 'Sample Storage - InOrganic',
                'inventory_store_id' => '10005',
                'created_at' => '2020-07-23 18:07:06.010',
                'updated_at' => '2020-07-23 18:07:06.010',
            ),
            10 => 
            array (
                'id' => '10010',
                'name' => 'Sample Storage - Food',
                'inventory_store_id' => '10005',
                'created_at' => '2020-07-23 18:27:46.473',
                'updated_at' => '2020-07-23 18:27:46.473',
            ),
        ));
        
        
    }
}