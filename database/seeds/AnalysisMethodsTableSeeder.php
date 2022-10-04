<?php

use Illuminate\Database\Seeder;

class AnalysisMethodsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('analysis_methods')->delete();
        
        \DB::table('analysis_methods')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Spectroscopy',
                'code' => 'SP',
                'company_id' => '1',
                'description' => 'Spectroscopy',
                'active' => '1',
                'created_at' => '2020-04-22 09:13:08.420',
                'updated_at' => '2020-06-26 13:10:25.367',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Kjeldahl',
                'code' => 'KJ',
                'company_id' => '1',
                'description' => 'Kjeldahl',
                'active' => '1',
                'created_at' => '2020-06-26 13:10:49.127',
                'updated_at' => '2020-06-26 13:13:06.067',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Calculated',
                'code' => 'CA',
                'company_id' => '1',
                'description' => 'Calculated',
                'active' => '1',
                'created_at' => '2020-06-26 13:11:08.683',
                'updated_at' => '2020-06-26 13:11:08.683',
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'Colorimetric',
                'code' => 'CO',
                'company_id' => '1',
                'description' => 'Colorimetric',
                'active' => '1',
                'created_at' => '2020-06-26 13:11:31.150',
                'updated_at' => '2020-06-26 13:11:31.150',
            ),
            4 => 
            array (
                'id' => '5',
                'name' => 'Gravimetric',
                'code' => 'GR',
                'company_id' => '1',
                'description' => 'Gravimetric',
                'active' => '1',
                'created_at' => '2020-06-26 13:11:49.217',
                'updated_at' => '2020-06-26 13:11:49.217',
            ),
            5 => 
            array (
                'id' => '6',
                'name' => 'Hydrometer',
                'code' => 'HY',
                'company_id' => '1',
                'description' => 'Hydrometer',
                'active' => '1',
                'created_at' => '2020-06-26 13:12:04.040',
                'updated_at' => '2020-06-26 13:12:04.040',
            ),
            6 => 
            array (
                'id' => '7',
                'name' => 'Potentiometric',
                'code' => 'PO',
                'company_id' => '1',
                'description' => 'Potentiometric',
                'active' => '1',
                'created_at' => '2020-06-26 13:12:25.930',
                'updated_at' => '2020-06-26 13:12:25.930',
            ),
            7 => 
            array (
                'id' => '8',
                'name' => 'Titrimetric',
                'code' => 'TI',
                'company_id' => '1',
                'description' => 'Titrimetric',
                'active' => '1',
                'created_at' => '2020-06-26 13:12:48.753',
                'updated_at' => '2020-06-26 13:12:48.753',
            ),
            8 => 
            array (
                'id' => '9',
                'name' => 'N/A',
                'code' => 'NA',
                'company_id' => '1',
                'description' => 'Not Applicable',
                'active' => '1',
                'created_at' => '2020-06-29 10:56:41.563',
                'updated_at' => '2020-06-29 10:56:41.563',
            ),
        ));
        
        
    }
}