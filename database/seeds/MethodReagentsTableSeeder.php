<?php

use Illuminate\Database\Seeder;

class MethodReagentsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('method_reagents')->delete();
        
        \DB::table('method_reagents')->insert(array (
            0 => 
            array (
                'id' => '2',
                'inventory_sub_category_id' => '20035',
                'method_id' => '1',
                'reporting_unit' => 'g/kg',
                'quantity' => '34.0',
                'created_at' => '2020-07-31 21:11:13.550',
                'updated_at' => '2020-07-31 21:11:13.550',
            ),
        ));
        
        
    }
}