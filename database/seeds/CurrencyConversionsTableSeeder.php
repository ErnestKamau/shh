<?php

use Illuminate\Database\Seeder;

class CurrencyConversionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('currency_conversions')->delete();
        
        \DB::table('currency_conversions')->insert(array (
            0 => 
            array (
                'id' => '1',
                'currency_1' => '12',
                'currency_2' => '14',
                'ratio' => '100.0',
                'created_at' => '2020-08-03 07:40:48.243',
                'updated_at' => '2020-08-03 07:40:48.243',
                'inventory_location_id' => '3',
            ),
            1 => 
            array (
                'id' => '2',
                'currency_1' => '14',
                'currency_2' => '12',
                'ratio' => '0.001',
                'created_at' => '2020-08-05 11:49:46.860',
                'updated_at' => '2020-08-05 11:49:46.860',
                'inventory_location_id' => '3',
            ),
        ));
        
        
    }
}