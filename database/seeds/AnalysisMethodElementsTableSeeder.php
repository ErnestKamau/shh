<?php

use Illuminate\Database\Seeder;

class AnalysisMethodElementsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('analysis_method_elements')->delete();
        
        \DB::table('analysis_method_elements')->insert(array (
            0 => 
            array (
                'id' => '1',
                'analysis_method_id' => '2',
                'analyte_id' => '7',
                'quantity' => '2.0',
                'active' => '1',
                'created_at' => '2020-06-26 13:16:05.137',
                'updated_at' => '2020-06-26 13:16:05.137',
                'company_id' => '1',
            ),
        ));
        
        
    }
}