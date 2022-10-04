<?php

use Illuminate\Database\Seeder;

class CompanyProductsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('company_products')->delete();
        
        \DB::table('company_products')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Metals',
                'crm_company_unit_id' => '3',
                'created_at' => '2020-06-28 04:47:13.973',
                'updated_at' => '2020-06-28 04:47:23.873',
                'active' => '1',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Pens',
                'crm_company_unit_id' => '1',
                'created_at' => '2020-06-28 05:49:44.297',
                'updated_at' => '2020-06-28 05:49:44.297',
                'active' => '1',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Water',
                'crm_company_unit_id' => '2',
                'created_at' => '2020-06-28 05:50:22.750',
                'updated_at' => '2020-06-28 05:50:22.750',
                'active' => '1',
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'Salt',
                'crm_company_unit_id' => '1',
                'created_at' => '2020-06-29 11:31:35.067',
                'updated_at' => '2020-06-29 11:31:35.067',
                'active' => '1',
            ),
            4 => 
            array (
                'id' => '10002',
                'name' => 'Alcoholic Beverage',
                'crm_company_unit_id' => '10003',
                'created_at' => '2020-06-30 08:25:09.927',
                'updated_at' => '2020-06-30 08:25:09.927',
                'active' => '1',
            ),
            5 => 
            array (
                'id' => '10003',
                'name' => 'Electronics',
                'crm_company_unit_id' => '10003',
                'created_at' => '2020-06-30 08:25:25.257',
                'updated_at' => '2020-06-30 08:25:25.257',
                'active' => '1',
            ),
        ));
        
        
    }
}