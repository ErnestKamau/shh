<?php

use Illuminate\Database\Seeder;

class SampleConditionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sample_conditions')->delete();
        
        \DB::table('sample_conditions')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Moist',
                'active' => '1',
                'sample_type_id' => '1',
                'created_at' => '2020-04-23 02:29:11.870',
                'updated_at' => '2020-04-23 02:29:11.870',
                'short_name' => NULL,
                'reporting_time' => NULL,
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Dry',
                'active' => '1',
                'sample_type_id' => '1',
                'created_at' => '2020-04-24 01:17:28.010',
                'updated_at' => '2020-04-24 01:17:35.210',
                'short_name' => NULL,
                'reporting_time' => '0',
            ),
            2 => 
            array (
                'id' => '10002',
                'name' => 'Wet',
                'active' => '1',
                'sample_type_id' => '1',
                'created_at' => '2020-05-14 04:17:40.140',
                'updated_at' => '2020-05-14 04:17:40.140',
                'short_name' => NULL,
                'reporting_time' => '0',
            ),
            3 => 
            array (
                'id' => '10003',
                'name' => 'Biochar Extracts',
                'active' => '1',
                'sample_type_id' => '1',
                'created_at' => '2020-06-26 13:00:02.283',
                'updated_at' => '2020-06-26 13:00:02.283',
                'short_name' => NULL,
                'reporting_time' => '0',
            ),
            4 => 
            array (
                'id' => '10004',
                'name' => 'Dry in Polythene',
                'active' => '1',
                'sample_type_id' => '1',
                'created_at' => '2020-06-26 13:00:22.447',
                'updated_at' => '2020-06-26 13:00:22.447',
                'short_name' => NULL,
                'reporting_time' => '0',
            ),
            5 => 
            array (
                'id' => '10005',
                'name' => 'Sieved',
                'active' => '1',
                'sample_type_id' => '1',
                'created_at' => '2020-06-26 13:00:48.163',
                'updated_at' => '2020-06-26 13:00:48.163',
                'short_name' => NULL,
                'reporting_time' => '0',
            ),
            6 => 
            array (
                'id' => '10006',
                'name' => 'Dry',
                'active' => '1',
                'sample_type_id' => '10001',
                'created_at' => '2020-06-29 11:27:09.003',
                'updated_at' => '2020-06-29 11:27:09.003',
                'short_name' => NULL,
                'reporting_time' => '0',
            ),
            7 => 
            array (
                'id' => '10007',
                'name' => 'Moist',
                'active' => '1',
                'sample_type_id' => '10001',
                'created_at' => '2020-06-29 11:27:17.043',
                'updated_at' => '2020-06-29 11:27:17.043',
                'short_name' => NULL,
                'reporting_time' => '0',
            ),
            8 => 
            array (
                'id' => '20006',
                'name' => 'Wet',
                'active' => '1',
                'sample_type_id' => '10001',
                'created_at' => '2020-06-30 08:29:14.900',
                'updated_at' => '2020-06-30 08:29:14.900',
                'short_name' => NULL,
                'reporting_time' => '0',
            ),
            9 => 
            array (
                'id' => '20007',
                'name' => 'Dirty',
                'active' => '1',
                'sample_type_id' => '3',
                'created_at' => '2020-07-21 15:36:30.433',
                'updated_at' => '2020-07-21 15:36:30.433',
                'short_name' => NULL,
                'reporting_time' => '0',
            ),
            10 => 
            array (
                'id' => '20008',
                'name' => 'Clear',
                'active' => '1',
                'sample_type_id' => '3',
                'created_at' => '2020-07-21 15:36:48.387',
                'updated_at' => '2020-07-21 15:36:48.387',
                'short_name' => NULL,
                'reporting_time' => '0',
            ),
        ));
        
        
    }
}