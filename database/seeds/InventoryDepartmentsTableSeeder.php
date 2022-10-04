<?php

use Illuminate\Database\Seeder;

class InventoryDepartmentsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_departments')->delete();
        
        \DB::table('inventory_departments')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Laboratory',
                'created_at' => '2020-04-17 00:29:36.620',
                'updated_at' => '2020-04-17 00:57:20.827',
                'company_id' => '1',
                'module' => 'organizational',
                'active' => '1',
                'location_id' => '3',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Production',
                'created_at' => '2020-04-17 00:33:20.627',
                'updated_at' => '2020-04-17 00:33:20.627',
                'company_id' => '1',
                'module' => 'organizational',
                'active' => '1',
                'location_id' => '3',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Administration',
                'created_at' => '2020-04-17 00:57:29.297',
                'updated_at' => '2020-04-17 00:57:29.297',
                'company_id' => '1',
                'module' => 'organizational',
                'active' => '1',
                'location_id' => '3',
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'Stock Taking',
                'created_at' => '2020-06-19 02:45:24.587',
                'updated_at' => '2020-06-22 10:19:35.880',
                'company_id' => '1',
                'module' => 'inventory',
                'active' => '1',
                'location_id' => '3',
            ),
            4 => 
            array (
                'id' => '5',
                'name' => 'Item Disposal',
                'created_at' => '2020-06-19 02:45:55.353',
                'updated_at' => '2020-07-02 06:08:52.653',
                'company_id' => '1',
                'module' => 'inventory',
                'active' => '1',
                'location_id' => '3',
            ),
            5 => 
            array (
                'id' => '6',
                'name' => 'Returned 2 Store',
                'created_at' => '2020-06-21 16:37:24.583',
                'updated_at' => '2020-06-21 16:37:24.583',
                'company_id' => '1',
                'module' => 'inventory',
                'active' => '1',
                'location_id' => '3',
            ),
            6 => 
            array (
                'id' => '10006',
                'name' => 'Procurement',
                'created_at' => '2020-06-21 16:37:24.583',
                'updated_at' => '2020-06-21 16:37:24.583',
                'company_id' => '1',
                'module' => 'organizational',
                'active' => '1',
                'location_id' => '3',
            ),
            7 => 
            array (
                'id' => '10007',
                'name' => 'Security',
                'created_at' => '2020-08-04 22:32:50.707',
                'updated_at' => '2020-08-04 22:32:50.707',
                'company_id' => '1',
                'module' => 'organizational',
                'active' => '1',
                'location_id' => '3',
            ),
            8 => 
            array (
                'id' => '10008',
                'name' => 'Administration',
                'created_at' => '2020-08-06 18:24:55.270',
                'updated_at' => '2020-08-06 18:24:55.270',
                'company_id' => '1',
                'module' => 'organizational',
                'active' => '1',
                'location_id' => '5',
            ),
            9 => 
            array (
                'id' => '10009',
                'name' => 'Laboratory',
                'created_at' => '2020-08-06 18:25:03.240',
                'updated_at' => '2020-08-06 18:25:03.240',
                'company_id' => '1',
                'module' => 'organizational',
                'active' => '1',
                'location_id' => '5',
            ),
            10 => 
            array (
                'id' => '10010',
                'name' => 'Production',
                'created_at' => '2020-08-06 18:25:11.777',
                'updated_at' => '2020-08-06 18:25:11.777',
                'company_id' => '1',
                'module' => 'organizational',
                'active' => '1',
                'location_id' => '5',
            ),
            11 => 
            array (
                'id' => '10011',
                'name' => 'Procurement',
                'created_at' => '2020-08-06 18:25:23.623',
                'updated_at' => '2020-08-06 18:25:23.623',
                'company_id' => '1',
                'module' => 'organizational',
                'active' => '1',
                'location_id' => '5',
            ),
        ));
        
        
    }
}