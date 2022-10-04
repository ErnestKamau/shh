<?php

use Illuminate\Database\Seeder;

class LabsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('labs')->delete();
        
        \DB::table('labs')->insert(array (
            0 => 
            array (
                'id' => '1',
                'code' => 'OL001',
                'name' => 'Organic Lab',
                'address' => '8543
Ronald Ngala',
                'location' => 'Times Tower, Nairobi, Kenya',
                'fax' => '0727500128',
                'email' => 'david.kimari@outlook.com',
                'website' => 'https://kra.go.ke/',
                'company_id' => '1',
                'is_external' => '0',
                'phone1' => '0727500128',
                'phone2' => NULL,
                'phone3' => NULL,
                'active' => '1',
                'created_at' => '2020-04-14 20:29:30.847',
                'updated_at' => '2020-07-02 12:52:13.373',
            ),
            1 => 
            array (
                'id' => '2',
                'code' => 'Lab002',
                'name' => 'Food Lab',
                'address' => '8543
Ronald Ngala',
                'location' => 'Times Tower, Nairobi, Kenya',
                'fax' => '0727500128',
                'email' => 'david.kimari@outlook.com',
                'website' => 'https://kra.go.ke/',
                'company_id' => '1',
                'is_external' => '0',
                'phone1' => '+254727500128',
                'phone2' => '+254727500128',
                'phone3' => NULL,
                'active' => '1',
                'created_at' => '2020-04-24 00:46:59.620',
                'updated_at' => '2020-06-26 12:45:16.850',
            ),
            2 => 
            array (
                'id' => '20002',
                'code' => 'Lab003',
                'name' => 'In-Organic Lab',
                'address' => 'Times Tower',
                'location' => 'Lab department',
                'fax' => NULL,
                'email' => 'david.kimari@outlook.com',
                'website' => 'https://kra.go.ke/',
                'company_id' => '1',
                'is_external' => '0',
                'phone1' => '+254727500128',
                'phone2' => NULL,
                'phone3' => NULL,
                'active' => '1',
                'created_at' => '2020-06-26 12:46:16.097',
                'updated_at' => '2020-06-26 12:46:16.097',
            ),
        ));
        
        
    }
}