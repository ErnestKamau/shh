<?php

use Illuminate\Database\Seeder;

class EquipmentOperatorsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('equipment_operators')->delete();
        
        \DB::table('equipment_operators')->insert(array (
            0 => 
            array (
                'id' => '2',
                'user_id' => '1',
                'equipment_id' => '2',
                'created_at' => '2020-06-18 05:45:26.760',
                'updated_at' => '2020-07-13 17:11:30.927',
            ),
            1 => 
            array (
                'id' => '10002',
                'user_id' => '2',
                'equipment_id' => '2',
                'created_at' => '2020-06-30 06:11:27.657',
                'updated_at' => '2020-07-13 17:11:30.890',
            ),
            2 => 
            array (
                'id' => '10003',
                'user_id' => '3',
                'equipment_id' => '3',
                'created_at' => '2020-06-30 06:11:36.687',
                'updated_at' => '2020-07-13 17:11:08.917',
            ),
            3 => 
            array (
                'id' => '10004',
                'user_id' => '2',
                'equipment_id' => '1004',
                'created_at' => '2020-07-13 17:13:49.303',
                'updated_at' => '2020-07-13 17:13:49.303',
            ),
            4 => 
            array (
                'id' => '10005',
                'user_id' => '1',
                'equipment_id' => '1004',
                'created_at' => '2020-07-13 17:13:49.340',
                'updated_at' => '2020-07-13 17:13:49.340',
            ),
            5 => 
            array (
                'id' => '10006',
                'user_id' => '2',
                'equipment_id' => '3',
                'created_at' => '2020-07-13 17:14:01.217',
                'updated_at' => '2020-07-13 17:14:01.217',
            ),
            6 => 
            array (
                'id' => '10007',
                'user_id' => '3',
                'equipment_id' => '1003',
                'created_at' => '2020-07-13 17:14:21.787',
                'updated_at' => '2020-07-13 17:14:21.787',
            ),
            7 => 
            array (
                'id' => '10008',
                'user_id' => '2',
                'equipment_id' => '1003',
                'created_at' => '2020-07-13 17:14:21.817',
                'updated_at' => '2020-07-13 17:14:21.817',
            ),
            8 => 
            array (
                'id' => '10009',
                'user_id' => '1',
                'equipment_id' => '1003',
                'created_at' => '2020-07-13 17:14:21.847',
                'updated_at' => '2020-07-13 17:14:21.847',
            ),
            9 => 
            array (
                'id' => '10010',
                'user_id' => '3',
                'equipment_id' => '1004',
                'created_at' => '2020-07-13 17:14:33.380',
                'updated_at' => '2020-07-13 17:14:33.380',
            ),
        ));
        
        
    }
}