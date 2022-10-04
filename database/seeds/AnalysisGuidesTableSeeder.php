<?php

use Illuminate\Database\Seeder;

class AnalysisGuidesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('analysis_guides')->delete();
        
        \DB::table('analysis_guides')->insert(array (
            0 => 
            array (
                'id' => '3',
                'guide_name' => 'Low',
                'analyte_id' => '10',
                'analysis_type_id' => '1',
                'value' => '0.59999999999999998',
                'comments' => 'Very Low',
                'recommendations' => 'Recommend moving to HS Code',
                'created_at' => '2020-07-13 10:28:50.963',
                'updated_at' => '2020-07-13 12:30:04.833',
            ),
            1 => 
            array (
                'id' => '4',
                'guide_name' => 'High',
                'analyte_id' => '10',
                'analysis_type_id' => '1',
                'value' => '0.93000000000000005',
                'comments' => 'Is high',
                'recommendations' => 'Is higher',
                'created_at' => '2020-07-13 12:30:49.233',
                'updated_at' => '2020-07-13 12:30:49.233',
            ),
        ));
        
        
    }
}