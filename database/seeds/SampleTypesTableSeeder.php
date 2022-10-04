<?php

use Illuminate\Database\Seeder;

class SampleTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sample_types')->delete();
        
        \DB::table('sample_types')->insert(array (
            0 => 
            array (
                'id' => '1',
                'code' => 'SA',
                'name' => 'Soil Analysis',
                'description' => '-',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => '2020-04-24 00:22:46.103',
            ),
            1 => 
            array (
                'id' => '2',
                'code' => 'PA        ',
                'name' => 'Plant Analysis',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 => 
            array (
                'id' => '3',
                'code' => 'WA        ',
                'name' => 'Water Analysis',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            3 => 
            array (
                'id' => '4',
                'code' => 'FA        ',
                'name' => 'Fertiliser Analysis',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            4 => 
            array (
                'id' => '5',
                'code' => 'HY        ',
                'name' => 'Hydroponics Analysis',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            5 => 
            array (
                'id' => '6',
                'code' => 'PR        ',
                'name' => 'Pesticide Residues',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            6 => 
            array (
                'id' => '7',
                'code' => 'PT        ',
                'name' => 'Pathology Screen',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            7 => 
            array (
                'id' => '8',
                'code' => 'CM        ',
                'name' => 'Manure Compost Analysis',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            8 => 
            array (
                'id' => '9',
                'code' => 'SGF       ',
                'name' => 'Soil Grown Flowers',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            9 => 
            array (
                'id' => '10',
                'code' => 'AF        ',
                'name' => 'Feed Analysis',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            10 => 
            array (
                'id' => '11',
                'code' => 'NEM       ',
                'name' => 'Nematodes Analysis',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            11 => 
            array (
                'id' => '13',
                'code' => 'NM        ',
                'name' => 'Nutrient Mapping',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            12 => 
            array (
                'id' => '14',
                'code' => 'MN        ',
                'name' => 'Mineral Analysis',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            13 => 
            array (
                'id' => '15',
                'code' => 'NEMA      ',
                'name' => 'NEMA Analysis',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            14 => 
            array (
                'id' => '16',
                'code' => 'LC        ',
                'name' => 'Liquid Compost Extract',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            15 => 
            array (
                'id' => '17',
                'code' => 'CA        ',
                'name' => 'Chemical Analysis',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            16 => 
            array (
                'id' => '18',
                'code' => 'SP        ',
                'name' => 'Special Projects',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            17 => 
            array (
                'id' => '19',
                'code' => 'LI',
                'name' => 'Lime Analysis',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            18 => 
            array (
                'id' => '20',
                'code' => 'ID',
                'name' => 'Insect Identification',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            19 => 
            array (
                'id' => '21',
                'code' => 'FS',
                'name' => 'Food Safety',
                'description' => '',
                'company_id' => '1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            20 => 
            array (
                'id' => '10001',
                'code' => 'MA',
                'name' => 'Material Analysis',
                'description' => 'A test for materials',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-06-29 11:10:09.033',
                'updated_at' => '2020-06-29 11:10:09.033',
            ),
        ));
        
        
    }
}