<?php

use Illuminate\Database\Seeder;

class CrmCustomersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \App\Models\CRM\CRMCustomer::query()->delete();
        
        $customers = array (
            0 => 
            array (
                'id' => '4',
                'code' => 'CB0007',
                'name' => 'BlueCore-Global',
                'postal_address' => '8543 Ronald Ngala',
                'physical_address' => '8543',
                'fax' => 'fx-0727500128',
                'email' => 'david.kimari@outlook.com',
                'telephone1' => '+254727500128',
                'telephone2' => '0789239852',
                'website' => 'bluecore.global',
                'country_id' => '110',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-04-30 06:08:54.707',
                'updated_at' => '2020-06-29 10:41:36.363',
                'unit_configurable_name' => 'Station',
                'sample_point_configurable_name' => 'Location',
                'product_configurable_name' => 'Material',
            ),
            1 => 
            array (
                'id' => '5',
                'code' => 'CN0001',
                'name' => 'Nuvemite',
                'postal_address' => '1801 Excise Ave Suite 111',
                'physical_address' => '1801 Excise Ave Suite 111',
                'fax' => 'fx-something',
                'email' => 'info@nuvemite.com',
                'telephone1' => '07230000000',
                'telephone2' => '07230000002',
                'website' => 'nuvemite.com',
                'country_id' => '110',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-04-30 06:26:50.227',
                'updated_at' => '2020-04-30 06:26:50.227',
                'unit_configurable_name' => NULL,
                'sample_point_configurable_name' => NULL,
                'product_configurable_name' => NULL,
            ),
            2 => 
            array (
                'id' => '6',
                'code' => 'CT0001',
                'name' => 'TenderSoko Ltd',
                'postal_address' => '8543 Ronald Ngala',
                'physical_address' => '8543',
                'fax' => 'ts-something',
                'email' => 'david@tendersoko.com',
                'telephone1' => '+254727500128',
                'telephone2' => '+254789239852',
                'website' => 'tendersoko.com',
                'country_id' => '110',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-04-30 06:36:39.603',
                'updated_at' => '2020-04-30 06:36:39.603',
                'unit_configurable_name' => NULL,
                'sample_point_configurable_name' => NULL,
                'product_configurable_name' => NULL,
            ),
            3 => 
            array (
                'id' => '10002',
                'code' => 'CZ0001',
                'name' => 'Zenith',
                'postal_address' => '8543 Ronald Ngala',
                'physical_address' => '8543',
                'fax' => 'fx-254727500128',
                'email' => 'david.kimari@outlook.com',
                'telephone1' => '+254727500128',
                'telephone2' => '+254727500128',
                'website' => 'http://bluecore.global',
                'country_id' => '110',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-05-14 04:56:45.450',
                'updated_at' => '2020-05-14 04:56:45.450',
                'unit_configurable_name' => NULL,
                'sample_point_configurable_name' => NULL,
                'product_configurable_name' => NULL,
            ),
            4 => 
            array (
                'id' => '10003',
                'code' => 'CZ0002',
                'name' => 'Zenith Business',
                'postal_address' => '34567suiioio',
                'physical_address' => 'yhsyusyus',
                'fax' => 'fx-0727500128',
                'email' => 'info@zenith.co.ke',
                'telephone1' => '0789652122',
                'telephone2' => '456789087675',
                'website' => 'www.test.com',
                'country_id' => '244',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-06-29 10:48:33.370',
                'updated_at' => '2020-06-29 10:48:33.370',
                'unit_configurable_name' => NULL,
                'sample_point_configurable_name' => NULL,
                'product_configurable_name' => NULL,
            ),
            5 => 
            array (
                'id' => '20003',
                'code' => 'CC0001',
                'name' => 'Customs Department',
                'postal_address' => 'Mombasa Port',
                'physical_address' => 'Mombasa Port',
                'fax' => 'fx-0727500128',
                'email' => 'david.kimari@outlook.com',
                'telephone1' => '+254727500128',
                'telephone2' => '+254789239852',
                'website' => 'kra.go.ke',
                'country_id' => '110',
                'company_id' => '1',
                'active' => '1',
                'created_at' => '2020-06-30 08:23:41.563',
                'updated_at' => '2020-06-30 08:31:56.093',
                'unit_configurable_name' => 'Station',
                'sample_point_configurable_name' => 'Location',
                'product_configurable_name' => 'Product',
            ),
        );

        foreach ($customers as $customerData) {
            \App\Models\CRM\CRMCustomer::forceCreate($customerData);
        }
    }
}