<?php

use Illuminate\Database\Seeder;

class EntityNotesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('entity_notes')->delete();
        
        \DB::table('entity_notes')->insert(array (
            0 => 
            array (
                'id' => '2',
                'type' => 'General Note',
                'description' => 'This is a test',
                'model' => 'Purchase Request',
                'model_id' => '9',
                'created_by' => '1',
                'created_at' => '2020-07-05 16:52:03.583',
                'updated_at' => '2020-07-05 16:52:03.583',
            ),
            1 => 
            array (
                'id' => '3',
                'type' => 'Rejection',
                'description' => 'This is not okay',
                'model' => 'Purchase Request',
                'model_id' => '11',
                'created_by' => '3',
                'created_at' => '2020-07-06 04:50:40.797',
                'updated_at' => '2020-07-06 04:50:40.797',
            ),
            2 => 
            array (
                'id' => '4',
                'type' => 'General Note',
                'description' => 'Test Note',
                'model' => 'Request for Quotation',
                'model_id' => '15',
                'created_by' => '3',
                'created_at' => '2020-07-06 07:11:57.213',
                'updated_at' => '2020-07-06 07:11:57.213',
            ),
            3 => 
            array (
                'id' => '5',
                'type' => 'General Note',
                'description' => 'test',
                'model' => 'Purchase Request',
                'model_id' => '16',
                'created_by' => '3',
                'created_at' => '2020-07-06 07:25:29.220',
                'updated_at' => '2020-07-06 07:25:29.220',
            ),
            4 => 
            array (
                'id' => '6',
                'type' => 'Supplier Awarded',
                'description' => 'Test',
                'model' => 'Request for Quotation',
                'model_id' => '18',
                'created_by' => '1',
                'created_at' => '2020-07-06 10:33:22.987',
                'updated_at' => '2020-07-12 22:04:08.960',
            ),
            5 => 
            array (
                'id' => '7',
                'type' => 'Supplier Awarded',
                'description' => 'Better than the rest',
                'model' => 'Request for Quotation',
                'model_id' => '18',
                'created_by' => '1',
                'created_at' => '2020-07-06 10:34:48.693',
                'updated_at' => '2020-07-12 22:04:08.980',
            ),
            6 => 
            array (
                'id' => '8',
                'type' => 'Supplier Awarded',
                'description' => 'Only received',
                'model' => 'Request for Quotation',
                'model_id' => '18',
                'created_by' => '1',
                'created_at' => '2020-07-06 10:35:11.303',
                'updated_at' => '2020-07-12 22:04:09.023',
            ),
            7 => 
            array (
                'id' => '9',
                'type' => 'Supplier Awarded',
                'description' => 'cheapest',
                'model' => 'Request for Quotation',
                'model_id' => '20',
                'created_by' => '3',
                'created_at' => '2020-07-06 10:55:44.507',
                'updated_at' => '2020-07-06 10:55:44.507',
            ),
            8 => 
            array (
                'id' => '10',
                'type' => 'General Note',
                'description' => 'test',
                'model' => 'Purchase Request',
                'model_id' => '21',
                'created_by' => '1',
                'created_at' => '2020-07-06 11:45:37.220',
                'updated_at' => '2020-07-06 11:45:37.220',
            ),
            9 => 
            array (
                'id' => '11',
                'type' => 'Supplier Awarded',
                'description' => 'Cheapest and quality goods',
                'model' => 'Request for Quotation',
                'model_id' => '22',
                'created_by' => '3',
                'created_at' => '2020-07-06 11:54:21.240',
                'updated_at' => '2020-07-06 11:54:21.240',
            ),
            10 => 
            array (
                'id' => '12',
                'type' => 'Supplier Awarded',
                'description' => 'Highest quality goods',
                'model' => 'Request for Quotation',
                'model_id' => '22',
                'created_by' => '3',
                'created_at' => '2020-07-06 11:54:53.927',
                'updated_at' => '2020-07-06 11:54:53.927',
            ),
            11 => 
            array (
                'id' => '10002',
                'type' => 'Supplier Awarded',
                'description' => 'Best Price',
                'model' => 'Request for Quotation',
                'model_id' => '17',
                'created_by' => '2',
                'created_at' => '2020-07-21 12:25:03.957',
                'updated_at' => '2020-07-21 12:25:03.957',
            ),
            12 => 
            array (
                'id' => '10003',
                'type' => 'Supplier Awarded',
                'description' => 'Cheaper',
                'model' => 'Request for Quotation',
                'model_id' => '10028',
                'created_by' => '2',
                'created_at' => '2020-08-03 19:02:35.757',
                'updated_at' => '2020-08-03 19:02:35.757',
            ),
            13 => 
            array (
                'id' => '10004',
                'type' => 'Supplier Awarded',
                'description' => 'Superior Quality',
                'model' => 'Request for Quotation',
                'model_id' => '10028',
                'created_by' => '2',
                'created_at' => '2020-08-03 19:02:55.383',
                'updated_at' => '2020-08-03 19:02:55.383',
            ),
            14 => 
            array (
                'id' => '10005',
                'type' => 'Supplier Awarded',
                'description' => 'Only Quote',
                'model' => 'Request for Quotation',
                'model_id' => '10028',
                'created_by' => '2',
                'created_at' => '2020-08-03 19:03:16.243',
                'updated_at' => '2020-08-03 19:03:16.243',
            ),
            15 => 
            array (
                'id' => '10006',
                'type' => 'Supplier Awarded',
                'description' => 'Is cheap',
                'model' => 'Request for Quotation',
                'model_id' => '10034',
                'created_by' => '7',
                'created_at' => '2020-08-04 16:50:52.617',
                'updated_at' => '2020-08-04 16:50:52.617',
            ),
            16 => 
            array (
                'id' => '10007',
                'type' => 'Supplier Awarded',
                'description' => 'Best Quality',
                'model' => 'Request for Quotation',
                'model_id' => '10034',
                'created_by' => '7',
                'created_at' => '2020-08-04 16:51:09.487',
                'updated_at' => '2020-08-04 16:51:09.487',
            ),
            17 => 
            array (
                'id' => '10008',
                'type' => 'Supplier Awarded',
                'description' => 'Reason 2',
                'model' => 'Request for Quotation',
                'model_id' => '10034',
                'created_by' => '7',
                'created_at' => '2020-08-04 16:51:30.677',
                'updated_at' => '2020-08-04 16:51:30.677',
            ),
            18 => 
            array (
                'id' => '10009',
                'type' => 'Supplier Awarded',
                'description' => 'Check the other one',
                'model' => 'Request for Quotation',
                'model_id' => '10034',
                'created_by' => '7',
                'created_at' => '2020-08-04 16:51:58.900',
                'updated_at' => '2020-08-04 16:51:58.900',
            ),
            19 => 
            array (
                'id' => '10010',
                'type' => 'Supplier Awarded',
                'description' => 'Cheap',
                'model' => 'Request for Quotation',
                'model_id' => '10034',
                'created_by' => '7',
                'created_at' => '2020-08-04 16:52:33.217',
                'updated_at' => '2020-08-04 16:52:33.217',
            ),
            20 => 
            array (
                'id' => '10011',
                'type' => 'Rejection',
                'description' => 'Not Good',
                'model' => 'Purchase Request',
                'model_id' => '10039',
                'created_by' => '7',
                'created_at' => '2020-08-04 20:52:58.473',
                'updated_at' => '2020-08-04 20:52:58.473',
            ),
            21 => 
            array (
                'id' => '10012',
                'type' => 'Rejection',
                'description' => 'Items too few',
                'model' => 'Purchase Request',
                'model_id' => '10039',
                'created_by' => '7',
                'created_at' => '2020-08-04 21:07:04.273',
                'updated_at' => '2020-08-04 21:07:04.273',
            ),
            22 => 
            array (
                'id' => '10013',
                'type' => 'Rejection',
                'description' => 'Items too few',
                'model' => 'Purchase Request',
                'model_id' => '10039',
                'created_by' => '7',
                'created_at' => '2020-08-04 21:07:37.160',
                'updated_at' => '2020-08-04 21:07:37.160',
            ),
            23 => 
            array (
                'id' => '10014',
                'type' => 'Rejection',
                'description' => 'This is quantities are extravagant.',
                'model' => 'Purchase Request',
                'model_id' => '10039',
                'created_by' => '7',
                'created_at' => '2020-08-04 21:11:21.693',
                'updated_at' => '2020-08-04 21:11:21.693',
            ),
            24 => 
            array (
                'id' => '10015',
                'type' => 'Supplier Awarded',
                'description' => 'This is a test',
                'model' => 'Request for Quotation',
                'model_id' => '10047',
                'created_by' => '2',
                'created_at' => '2020-08-05 10:29:08.437',
                'updated_at' => '2020-08-05 10:29:08.437',
            ),
            25 => 
            array (
                'id' => '10016',
                'type' => 'Supplier Awarded',
                'description' => 'Quality',
                'model' => 'Request for Quotation',
                'model_id' => '10047',
                'created_by' => '2',
                'created_at' => '2020-08-05 10:29:24.217',
                'updated_at' => '2020-08-05 10:29:24.217',
            ),
            26 => 
            array (
                'id' => '10017',
                'type' => 'Supplier Awarded',
                'description' => 'Best Price',
                'model' => 'Request for Quotation',
                'model_id' => '10047',
                'created_by' => '2',
                'created_at' => '2020-08-05 10:29:36.180',
                'updated_at' => '2020-08-05 10:29:36.180',
            ),
            27 => 
            array (
                'id' => '10018',
                'type' => 'Supplier Awarded',
                'description' => 'Best',
                'model' => 'Request for Quotation',
                'model_id' => '10054',
                'created_by' => '2',
                'created_at' => '2020-08-05 11:04:40.477',
                'updated_at' => '2020-08-05 11:04:40.477',
            ),
            28 => 
            array (
                'id' => '10019',
                'type' => 'Supplier Awarded',
                'description' => 'Test',
                'model' => 'Request for Quotation',
                'model_id' => '10054',
                'created_by' => '2',
                'created_at' => '2020-08-05 11:04:51.040',
                'updated_at' => '2020-08-05 11:04:51.040',
            ),
            29 => 
            array (
                'id' => '10020',
                'type' => 'Supplier Awarded',
                'description' => 'cheapest',
                'model' => 'Request for Quotation',
                'model_id' => '10057',
                'created_by' => '7',
                'created_at' => '2020-08-05 12:24:10.817',
                'updated_at' => '2020-08-05 12:24:10.817',
            ),
            30 => 
            array (
                'id' => '10021',
                'type' => 'Supplier Awarded',
                'description' => 'Cheapest',
                'model' => 'Request for Quotation',
                'model_id' => '10057',
                'created_by' => '7',
                'created_at' => '2020-08-05 12:24:21.397',
                'updated_at' => '2020-08-05 12:24:21.397',
            ),
            31 => 
            array (
                'id' => '10022',
                'type' => 'Supplier Awarded',
                'description' => 'Cheapest and High quality',
                'model' => 'Request for Quotation',
                'model_id' => '10057',
                'created_by' => '7',
                'created_at' => '2020-08-05 12:24:36.437',
                'updated_at' => '2020-08-05 12:24:36.437',
            ),
        ));
        
        
    }
}