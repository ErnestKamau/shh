<?php

use Illuminate\Database\Seeder;

class InventorySupplierRatingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_supplier_ratings')->delete();
        
        \DB::table('inventory_supplier_ratings')->insert(array (
            0 => 
            array (
                'id' => '1',
                'supplier_id' => '3',
                'inventory_item_id' => '20046',
                'rating' => '8',
                'title' => 'Excellent',
                'comments' => 'The products meet the expectations',
                'rating_by' => '1',
                'created_at' => '2020-06-22 00:47:48.373',
                'updated_at' => '2020-06-22 00:47:48.373',
            ),
            1 => 
            array (
                'id' => '2',
                'supplier_id' => '3',
                'inventory_item_id' => '20047',
                'rating' => '7',
                'title' => 'Okay',
                'comments' => 'Not Bad',
                'rating_by' => '1',
                'created_at' => '2020-06-22 01:20:43.733',
                'updated_at' => '2020-06-22 01:20:43.733',
            ),
            2 => 
            array (
                'id' => '10002',
                'supplier_id' => '2',
                'inventory_item_id' => '6',
                'rating' => '7',
                'title' => 'Good',
                'comments' => 'The items are in good quality.',
                'rating_by' => '1',
                'created_at' => '2020-06-22 10:30:53.227',
                'updated_at' => '2020-06-22 10:30:53.227',
            ),
            3 => 
            array (
                'id' => '10003',
                'supplier_id' => '2',
                'inventory_item_id' => '30052',
                'rating' => '8',
                'title' => 'suppy',
                'comments' => 'Good',
                'rating_by' => '1',
                'created_at' => '2020-07-03 13:10:18.123',
                'updated_at' => '2020-07-03 13:10:18.123',
            ),
            4 => 
            array (
                'id' => '20003',
                'supplier_id' => '2',
                'inventory_item_id' => '5',
                'rating' => '9',
                'title' => 'Goods are excellent',
                'comments' => 'Goods were in good condition and of high quality',
                'rating_by' => '1',
                'created_at' => '2020-07-06 12:01:48.340',
                'updated_at' => '2020-07-06 12:01:48.340',
            ),
        ));
        
        
    }
}