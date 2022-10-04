<?php

use Illuminate\Database\Seeder;

class RequestTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('request_types')->delete();
        
        \DB::table('request_types')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Drawing of samples for laboratory analysis',
                'visible' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Performance of tests to ascertain goods identification',
                'visible' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Techinical verification of goods',
                'visible' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'Creation of technical specifications for procurement items',
                'visible' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            4 => 
            array (
                'id' => '5',
                'name' => 'Provision of technical advisory and consultancy services',
                'visible' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            5 => 
            array (
                'id' => '6',
                'name' => 'Verification of industrial manufacture processes',
                'visible' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            6 => 
            array (
                'id' => '7',
                'name' => 'Provision of document exermination services',
                'visible' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            7 => 
            array (
                'id' => '8',
                'name' => 'Interpretation of technical specfification of goods',
                'visible' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            8 => 
            array (
                'id' => '9',
                'name' => 'Solubility',
                'visible' => '0',
                'created_at' => '2020-07-14 15:28:02.877',
                'updated_at' => '2020-07-14 15:28:02.877',
            ),
            9 => 
            array (
                'id' => '10',
                'name' => 'Solubility',
                'visible' => '0',
                'created_at' => '2020-07-14 15:28:24.723',
                'updated_at' => '2020-07-14 15:28:24.723',
            ),
            10 => 
            array (
                'id' => '11',
                'name' => 'Solubility',
                'visible' => '0',
                'created_at' => '2020-07-14 15:29:00.173',
                'updated_at' => '2020-07-14 15:29:00.173',
            ),
            11 => 
            array (
                'id' => '12',
                'name' => 'Solubilty',
                'visible' => '0',
                'created_at' => '2020-07-14 15:33:53.967',
                'updated_at' => '2020-07-14 15:33:53.967',
            ),
        ));
        
        
    }
}