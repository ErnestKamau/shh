<?php

use Illuminate\Database\Seeder;

class InventoryOrdersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_orders')->delete();
        
        \DB::table('inventory_orders')->insert(array (
            0 => 
            array (
                'id' => '1',
                'order_number' => 'KRA-0001',
                'supplier_id' => '2',
                'created_by' => '1',
                'status' => 'fulfilled',
                'created_at' => '2020-05-26 07:09:34.340',
                'updated_at' => '2020-06-08 21:10:28.190',
                'company_id' => '1',
                'comments' => 'This is high priority',
            ),
            1 => 
            array (
                'id' => '2',
                'order_number' => 'KRA-0002',
                'supplier_id' => '2',
                'created_by' => '1',
                'status' => 'fulfilled',
                'created_at' => '2020-06-05 02:42:45.087',
                'updated_at' => '2020-06-08 21:12:05.853',
                'company_id' => '1',
                'comments' => 'Need',
            ),
            2 => 
            array (
                'id' => '10003',
                'order_number' => 'KRA-0004',
                'supplier_id' => '2',
                'created_by' => '1',
                'status' => 'fulfilled',
                'created_at' => '2020-06-08 22:37:31.000',
                'updated_at' => '2020-06-18 22:20:09.130',
                'company_id' => '1',
                'comments' => NULL,
            ),
            3 => 
            array (
                'id' => '20002',
                'order_number' => 'KRA-0005',
                'supplier_id' => '2',
                'created_by' => '1',
                'status' => 'fulfilled',
                'created_at' => '2020-06-21 09:19:57.040',
                'updated_at' => '2020-06-22 01:25:15.580',
                'company_id' => '1',
                'comments' => 'This is a et',
            ),
            4 => 
            array (
                'id' => '20003',
                'order_number' => 'KRA-0006',
                'supplier_id' => '3',
                'created_by' => '1',
                'status' => 'fulfilled',
                'created_at' => '2020-06-21 22:52:31.257',
                'updated_at' => '2020-06-21 23:24:06.983',
                'company_id' => '1',
                'comments' => 'Items required urgently',
            ),
        ));
        
        
    }
}