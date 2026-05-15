<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \App\User::query()->delete();
        
        $users = array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Admin Main ',
                'email' => 'admin@lab.kra.go.ke',
                'email_verified_at' => NULL,
                'password' => '$2y$10$6EmgvTt7CkvRjRPNr5LaY.vH5ePsUCtT8XN8d7E3XnYJZuzjVuAye',
                'company_id' => '1',
                'remember_token' => '4kWAZgTZTF7VQF0jt6wjv2p6a1soTIpEOl8EF09YAtx1aqHhVvIpVhsDnar9',
                'created_at' => '2020-04-14 19:48:30.490',
                'updated_at' => '2020-07-27 03:49:09.693',
                'active' => '1',
                'location_id' => '3',
                'department_id' => '3',
                'photo' => NULL,
                'position' => '5',
                'education_level' => NULL,
                'date_of_birth' => NULL,
                'employment_date' => NULL,
                'id_number' => '234567',
                'nssf' => NULL,
                'nhif' => NULL,
                'kra_pin' => NULL,
                'first_name' => 'Admin',
                'middle_name' => 'Main',
                'last_name' => NULL,
                'electronic_sig' => NULL,
                'designation' => '7',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'John Smith Doe',
                'email' => 'john@lab.kra.go.ke',
                'email_verified_at' => NULL,
                'password' => '$2y$10$pFlEEk9eHTTJJGD9JpU/seRspjI/qyiQww5exZ5WryxV40zgzAEGq',
                'company_id' => '1',
                'remember_token' => 'mrO08VGOSoerzycQo15Z5toKDOXIwBa9X5IX90TuJeDczIA1pBnv2O373t5c',
                'created_at' => '2020-06-30 06:10:04.353',
                'updated_at' => '2020-07-21 12:00:02.217',
                'active' => '1',
                'location_id' => '3',
                'department_id' => '1',
                'photo' => '/storage/personnel/v2648AmXK3YGLBKucg4zgatAupm53hIYZ5PUhfLk.jpeg',
                'position' => '5',
                'education_level' => NULL,
                'date_of_birth' => NULL,
                'employment_date' => NULL,
                'id_number' => '98765434567',
                'nssf' => NULL,
                'nhif' => NULL,
                'kra_pin' => NULL,
                'first_name' => 'John',
                'middle_name' => 'Smith',
                'last_name' => 'Doe',
                'electronic_sig' => NULL,
                'designation' => '7',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Jane Doe Smith',
                'email' => 'jane@lab.kra.go.ke',
                'email_verified_at' => NULL,
                'password' => '$2y$10$pFlEEk9eHTTJJGD9JpU/seRspjI/qyiQww5exZ5WryxV40zgzAEGq',
                'company_id' => '1',
                'remember_token' => NULL,
                'created_at' => '2020-06-30 06:10:51.843',
                'updated_at' => '2020-07-20 02:40:56.883',
                'active' => '1',
                'location_id' => '3',
                'department_id' => '1',
                'photo' => '/storage/personnel/mZJ01mDwgCQcZ5fk7BgIfYTg7r64bazCm6LAzIoP.png',
                'position' => '4',
                'education_level' => '3',
                'date_of_birth' => '1987-03-20',
                'employment_date' => '2020-07-01',
                'id_number' => '232245',
                'nssf' => '345678',
                'nhif' => 'G-8765',
                'kra_pin' => 'A56Q7676R',
                'first_name' => 'Jane',
                'middle_name' => 'Doe',
                'last_name' => 'Smith',
                'electronic_sig' => NULL,
                'designation' => '9',
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'David Kimari',
                'email' => 'david.kimari@gmail.com',
                'email_verified_at' => NULL,
                'password' => '$2y$10$pFlEEk9eHTTJJGD9JpU/seRspjI/qyiQww5exZ5WryxV40zgzAEGq',
                'company_id' => '0',
                'remember_token' => NULL,
                'created_at' => '2020-07-02 04:47:39.083',
                'updated_at' => '2020-07-02 04:47:39.083',
                'active' => '1',
                'location_id' => '0',
                'department_id' => NULL,
                'photo' => NULL,
                'position' => NULL,
                'education_level' => NULL,
                'date_of_birth' => NULL,
                'employment_date' => NULL,
                'id_number' => NULL,
                'nssf' => NULL,
                'nhif' => NULL,
                'kra_pin' => NULL,
                'first_name' => NULL,
                'middle_name' => NULL,
                'last_name' => NULL,
                'electronic_sig' => NULL,
                'designation' => NULL,
            ),
            4 => 
            array (
                'id' => '7',
                'name' => 'David Kimari Mwangi',
                'email' => 'david.kimari@outlook.com',
                'email_verified_at' => NULL,
                'password' => '$2y$10$DrvaDcI3ZOh7VmI.KEcXNOJ7F69H2SLm6Vr1BDBRbKe4vIsXWES9G',
                'company_id' => '1',
                'remember_token' => NULL,
                'created_at' => '2020-08-03 20:26:45.027',
                'updated_at' => '2020-08-03 20:26:45.027',
                'active' => '1',
                'location_id' => '3',
                'department_id' => '1',
                'photo' => '/storage/personnel/vSAGmKksRyRvtzMPZBSPMUDkhfyT2GpkZvMxUJT6.png',
                'position' => '5',
                'education_level' => '3',
                'date_of_birth' => '1987-09-22',
                'employment_date' => '2012-08-01',
                'id_number' => '25076536',
                'nssf' => 'N345678',
                'nhif' => 'NH-0987654',
                'kra_pin' => 'AQ456789',
                'first_name' => 'David',
                'middle_name' => 'Kimari',
                'last_name' => 'Mwangi',
                'electronic_sig' => NULL,
                'designation' => '7',
            ),
        );

        foreach ($users as $userData) {
            \App\User::forceCreate($userData);
        }
    }
}