<?php

use Illuminate\Database\Seeder;

class StockTakingSheetsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('stock_taking_sheets')->delete();
        
        
        
    }
}