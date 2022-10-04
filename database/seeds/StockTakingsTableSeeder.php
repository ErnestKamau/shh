<?php

use Illuminate\Database\Seeder;

class StockTakingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('stock_takings')->delete();
        
        \DB::table('stock_takings')->insert(array (
            0 => 
            array (
                'id' => '1',
                'description' => 'This is it',
                'created_by' => '7',
                'updated_by' => NULL,
                'completed_at' => NULL,
                'status' => 'In Preparation',
                'stores' => '3,10003,10005',
                'store_names' => 'Organic,Food Store,Lab Storage',
                'approved_by' => NULL,
                'inventory_location_id' => '0',
                'created_at' => '2020-08-04 23:56:34.297',
                'updated_at' => '2020-08-05 00:13:07.517',
                'code' => 'ST-0002',
            ),
            1 => 
            array (
                'id' => '2',
                'description' => 'The stock taking process has begun.',
                'created_by' => '7',
                'updated_by' => NULL,
                'completed_at' => NULL,
                'status' => 'In Preparation',
                'stores' => '2,3,10003,10005',
                'store_names' => 'In-Organic,Organic,Food Store,Lab Storage',
                'approved_by' => NULL,
                'inventory_location_id' => '0',
                'created_at' => '2020-08-05 00:06:22.783',
                'updated_at' => '2020-08-05 07:48:55.837',
                'code' => 'ST-0001',
            ),
        ));
        
        
    }
}