<?php

use Illuminate\Database\Seeder;

class AnalysisTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('analysis_types')->delete();
        
        \DB::table('analysis_types')->insert(array (
            0 => 
            array (
                'id' => '1',
                'code' => 'CSA',
                'name' => 'Complete Soil Analysis',
                'description' => 'Complete Soil Analysis',
                'sample_type_id' => '1',
                'lab_id' => '1',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-04-22 09:47:46.873',
                'updated_at' => '2020-06-29 02:33:09.570',
                'short_name' => NULL,
                'reporting_time' => '7',
            ),
            1 => 
            array (
                'id' => '2',
                'code' => 'BSA',
                'name' => 'Basic Soil Analysis',
                'description' => 'Basic Soil Analysis',
                'sample_type_id' => '1',
                'lab_id' => '1',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-05-17 19:41:41.303',
                'updated_at' => '2020-06-26 12:51:38.103',
                'short_name' => NULL,
                'reporting_time' => '7',
            ),
            2 => 
            array (
                'id' => '3',
                'code' => 'CFA001',
                'name' => 'Complete Feed Analysis',
                'description' => 'Feed Ananlysis',
                'sample_type_id' => '10',
                'lab_id' => '2',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-06-29 06:25:32.707',
                'updated_at' => '2020-06-29 06:25:32.707',
                'short_name' => 'CFA',
                'reporting_time' => '10',
            ),
            3 => 
            array (
                'id' => '4',
                'code' => '9907',
                'name' => '9907 - Cane Sugar',
                'description' => 'Sugar Cane',
                'sample_type_id' => '10001',
                'lab_id' => '1',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-06-29 11:13:09.840',
                'updated_at' => '2020-06-29 11:13:09.840',
                'short_name' => '9907',
                'reporting_time' => '7',
            ),
            4 => 
            array (
                'id' => '5',
                'code' => 'SM',
                'name' => 'Salt Moisture',
                'description' => 'Check soil moisture',
                'sample_type_id' => '10001',
                'lab_id' => '1',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-06-29 11:19:01.847',
                'updated_at' => '2020-06-29 17:45:50.787',
                'short_name' => NULL,
                'reporting_time' => '9',
            ),
            5 => 
            array (
                'id' => '10003',
                'code' => 'ABV-2208.90.90',
                'name' => '2208.90.90 - Alcoholic Beverage 7.2%',
                'description' => 'Check whether the alcohol is at 7.2%',
                'sample_type_id' => '10001',
                'lab_id' => '1',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-06-30 08:03:06.507',
                'updated_at' => '2020-06-30 08:03:06.507',
                'short_name' => '2208.90.90',
                'reporting_time' => '6',
            ),
            6 => 
            array (
                'id' => '10004',
                'code' => 'FC001',
                'name' => 'Fecal coliform',
                'description' => 'Fecal coliform',
                'sample_type_id' => '3',
                'lab_id' => '1',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-07-21 15:28:28.100',
                'updated_at' => '2020-07-21 15:28:28.100',
                'short_name' => NULL,
                'reporting_time' => '6',
            ),
        ));
        
        
    }
}