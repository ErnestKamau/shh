<?php

use Illuminate\Database\Seeder;

class StockTransfersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('stock_transfers')->delete();
        
        
        
    }
}