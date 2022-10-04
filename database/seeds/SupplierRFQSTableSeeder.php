<?php

use Illuminate\Database\Seeder;

class SupplierRFQSTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('supplier_r_f_q_s')->delete();
        
        \DB::table('supplier_r_f_q_s')->insert(array (
            0 => 
            array (
                'id' => '2',
                'supplier_id' => '2',
                'request_id' => '15',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-07-06 06:58:10.663',
                'updated_at' => '2020-07-06 09:31:59.160',
            ),
            1 => 
            array (
                'id' => '4',
                'supplier_id' => '2',
                'request_id' => '17',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-07-06 07:27:30.773',
                'updated_at' => '2020-07-06 09:04:42.687',
            ),
            2 => 
            array (
                'id' => '6',
                'supplier_id' => '3',
                'request_id' => '15',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-07-06 06:58:10.000',
                'updated_at' => '2020-07-06 09:32:27.937',
            ),
            3 => 
            array (
                'id' => '7',
                'supplier_id' => '3',
                'request_id' => '17',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-07-06 07:27:30.000',
                'updated_at' => '2020-07-06 09:30:38.510',
            ),
            4 => 
            array (
                'id' => '8',
                'supplier_id' => '2',
                'request_id' => '18',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-07-06 10:07:43.760',
                'updated_at' => '2020-07-06 10:08:05.930',
            ),
            5 => 
            array (
                'id' => '9',
                'supplier_id' => '3',
                'request_id' => '18',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-07-06 10:07:43.770',
                'updated_at' => '2020-07-06 10:08:40.473',
            ),
            6 => 
            array (
                'id' => '10',
                'supplier_id' => '2',
                'request_id' => '20',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-07-06 10:54:49.307',
                'updated_at' => '2020-07-06 10:55:18.880',
            ),
            7 => 
            array (
                'id' => '11',
                'supplier_id' => '3',
                'request_id' => '20',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-07-06 10:54:49.320',
                'updated_at' => '2020-07-06 10:55:30.693',
            ),
            8 => 
            array (
                'id' => '12',
                'supplier_id' => '2',
                'request_id' => '22',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-07-06 11:52:43.000',
                'updated_at' => '2020-07-06 11:53:19.470',
            ),
            9 => 
            array (
                'id' => '13',
                'supplier_id' => '3',
                'request_id' => '22',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-07-06 11:52:43.013',
                'updated_at' => '2020-07-06 11:53:34.543',
            ),
            10 => 
            array (
                'id' => '10002',
                'supplier_id' => '2',
                'request_id' => '10028',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-08-03 18:42:11.300',
                'updated_at' => '2020-08-03 18:56:58.747',
            ),
            11 => 
            array (
                'id' => '10003',
                'supplier_id' => '3',
                'request_id' => '10028',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-08-03 18:42:11.360',
                'updated_at' => '2020-08-03 18:57:39.860',
            ),
            12 => 
            array (
                'id' => '10004',
                'supplier_id' => '3',
                'request_id' => '10030',
                'rfq_sent' => '0',
                'quote_received' => '0',
                'created_at' => '2020-08-03 20:11:33.150',
                'updated_at' => '2020-08-03 20:11:33.150',
            ),
            13 => 
            array (
                'id' => '10005',
                'supplier_id' => '2',
                'request_id' => '10034',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-08-04 16:48:46.230',
                'updated_at' => '2020-08-04 16:49:44.120',
            ),
            14 => 
            array (
                'id' => '10006',
                'supplier_id' => '3',
                'request_id' => '10034',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-08-04 16:48:46.273',
                'updated_at' => '2020-08-04 16:50:19.093',
            ),
            15 => 
            array (
                'id' => '10007',
                'supplier_id' => '2',
                'request_id' => '10045',
                'rfq_sent' => '1',
                'quote_received' => '0',
                'created_at' => '2020-08-05 10:21:49.063',
                'updated_at' => '2020-08-05 10:22:01.773',
            ),
            16 => 
            array (
                'id' => '10008',
                'supplier_id' => '3',
                'request_id' => '10045',
                'rfq_sent' => '1',
                'quote_received' => '0',
                'created_at' => '2020-08-05 10:21:49.113',
                'updated_at' => '2020-08-05 10:22:04.953',
            ),
            17 => 
            array (
                'id' => '10009',
                'supplier_id' => '2',
                'request_id' => '10047',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-08-05 10:23:37.317',
                'updated_at' => '2020-08-05 10:27:28.310',
            ),
            18 => 
            array (
                'id' => '10010',
                'supplier_id' => '3',
                'request_id' => '10047',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-08-05 10:23:37.357',
                'updated_at' => '2020-08-05 10:27:49.863',
            ),
            19 => 
            array (
                'id' => '10011',
                'supplier_id' => '2',
                'request_id' => '10054',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-08-05 11:01:58.043',
                'updated_at' => '2020-08-05 11:04:01.107',
            ),
            20 => 
            array (
                'id' => '10012',
                'supplier_id' => '3',
                'request_id' => '10054',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-08-05 11:01:58.090',
                'updated_at' => '2020-08-05 11:04:18.677',
            ),
            21 => 
            array (
                'id' => '10013',
                'supplier_id' => '2',
                'request_id' => '10057',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-08-05 12:19:56.257',
                'updated_at' => '2020-08-05 12:22:31.050',
            ),
            22 => 
            array (
                'id' => '10014',
                'supplier_id' => '3',
                'request_id' => '10057',
                'rfq_sent' => '1',
                'quote_received' => '1',
                'created_at' => '2020-08-05 12:19:56.303',
                'updated_at' => '2020-08-05 12:23:17.330',
            ),
        ));
        
        
    }
}