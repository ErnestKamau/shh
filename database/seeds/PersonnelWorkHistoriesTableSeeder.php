<?php

use Illuminate\Database\Seeder;

class PersonnelWorkHistoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('personnel_work_histories')->delete();
        
        \DB::table('personnel_work_histories')->insert(array (
            0 => 
            array (
                'id' => '2',
                'department_id' => '3',
                'job_id' => '6',
                'user_id' => '1',
                'end_date' => '2020-07-20',
                'created_at' => '2020-07-20 22:03:33.783',
                'updated_at' => '2020-07-20 22:03:58.023',
            ),
            1 => 
            array (
                'id' => '3',
                'department_id' => '3',
                'job_id' => '4',
                'user_id' => '1',
                'end_date' => '2020-07-20',
                'created_at' => '2020-07-20 22:03:58.040',
                'updated_at' => '2020-07-20 22:48:20.220',
            ),
            2 => 
            array (
                'id' => '4',
                'department_id' => '3',
                'job_id' => '5',
                'user_id' => '1',
                'end_date' => NULL,
                'created_at' => '2020-07-20 22:48:20.240',
                'updated_at' => '2020-07-20 22:48:20.240',
            ),
            3 => 
            array (
                'id' => '6',
                'department_id' => '1',
                'job_id' => '4',
                'user_id' => '2',
                'end_date' => '2020-07-21',
                'created_at' => '2020-07-21 03:00:18.800',
                'updated_at' => '2020-07-21 12:00:02.250',
            ),
            4 => 
            array (
                'id' => '7',
                'department_id' => '1',
                'job_id' => '5',
                'user_id' => '2',
                'end_date' => NULL,
                'created_at' => '2020-07-21 12:00:02.290',
                'updated_at' => '2020-07-21 12:00:02.290',
            ),
            5 => 
            array (
                'id' => '8',
                'department_id' => '1',
                'job_id' => '5',
                'user_id' => '7',
                'end_date' => NULL,
                'created_at' => '2020-08-03 20:26:45.067',
                'updated_at' => '2020-08-03 20:26:45.067',
            ),
        ));
        
        
    }
}