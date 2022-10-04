<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('roles')->delete();
        
        \DB::table('roles')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Admin',
                'description' => 'Administrator',
                'created_at' => '2020-06-29 13:00:34.963',
                'updated_at' => '2020-07-21 14:03:25.433',
                'company_id' => '1',
                'permissions' => '{"Laboratory":{"permission":"true"},"components":{"Samples En-Route":{"Add":"false","Edit":"true","View":"true","Delete":"true"},"Samples Reception":{"Add":"true","Edit":"true","View":"false","Delete":"true"},"Samples Request Review":{"Add":"false","Edit":"true","View":"true","Delete":"false"},"Samples In Lab":{"Add":"false","Edit":"false","View":"true","Delete":"false"},"Sample Verification":{"Add":"false","Edit":"false","View":"false","Delete":"true"},"Sample Approval":{"Add":"true","Edit":"true","View":"false","Delete":"true"},"Payments":{"Add":"false","Edit":"true","View":"false","Delete":"true"},"Reports":{"Add":"false","Edit":"false","View":"true","Delete":"false"},"Analytes":{"Add":"false","Edit":"false","View":"true","Delete":"false"},"Labs":{"Add":"false","Edit":"true","View":"false","Delete":"true"},"Sample-Types":{"Add":"false","Edit":"false","View":"false","Delete":"true"},"Reporting-Units":{"Add":"true","Edit":"false","View":"false","Delete":"false"},"Methods":{"Add":"false","Edit":"true","View":"true","Delete":"true"},"Sample-Tracking-Stages":{"Add":"false","Edit":"true","View":"false","Delete":"false"}},"Inventory":{"permission":"false"},"Equipment":{"permission":"false"},"Documents":{"permission":"false"},"CRM":{"permission":"false"},"Personnel":{"permission":"false"}}',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Manager',
                'description' => 'Incharge of management',
                'created_at' => '2020-06-29 13:02:47.270',
                'updated_at' => '2020-06-29 13:02:47.270',
                'company_id' => '1',
                'permissions' => NULL,
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Lab',
                'description' => 'Laboratory',
                'created_at' => '2020-06-29 13:03:19.240',
                'updated_at' => '2020-06-29 13:03:19.240',
                'company_id' => '1',
                'permissions' => NULL,
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'HR',
                'description' => 'Human Resource',
                'created_at' => '2020-06-29 13:04:13.810',
                'updated_at' => '2020-06-29 13:04:13.810',
                'company_id' => '1',
                'permissions' => NULL,
            ),
            4 => 
            array (
                'id' => '10002',
                'name' => 'Analyst',
                'description' => 'Analyst',
                'created_at' => '2020-07-14 08:26:01.310',
                'updated_at' => '2020-07-14 08:26:01.310',
                'company_id' => '1',
                'permissions' => NULL,
            ),
            5 => 
            array (
                'id' => '10004',
                'name' => 'Unit Lab Manager',
                'description' => 'Unit Lab Manager',
                'created_at' => '2020-07-14 17:13:15.657',
                'updated_at' => '2020-07-14 17:13:15.657',
                'company_id' => '1',
                'permissions' => NULL,
            ),
        ));
        
        
    }
}