<?php

use Illuminate\Database\Seeder;

class InventoryOrderItemsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_order_items')->delete();
        
        \DB::table('inventory_order_items')->insert(array (
            0 => 
            array (
                'id' => '1',
                'inventory_order_id' => '1',
                'inventory_category_id' => '1',
                'inventory_sub_category_id' => '1',
                'quantity' => '1200.0',
                'fulfilled' => '1',
                'created_at' => '2020-05-26 07:09:34.367',
                'updated_at' => '2020-05-26 07:09:34.367',
            ),
            1 => 
            array (
                'id' => '3',
                'inventory_order_id' => '1',
                'inventory_category_id' => '2',
                'inventory_sub_category_id' => '2',
                'quantity' => '670.0',
                'fulfilled' => '1',
                'created_at' => '2020-05-29 06:57:17.470',
                'updated_at' => '2020-06-08 21:10:28.177',
            ),
            2 => 
            array (
                'id' => '10002',
                'inventory_order_id' => '2',
                'inventory_category_id' => '1',
                'inventory_sub_category_id' => '1',
                'quantity' => '1234.0',
                'fulfilled' => '1',
                'created_at' => '2020-06-05 02:42:45.103',
                'updated_at' => '2020-06-08 20:47:02.320',
            ),
            3 => 
            array (
                'id' => '10003',
                'inventory_order_id' => '2',
                'inventory_category_id' => '2',
                'inventory_sub_category_id' => '2',
                'quantity' => '567.0',
                'fulfilled' => '1',
                'created_at' => '2020-06-05 02:42:45.120',
                'updated_at' => '2020-06-08 20:47:02.353',
            ),
            4 => 
            array (
                'id' => '20002',
                'inventory_order_id' => '10002',
                'inventory_category_id' => '2',
                'inventory_sub_category_id' => '2',
                'quantity' => '1234.0',
                'fulfilled' => '0',
                'created_at' => '2020-06-08 22:31:26.987',
                'updated_at' => '2020-06-08 22:31:26.987',
            ),
            5 => 
            array (
                'id' => '20003',
                'inventory_order_id' => '10003',
                'inventory_category_id' => '2',
                'inventory_sub_category_id' => '2',
                'quantity' => '1345.0',
                'fulfilled' => '1',
                'created_at' => '2020-06-08 22:37:31.017',
                'updated_at' => '2020-06-18 22:20:09.117',
            ),
            6 => 
            array (
                'id' => '30002',
                'inventory_order_id' => '20002',
                'inventory_category_id' => '1',
                'inventory_sub_category_id' => '10002',
                'quantity' => '2000.0',
                'fulfilled' => '1',
                'created_at' => '2020-06-21 09:19:57.057',
                'updated_at' => '2020-06-22 01:25:15.560',
            ),
            7 => 
            array (
                'id' => '30003',
                'inventory_order_id' => '20003',
                'inventory_category_id' => '1',
                'inventory_sub_category_id' => '10002',
                'quantity' => '200.0',
                'fulfilled' => '1',
                'created_at' => '2020-06-21 22:52:31.270',
                'updated_at' => '2020-06-21 23:24:06.970',
            ),
        ));
        
        
    }
}