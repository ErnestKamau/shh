<?php

use Illuminate\Database\Seeder;

class EntityAttachmentsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('entity_attachments')->delete();
        
        \DB::table('entity_attachments')->insert(array (
            0 => 
            array (
                'id' => '1',
                'title' => 'Petty errands2',
                'type' => 'Image File',
                'file' => '/storage/requisition/vQNnblkcOp3KvtA7GWzNPPBVKwXTjPP8bxEmm160.png',
                'description' => 'This',
                'model' => 'Purchase Request',
                'model_id' => '9',
                'created_by' => '1',
                'created_at' => '2020-07-05 16:02:28.000',
                'updated_at' => '2020-07-05 16:51:11.423',
                'mime' => NULL,
                'size' => NULL,
            ),
            1 => 
            array (
                'id' => '2',
                'title' => 'Supply and Distribution',
                'type' => 'Image File',
                'file' => '/storage/requisition/DULEcFOlZLE2xz48cxyHjq0qGzOg0BHvgXA2JGAX.jpeg',
                'description' => 'image',
                'model' => 'Purchase Request',
                'model_id' => '16',
                'created_by' => '3',
                'created_at' => '2020-07-06 07:25:29.240',
                'updated_at' => '2020-07-06 07:25:29.240',
                'mime' => NULL,
                'size' => NULL,
            ),
            2 => 
            array (
                'id' => '3',
                'title' => 'Supply and Distribution',
                'type' => 'Image File',
                'file' => '/storage/requisition/Ic9muSr8RmqVVaTOAKcUVVekutxc5RxVm5QHBqns.png',
                'description' => 'Image',
                'model' => 'Purchase Request',
                'model_id' => '21',
                'created_by' => '1',
                'created_at' => '2020-07-06 11:46:03.157',
                'updated_at' => '2020-07-06 11:46:03.157',
                'mime' => NULL,
                'size' => NULL,
            ),
            3 => 
            array (
                'id' => '10008',
                'title' => 'Test Document',
                'type' => 'Page Attachment',
                'file' => '/storage/page-attachents/KUMoHsBXo73GX3W1CgpzmSmk7IOftjR0uF19ufte.pdf',
                'description' => 'This is the ultimate test that every document must go through',
                'model' => 'show-inventory-items/1/1',
                'model_id' => '0',
                'created_by' => '1',
                'created_at' => '2020-07-12 18:43:22.057',
                'updated_at' => '2020-07-12 18:43:22.057',
                'mime' => 'application/pdf',
                'size' => '1889',
            ),
            4 => 
            array (
                'id' => '10009',
                'title' => 'Test Document',
                'type' => 'Page Attachment',
                'file' => '/storage/page-attachents/q2VKcosbPjlBJ6f3gbAMQ2iRhIbokT8NgMat6YVs.pdf',
                'description' => 'This is the thing',
                'model' => 'sample-workflow/batch/30016/details',
                'model_id' => '0',
                'created_by' => '2',
                'created_at' => '2020-07-14 06:23:04.893',
                'updated_at' => '2020-07-14 06:23:04.893',
                'mime' => 'application/pdf',
                'size' => '69599',
            ),
        ));
        
        
    }
}