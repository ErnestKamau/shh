<?php

use Illuminate\Database\Seeder;

class CrmCustomerContactsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('crm_customer_contacts')->delete();
        
        \DB::table('crm_customer_contacts')->insert(array (
            0 => 
            array (
                'id' => '1',
                'first_name' => 'David',
                'middle_name' => 'Mwangi',
                'last_name' => 'Kimari',
                'job_occupation' => 'CTO',
                'unit_name' => 'Administration,Finance',
                'email' => 'david.kimari@outlook.com',
                'telephone' => '+254727500128',
                'mobile' => '+254727500128',
                'receive_price_list' => '0',
                'receive_invoice' => '0',
                'receive_report' => '1',
                'company_id' => '1',
                'crm_customer_id' => '4',
                'active' => '1',
                'created_at' => '2020-04-30 12:35:42.607',
                'updated_at' => '2020-07-18 02:34:16.880',
            ),
            1 => 
            array (
                'id' => '2',
                'first_name' => 'Colman',
                'middle_name' => 'Mwakio',
                'last_name' => 'K',
                'job_occupation' => 'CEO',
                'unit_name' => 'Administration',
                'email' => 'cmwakio@gmail.com',
                'telephone' => '0987654',
                'mobile' => '09876543',
                'receive_price_list' => '1',
                'receive_invoice' => '1',
                'receive_report' => '1',
                'company_id' => '1',
                'crm_customer_id' => '4',
                'active' => '1',
                'created_at' => '2020-07-18 06:12:19.740',
                'updated_at' => '2020-07-18 06:12:19.740',
            ),
            2 => 
            array (
                'id' => '3',
                'first_name' => 'Abel',
                'middle_name' => 'Ondati',
                'last_name' => 'm',
                'job_occupation' => 'CTO',
                'unit_name' => 'Administration',
                'email' => 'ondatio@gmail.com',
                'telephone' => '123455321',
                'mobile' => '1234532234',
                'receive_price_list' => '1',
                'receive_invoice' => '1',
                'receive_report' => '1',
                'company_id' => '1',
                'crm_customer_id' => '4',
                'active' => '1',
                'created_at' => '2020-07-21 13:29:23.017',
                'updated_at' => '2020-07-21 13:29:23.017',
            ),
        ));
        
        
    }
}