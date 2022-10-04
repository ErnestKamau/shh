<?php

use Illuminate\Database\Seeder;

class SamplePointsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sample_points')->delete();
        
        \DB::table('sample_points')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Section 1',
                'crm_company_unit_id' => '1',
                'created_at' => '2020-06-28 04:38:29.583',
                'updated_at' => '2020-06-28 04:38:29.583',
                'active' => '1',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Section 2',
                'crm_company_unit_id' => '2',
                'created_at' => '2020-06-28 04:38:56.177',
                'updated_at' => '2020-06-28 05:49:53.503',
                'active' => '1',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Section 3',
                'crm_company_unit_id' => '3',
                'created_at' => '2020-06-28 05:50:04.040',
                'updated_at' => '2020-06-28 05:50:04.040',
                'active' => '1',
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'Port',
                'crm_company_unit_id' => '1',
                'created_at' => '2020-06-29 11:31:21.933',
                'updated_at' => '2020-06-29 11:31:21.933',
                'active' => '1',
            ),
            4 => 
            array (
                'id' => '10002',
                'name' => 'Mombasa Port',
                'crm_company_unit_id' => '10003',
                'created_at' => '2020-06-30 08:24:25.880',
                'updated_at' => '2020-06-30 08:24:25.880',
                'active' => '1',
            ),
            5 => 
            array (
                'id' => '10003',
                'name' => 'JKIA',
                'crm_company_unit_id' => '10003',
                'created_at' => '2020-06-30 08:24:39.997',
                'updated_at' => '2020-06-30 08:24:39.997',
                'active' => '1',
            ),
        ));
        
        
    }
}