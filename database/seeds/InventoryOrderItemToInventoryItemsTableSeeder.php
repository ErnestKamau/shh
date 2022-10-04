<?php

use Illuminate\Database\Seeder;

class InventoryOrderItemToInventoryItemsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_order_item_to_inventory_items')->delete();
        
        \DB::table('inventory_order_item_to_inventory_items')->insert(array (
            0 => 
            array (
                'id' => '1',
                'inventory_order_id' => '2',
                'inventory_item_id' => '10008',
                'inventory_order_item_id' => '10002',
                'created_at' => '2020-06-08 20:47:02.310',
                'updated_at' => '2020-06-08 20:47:02.310',
            ),
            1 => 
            array (
                'id' => '2',
                'inventory_order_id' => '2',
                'inventory_item_id' => '10009',
                'inventory_order_item_id' => '10003',
                'created_at' => '2020-06-08 20:47:02.340',
                'updated_at' => '2020-06-08 20:47:02.340',
            ),
            2 => 
            array (
                'id' => '3',
                'inventory_order_id' => '1',
                'inventory_item_id' => '10010',
                'inventory_order_item_id' => '3',
                'created_at' => '2020-06-08 21:09:10.370',
                'updated_at' => '2020-06-08 21:09:10.370',
            ),
            3 => 
            array (
                'id' => '4',
                'inventory_order_id' => '1',
                'inventory_item_id' => '10011',
                'inventory_order_item_id' => '3',
                'created_at' => '2020-06-08 21:10:28.160',
                'updated_at' => '2020-06-08 21:10:28.160',
            ),
            4 => 
            array (
                'id' => '10002',
                'inventory_order_id' => '10003',
                'inventory_item_id' => '20013',
                'inventory_order_item_id' => '20003',
                'created_at' => '2020-06-18 22:20:09.100',
                'updated_at' => '2020-06-18 22:20:09.100',
            ),
            5 => 
            array (
                'id' => '10003',
                'inventory_order_id' => '20003',
                'inventory_item_id' => '20046',
                'inventory_order_item_id' => '30003',
                'created_at' => '2020-06-21 23:24:06.957',
                'updated_at' => '2020-06-21 23:24:06.957',
            ),
            6 => 
            array (
                'id' => '10004',
                'inventory_order_id' => '20002',
                'inventory_item_id' => '20048',
                'inventory_order_item_id' => '30002',
                'created_at' => '2020-06-22 01:25:15.543',
                'updated_at' => '2020-06-22 01:25:15.543',
            ),
        ));
        
        
    }
}