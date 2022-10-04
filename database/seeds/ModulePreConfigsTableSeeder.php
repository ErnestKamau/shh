<?php

use Illuminate\Database\Seeder;

class ModulePreConfigsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('module_pre_configs')->delete();
        
        \DB::table('module_pre_configs')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Primary',
                'type' => 'Educational Levels',
                'description' => 'Primary school level',
                'created_at' => '2020-07-20 00:17:46.660',
                'updated_at' => '2020-07-20 00:17:46.660',
                'module' => 'Personnel-Management',
                'inventory_location_id' => '3',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Secondary',
                'type' => 'Educational Levels',
                'description' => 'Secondary school level.',
                'created_at' => '2020-07-20 00:19:11.900',
                'updated_at' => '2020-07-20 00:24:25.357',
                'module' => 'Personnel-Management',
                'inventory_location_id' => '3',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'University',
                'type' => 'Educational Levels',
                'description' => 'University level',
                'created_at' => '2020-07-20 00:25:16.787',
                'updated_at' => '2020-07-20 00:25:16.787',
                'module' => 'Personnel-Management',
                'inventory_location_id' => '3',
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'Analyst',
                'type' => 'Job Description',
                'description' => NULL,
                'created_at' => '2020-07-20 00:37:51.783',
                'updated_at' => '2020-07-20 00:37:51.783',
                'module' => 'Personnel-Management',
                'inventory_location_id' => '3',
            ),
            4 => 
            array (
                'id' => '5',
                'name' => 'Lab Manager',
                'type' => 'Job Description',
                'description' => NULL,
                'created_at' => '2020-07-20 00:38:09.380',
                'updated_at' => '2020-07-20 00:38:09.380',
                'module' => 'Personnel-Management',
                'inventory_location_id' => '3',
            ),
            5 => 
            array (
                'id' => '6',
                'name' => 'Chief Manager',
                'type' => 'Job Description',
                'description' => NULL,
                'created_at' => '2020-07-20 00:38:17.597',
                'updated_at' => '2020-07-20 00:38:17.597',
                'module' => 'Personnel-Management',
                'inventory_location_id' => '3',
            ),
            6 => 
            array (
                'id' => '7',
                'name' => 'Mr.',
                'type' => 'Designation',
                'description' => NULL,
                'created_at' => '2020-07-20 00:38:29.150',
                'updated_at' => '2020-07-20 00:38:29.150',
                'module' => 'Personnel-Management',
                'inventory_location_id' => '3',
            ),
            7 => 
            array (
                'id' => '8',
                'name' => 'Mrs',
                'type' => 'Designation',
                'description' => NULL,
                'created_at' => '2020-07-20 00:38:35.940',
                'updated_at' => '2020-07-20 00:38:35.940',
                'module' => 'Personnel-Management',
                'inventory_location_id' => '3',
            ),
            8 => 
            array (
                'id' => '9',
                'name' => 'Ms.',
                'type' => 'Designation',
                'description' => NULL,
                'created_at' => '2020-07-20 00:38:47.230',
                'updated_at' => '2020-07-20 00:38:47.230',
                'module' => 'Personnel-Management',
                'inventory_location_id' => '3',
            ),
            9 => 
            array (
                'id' => '10',
                'name' => 'Ph.D.',
                'type' => 'Designation',
                'description' => NULL,
                'created_at' => '2020-07-20 00:40:03.037',
                'updated_at' => '2020-07-20 00:40:03.037',
                'module' => 'Personnel-Management',
                'inventory_location_id' => '3',
            ),
            10 => 
            array (
                'id' => '11',
                'name' => 'Dr.',
                'type' => 'Designation',
                'description' => NULL,
                'created_at' => '2020-07-20 00:40:09.157',
                'updated_at' => '2020-07-20 00:40:09.157',
                'module' => 'Personnel-Management',
                'inventory_location_id' => '3',
            ),
            11 => 
            array (
                'id' => '12',
                'name' => 'USD',
                'type' => 'Currency',
                'description' => 'United States Dollars',
                'created_at' => '2020-08-03 06:13:16.337',
                'updated_at' => '2020-08-03 06:13:16.337',
                'module' => 'Inventory-Management',
                'inventory_location_id' => '3',
            ),
            12 => 
            array (
                'id' => '14',
                'name' => 'KES',
                'type' => 'Currency',
                'description' => 'Kenya Shillings',
                'created_at' => '2020-08-03 06:27:03.020',
                'updated_at' => '2020-08-03 06:27:03.020',
                'module' => 'Inventory-Management',
                'inventory_location_id' => '3',
            ),
        ));
        
        
    }
}