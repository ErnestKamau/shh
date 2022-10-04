<?php

use Illuminate\Database\Seeder;

class BatchCommentsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('batch_comments')->delete();
        
        \DB::table('batch_comments')->insert(array (
            0 => 
            array (
                'id' => '2',
                'comments' => 'This is a test on notes',
                'created_by' => '1',
                'reminder_for' => '1',
                'personnel_to_cc' => '1',
                'completed_at' => NULL,
                'created_at' => '2020-06-28 10:56:40.853',
                'updated_at' => '2020-06-28 11:25:12.887',
                'sample_header_id' => '4',
                'comment_type' => 'Non-Conformity Issue',
            ),
            1 => 
            array (
                'id' => '3',
                'comments' => 'Is urgent',
                'created_by' => '2',
                'reminder_for' => '4',
                'personnel_to_cc' => '1,3',
                'completed_at' => NULL,
                'created_at' => '2020-07-21 12:50:42.843',
                'updated_at' => '2020-07-21 12:50:42.843',
                'sample_header_id' => '30023',
                'comment_type' => 'Reminder',
            ),
        ));
        
        
    }
}