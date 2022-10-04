<?php

use Illuminate\Database\Seeder;

class SampleAnalysisStagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sample_analysis_stages')->delete();
        
        \DB::table('sample_analysis_stages')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Drying',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => '2020-06-28 11:53:06.583',
                'company_id' => '1',
                'sample_workflow' => 'Samples In Lab',
                'level' => '1',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Weighing Room',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => '2020-06-28 18:26:54.143',
                'company_id' => '0',
                'sample_workflow' => 'Samples In Lab',
                'level' => '1',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Instrument Room 1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => '2020-06-28 18:26:17.703',
                'company_id' => '0',
                'sample_workflow' => 'Samples In Lab',
                'level' => '2',
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'Sample Reception',
                'active' => '1',
                'created_at' => '2020-04-24 00:42:17.957',
                'updated_at' => '2020-06-28 11:53:21.103',
                'company_id' => '1',
                'sample_workflow' => 'Samples Reception',
                'level' => '1',
            ),
            4 => 
            array (
                'id' => '10004',
                'name' => 'Instrument Room 2',
                'active' => '1',
                'created_at' => '2020-06-26 12:53:42.397',
                'updated_at' => '2020-06-28 18:26:26.843',
                'company_id' => '0',
                'sample_workflow' => 'Samples In Lab',
                'level' => '2',
            ),
            5 => 
            array (
                'id' => '10005',
                'name' => 'Instrument Room 3',
                'active' => '1',
                'created_at' => '2020-06-26 12:53:54.207',
                'updated_at' => '2020-06-28 18:26:36.183',
                'company_id' => '0',
                'sample_workflow' => 'Samples In Lab',
                'level' => '2',
            ),
            6 => 
            array (
                'id' => '10006',
                'name' => 'Preparation',
                'active' => '1',
                'created_at' => '2020-06-26 12:54:15.717',
                'updated_at' => '2020-06-28 18:26:44.877',
                'company_id' => '0',
                'sample_workflow' => 'Samples In Lab',
                'level' => '1',
            ),
            7 => 
            array (
                'id' => '10007',
                'name' => 'Sample Labeling',
                'active' => '1',
                'created_at' => '2020-06-28 13:10:31.780',
                'updated_at' => '2020-06-28 13:10:31.780',
                'company_id' => '1',
                'sample_workflow' => 'Samples Reception',
                'level' => '2',
            ),
            8 => 
            array (
                'id' => '10008',
                'name' => 'Cooling',
                'active' => '1',
                'created_at' => '2020-06-29 06:27:34.157',
                'updated_at' => '2020-06-29 06:27:34.157',
                'company_id' => '1',
                'sample_workflow' => 'Samples In Lab',
                'level' => '2',
            ),
            9 => 
            array (
                'id' => '20007',
                'name' => 'Chief Manager Review',
                'active' => '1',
                'created_at' => '2020-06-30 02:37:10.067',
                'updated_at' => '2020-07-14 08:05:53.983',
                'company_id' => '1',
                'sample_workflow' => 'Samples Request Review',
                'level' => '1',
            ),
            10 => 
            array (
                'id' => '20008',
                'name' => 'Unit Manager Review',
                'active' => '1',
                'created_at' => '2020-07-14 08:06:24.953',
                'updated_at' => '2020-07-14 08:23:21.717',
                'company_id' => '1',
                'sample_workflow' => 'Samples Request Review',
                'level' => '2',
            ),
        ));
        
        
    }
}