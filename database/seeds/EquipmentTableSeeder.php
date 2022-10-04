<?php

use Illuminate\Database\Seeder;

class EquipmentTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('equipment')->delete();
        
        \DB::table('equipment')->insert(array (
            0 => 
            array (
                'id' => '2',
                'name' => 'Quantum Wave Generator',
                'equipment_number' => 'QO/YT567/2019',
                'description' => 'This is something',
                'picture' => '/storage/equipment/ovkEgaTcC8WOo7X1iJDCsPopGprtWZynCseL9heK.jpeg',
                'make' => 'NV',
                'model' => 'NV90XT',
                'date_purchased' => '2020-06-13',
                'maintainance_days' => '180',
                'calibration_days' => '90',
                'active' => '1',
                'created_at' => '2020-06-13 20:25:26.690',
                'updated_at' => '2020-06-26 12:15:36.953',
                'company_id' => '1',
                'maintainance_notification_in_days' => '90',
                'calibration_notification_in_days' => '30',
                'inventory_item_id' => NULL,
            ),
            1 => 
            array (
                'id' => '3',
                'name' => 'Milko Tester',
                'equipment_number' => 'MT0001',
                'description' => 'This is a Milko Tester',
                'picture' => '/storage/equipment/F4BByQDWhzGzEByAld8N23gR7M7cPMxNJedAnV6D.jpeg',
                'make' => 'MK Industries',
                'model' => 'MT-0001',
                'date_purchased' => '2020-06-11',
                'maintainance_days' => '90',
                'calibration_days' => '30',
                'active' => '1',
                'created_at' => '2020-06-29 06:38:48.347',
                'updated_at' => '2020-06-29 06:43:20.700',
                'company_id' => '1',
                'maintainance_notification_in_days' => '30',
                'calibration_notification_in_days' => '15',
                'inventory_item_id' => NULL,
            ),
            2 => 
            array (
                'id' => '1003',
                'name' => 'Other',
                'equipment_number' => 'Other',
                'description' => 'Other',
                'picture' => '/storage/equipment/dLLRhgGEmMJLqh5hndgYzMCNpQ4KvwifuFXgEQQ3.png',
                'make' => 'MK Industries',
                'model' => 'MT-0001',
                'date_purchased' => '2020-06-11',
                'maintainance_days' => '90',
                'calibration_days' => '30',
                'active' => '1',
                'created_at' => '2020-06-29 06:38:48.000',
                'updated_at' => '2020-06-30 08:35:54.507',
                'company_id' => '1',
                'maintainance_notification_in_days' => '30',
                'calibration_notification_in_days' => '15',
                'inventory_item_id' => NULL,
            ),
            3 => 
            array (
                'id' => '1004',
                'name' => 'Manual',
                'equipment_number' => 'M',
                'description' => 'Manual Data Capture',
                'picture' => '/storage/equipment/ml4Z890sF9Tu6758FuGckigkFl1uTrfC7BSa0kZn.png',
                'make' => 'MK Industries',
                'model' => 'MT-0001',
                'date_purchased' => '2020-06-11',
                'maintainance_days' => '180',
                'calibration_days' => '90',
                'active' => '1',
                'created_at' => '2020-06-29 06:38:48.000',
                'updated_at' => '2020-07-06 11:21:29.880',
                'company_id' => '1',
                'maintainance_notification_in_days' => '30',
                'calibration_notification_in_days' => '30',
                'inventory_item_id' => NULL,
            ),
        ));
        
        
    }
}