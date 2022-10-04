<?php

use Illuminate\Database\Seeder;

class CrmCompanyUnitsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('crm_company_units')->delete();
        
        \DB::table('crm_company_units')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Administration',
                'company_id' => '1',
                'crm_customer_id' => '4',
                'active' => '1',
                'created_at' => '2020-04-30 09:13:41.803',
                'updated_at' => '2020-04-30 09:14:42.803',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Finance',
                'company_id' => '1',
                'crm_customer_id' => '4',
                'active' => '1',
                'created_at' => '2020-04-30 12:38:14.937',
                'updated_at' => '2020-06-28 04:46:50.460',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'IT',
                'company_id' => '1',
                'crm_customer_id' => '4',
                'active' => '1',
                'created_at' => '2020-06-28 04:47:01.767',
                'updated_at' => '2020-06-28 04:47:01.767',
            ),
            3 => 
            array (
                'id' => '10003',
                'name' => 'EGMS Office',
                'company_id' => '1',
                'crm_customer_id' => '20003',
                'active' => '1',
                'created_at' => '2020-06-30 08:24:02.013',
                'updated_at' => '2020-06-30 08:24:02.013',
            ),
        ));
        
        
    }
}