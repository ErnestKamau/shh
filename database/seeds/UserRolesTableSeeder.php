<?php

use Illuminate\Database\Seeder;

class UserRolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('user_roles')->delete();
        
        \DB::table('user_roles')->insert(array (
            0 => 
            array (
                'id' => '1',
                'role_id' => '4',
                'user_id' => '3',
                'created_at' => '2020-07-04 13:48:55.490',
                'updated_at' => '2020-07-04 13:48:55.490',
            ),
            1 => 
            array (
                'id' => '3',
                'role_id' => '2',
                'user_id' => '3',
                'created_at' => '2020-07-04 13:49:44.013',
                'updated_at' => '2020-07-04 13:49:44.013',
            ),
            2 => 
            array (
                'id' => '4',
                'role_id' => '3',
                'user_id' => '2',
                'created_at' => '2020-07-04 15:13:01.967',
                'updated_at' => '2020-07-04 15:13:01.967',
            ),
            3 => 
            array (
                'id' => '5',
                'role_id' => '2',
                'user_id' => '2',
                'created_at' => '2020-07-04 15:13:01.983',
                'updated_at' => '2020-07-04 15:13:01.983',
            ),
            4 => 
            array (
                'id' => '10002',
                'role_id' => '10002',
                'user_id' => '3',
                'created_at' => '2020-07-14 08:28:43.187',
                'updated_at' => '2020-07-14 08:28:43.187',
            ),
            5 => 
            array (
                'id' => '10003',
                'role_id' => '10002',
                'user_id' => '2',
                'created_at' => '2020-07-14 08:28:56.317',
                'updated_at' => '2020-07-14 08:28:56.317',
            ),
            6 => 
            array (
                'id' => '10004',
                'role_id' => '10002',
                'user_id' => '1',
                'created_at' => '2020-07-20 22:58:08.673',
                'updated_at' => '2020-07-20 22:58:08.673',
            ),
            7 => 
            array (
                'id' => '10005',
                'role_id' => '10002',
                'user_id' => '7',
                'created_at' => '2020-08-03 20:27:35.283',
                'updated_at' => '2020-08-03 20:27:35.283',
            ),
            8 => 
            array (
                'id' => '10006',
                'role_id' => '3',
                'user_id' => '7',
                'created_at' => '2020-08-03 20:27:35.333',
                'updated_at' => '2020-08-03 20:27:35.333',
            ),
            9 => 
            array (
                'id' => '10007',
                'role_id' => '10004',
                'user_id' => '7',
                'created_at' => '2020-08-03 20:27:35.380',
                'updated_at' => '2020-08-03 20:27:35.380',
            ),
        ));
        
        
    }
}