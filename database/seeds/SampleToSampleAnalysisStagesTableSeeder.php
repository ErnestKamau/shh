<?php

use Illuminate\Database\Seeder;

class SampleToSampleAnalysisStagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sample_to_sample_analysis_stages')->delete();
        
        \DB::table('sample_to_sample_analysis_stages')->insert(array (
            0 => 
            array (
                'id' => '1',
                'sample_type_id' => '1',
                'sample_analysis_stage_id' => '2',
                'created_at' => '2020-04-24 01:41:35.707',
                'updated_at' => '2020-06-28 18:31:38.627',
                'active' => '1',
            ),
            1 => 
            array (
                'id' => '2',
                'sample_type_id' => '1',
                'sample_analysis_stage_id' => '4',
                'created_at' => '2020-04-24 02:05:05.507',
                'updated_at' => '2020-06-28 18:31:44.327',
                'active' => '1',
            ),
            2 => 
            array (
                'id' => '10002',
                'sample_type_id' => '10001',
                'sample_analysis_stage_id' => '1',
                'created_at' => '2020-06-29 11:27:32.860',
                'updated_at' => '2020-06-29 11:27:32.860',
                'active' => '1',
            ),
            3 => 
            array (
                'id' => '10003',
                'sample_type_id' => '10001',
                'sample_analysis_stage_id' => '10006',
                'created_at' => '2020-06-29 11:27:46.650',
                'updated_at' => '2020-06-29 11:27:46.650',
                'active' => '1',
            ),
            4 => 
            array (
                'id' => '20002',
                'sample_type_id' => '3',
                'sample_analysis_stage_id' => '2',
                'created_at' => '2020-07-21 15:37:02.990',
                'updated_at' => '2020-07-21 15:37:02.990',
                'active' => '1',
            ),
            5 => 
            array (
                'id' => '20003',
                'sample_type_id' => '3',
                'sample_analysis_stage_id' => '4',
                'created_at' => '2020-07-21 15:37:18.010',
                'updated_at' => '2020-07-21 15:37:18.010',
                'active' => '1',
            ),
            6 => 
            array (
                'id' => '20004',
                'sample_type_id' => '3',
                'sample_analysis_stage_id' => '3',
                'created_at' => '2020-07-21 15:37:31.700',
                'updated_at' => '2020-07-21 15:37:31.700',
                'active' => '1',
            ),
        ));
        
        
    }
}