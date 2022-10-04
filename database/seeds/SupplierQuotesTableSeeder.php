<?php

use Illuminate\Database\Seeder;

class SupplierQuotesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('supplier_quotes')->delete();
        
        \DB::table('supplier_quotes')->insert(array (
            0 => 
            array (
                'id' => '1',
                'supplier_id' => '2',
                'request_id' => '17',
                'request_item_id' => '19',
                'quote_amount' => '12.0',
                'created_at' => '2020-07-06 09:04:42.743',
                'updated_at' => '2020-07-21 12:25:03.930',
                'awarded_at' => '2020-07-21 12:25:03.927',
                'is_awarded' => '1',
            ),
            1 => 
            array (
                'id' => '8',
                'supplier_id' => '3',
                'request_id' => '17',
                'request_item_id' => '19',
                'quote_amount' => '12345.0',
                'created_at' => '2020-07-06 09:30:38.487',
                'updated_at' => '2020-07-21 12:25:03.930',
                'awarded_at' => '2020-07-21 12:25:03.927',
                'is_awarded' => '0',
            ),
            2 => 
            array (
                'id' => '9',
                'supplier_id' => '2',
                'request_id' => '15',
                'request_item_id' => '17',
                'quote_amount' => '12345.0',
                'created_at' => '2020-07-06 09:31:59.123',
                'updated_at' => '2020-07-06 09:31:59.123',
                'awarded_at' => NULL,
                'is_awarded' => '0',
            ),
            3 => 
            array (
                'id' => '10',
                'supplier_id' => '3',
                'request_id' => '15',
                'request_item_id' => '17',
                'quote_amount' => '6700.0',
                'created_at' => '2020-07-06 09:32:27.920',
                'updated_at' => '2020-07-06 09:32:27.920',
                'awarded_at' => NULL,
                'is_awarded' => '0',
            ),
            4 => 
            array (
                'id' => '11',
                'supplier_id' => '2',
                'request_id' => '18',
                'request_item_id' => '22',
                'quote_amount' => '12345.0',
                'created_at' => '2020-07-06 10:08:05.893',
                'updated_at' => '2020-07-06 10:33:22.957',
                'awarded_at' => '2020-07-06 10:33:22.957',
                'is_awarded' => '1',
            ),
            5 => 
            array (
                'id' => '12',
                'supplier_id' => '2',
                'request_id' => '18',
                'request_item_id' => '23',
                'quote_amount' => '56000.0',
                'created_at' => '2020-07-06 10:08:05.917',
                'updated_at' => '2020-07-06 10:34:48.677',
                'awarded_at' => '2020-07-06 10:34:48.677',
                'is_awarded' => '1',
            ),
            6 => 
            array (
                'id' => '13',
                'supplier_id' => '3',
                'request_id' => '18',
                'request_item_id' => '21',
                'quote_amount' => '67000.0',
                'created_at' => '2020-07-06 10:08:40.407',
                'updated_at' => '2020-07-06 10:35:11.297',
                'awarded_at' => '2020-07-06 10:35:11.297',
                'is_awarded' => '1',
            ),
            7 => 
            array (
                'id' => '14',
                'supplier_id' => '3',
                'request_id' => '18',
                'request_item_id' => '22',
                'quote_amount' => '80000.0',
                'created_at' => '2020-07-06 10:08:40.440',
                'updated_at' => '2020-07-06 10:33:22.957',
                'awarded_at' => '2020-07-06 10:33:22.957',
                'is_awarded' => '0',
            ),
            8 => 
            array (
                'id' => '15',
                'supplier_id' => '3',
                'request_id' => '18',
                'request_item_id' => '23',
                'quote_amount' => '63000.0',
                'created_at' => '2020-07-06 10:08:40.457',
                'updated_at' => '2020-07-06 10:34:48.677',
                'awarded_at' => '2020-07-06 10:34:48.677',
                'is_awarded' => '0',
            ),
            9 => 
            array (
                'id' => '16',
                'supplier_id' => '2',
                'request_id' => '20',
                'request_item_id' => '25',
                'quote_amount' => '4569.0',
                'created_at' => '2020-07-06 10:55:18.860',
                'updated_at' => '2020-07-06 10:55:44.497',
                'awarded_at' => '2020-07-06 10:55:44.497',
                'is_awarded' => '0',
            ),
            10 => 
            array (
                'id' => '17',
                'supplier_id' => '3',
                'request_id' => '20',
                'request_item_id' => '25',
                'quote_amount' => '1234.0',
                'created_at' => '2020-07-06 10:55:30.677',
                'updated_at' => '2020-07-06 10:55:44.497',
                'awarded_at' => '2020-07-06 10:55:44.497',
                'is_awarded' => '1',
            ),
            11 => 
            array (
                'id' => '18',
                'supplier_id' => '2',
                'request_id' => '22',
                'request_item_id' => '29',
                'quote_amount' => '12456.0',
                'created_at' => '2020-07-06 11:53:19.427',
                'updated_at' => '2020-07-06 11:54:21.233',
                'awarded_at' => '2020-07-06 11:54:21.233',
                'is_awarded' => '0',
            ),
            12 => 
            array (
                'id' => '19',
                'supplier_id' => '2',
                'request_id' => '22',
                'request_item_id' => '30',
                'quote_amount' => '10000.0',
                'created_at' => '2020-07-06 11:53:19.450',
                'updated_at' => '2020-07-06 11:54:53.900',
                'awarded_at' => '2020-07-06 11:54:53.900',
                'is_awarded' => '1',
            ),
            13 => 
            array (
                'id' => '20',
                'supplier_id' => '3',
                'request_id' => '22',
                'request_item_id' => '29',
                'quote_amount' => '12000.0',
                'created_at' => '2020-07-06 11:53:34.503',
                'updated_at' => '2020-07-06 11:54:21.233',
                'awarded_at' => '2020-07-06 11:54:21.233',
                'is_awarded' => '1',
            ),
            14 => 
            array (
                'id' => '21',
                'supplier_id' => '3',
                'request_id' => '22',
                'request_item_id' => '30',
                'quote_amount' => '7000.0',
                'created_at' => '2020-07-06 11:53:34.527',
                'updated_at' => '2020-07-06 11:54:53.900',
                'awarded_at' => '2020-07-06 11:54:53.900',
                'is_awarded' => '0',
            ),
            15 => 
            array (
                'id' => '10016',
                'supplier_id' => '2',
                'request_id' => '10028',
                'request_item_id' => '10037',
                'quote_amount' => '120000.0',
                'created_at' => '2020-08-03 18:56:58.643',
                'updated_at' => '2020-08-03 19:02:35.730',
                'awarded_at' => '2020-08-03 19:02:35.730',
                'is_awarded' => '1',
            ),
            16 => 
            array (
                'id' => '10017',
                'supplier_id' => '2',
                'request_id' => '10028',
                'request_item_id' => '10038',
                'quote_amount' => '700000.0',
                'created_at' => '2020-08-03 18:56:58.707',
                'updated_at' => '2020-08-03 19:02:55.367',
                'awarded_at' => '2020-08-03 19:02:55.367',
                'is_awarded' => '0',
            ),
            17 => 
            array (
                'id' => '10018',
                'supplier_id' => '3',
                'request_id' => '10028',
                'request_item_id' => '10037',
                'quote_amount' => '130000.0',
                'created_at' => '2020-08-03 18:57:39.713',
                'updated_at' => '2020-08-03 19:02:35.730',
                'awarded_at' => '2020-08-03 19:02:35.730',
                'is_awarded' => '0',
            ),
            18 => 
            array (
                'id' => '10019',
                'supplier_id' => '3',
                'request_id' => '10028',
                'request_item_id' => '10038',
                'quote_amount' => '800000.0',
                'created_at' => '2020-08-03 18:57:39.770',
                'updated_at' => '2020-08-03 19:02:55.367',
                'awarded_at' => '2020-08-03 19:02:55.367',
                'is_awarded' => '1',
            ),
            19 => 
            array (
                'id' => '10020',
                'supplier_id' => '3',
                'request_id' => '10028',
                'request_item_id' => '10039',
                'quote_amount' => '56000.0',
                'created_at' => '2020-08-03 18:57:39.807',
                'updated_at' => '2020-08-03 19:03:16.227',
                'awarded_at' => '2020-08-03 19:03:16.227',
                'is_awarded' => '1',
            ),
            20 => 
            array (
                'id' => '10021',
                'supplier_id' => '2',
                'request_id' => '10034',
                'request_item_id' => '10077',
                'quote_amount' => '60000.0',
                'created_at' => '2020-08-04 16:49:43.857',
                'updated_at' => '2020-08-04 16:50:52.600',
                'awarded_at' => '2020-08-04 16:50:52.600',
                'is_awarded' => '1',
            ),
            21 => 
            array (
                'id' => '10022',
                'supplier_id' => '2',
                'request_id' => '10034',
                'request_item_id' => '10078',
                'quote_amount' => '78000.0',
                'created_at' => '2020-08-04 16:49:43.930',
                'updated_at' => '2020-08-04 16:51:09.470',
                'awarded_at' => '2020-08-04 16:51:09.470',
                'is_awarded' => '0',
            ),
            22 => 
            array (
                'id' => '10023',
                'supplier_id' => '2',
                'request_id' => '10034',
                'request_item_id' => '10084',
                'quote_amount' => '20000.0',
                'created_at' => '2020-08-04 16:49:43.993',
                'updated_at' => '2020-08-04 16:51:30.657',
                'awarded_at' => '2020-08-04 16:51:30.657',
                'is_awarded' => '1',
            ),
            23 => 
            array (
                'id' => '10024',
                'supplier_id' => '2',
                'request_id' => '10034',
                'request_item_id' => '10085',
                'quote_amount' => '100000.0',
                'created_at' => '2020-08-04 16:49:44.030',
                'updated_at' => '2020-08-04 16:51:58.887',
                'awarded_at' => '2020-08-04 16:51:58.887',
                'is_awarded' => '1',
            ),
            24 => 
            array (
                'id' => '10025',
                'supplier_id' => '2',
                'request_id' => '10034',
                'request_item_id' => '10086',
                'quote_amount' => '100000.0',
                'created_at' => '2020-08-04 16:49:44.087',
                'updated_at' => '2020-08-04 16:52:33.197',
                'awarded_at' => '2020-08-04 16:52:33.197',
                'is_awarded' => '0',
            ),
            25 => 
            array (
                'id' => '10026',
                'supplier_id' => '3',
                'request_id' => '10034',
                'request_item_id' => '10077',
                'quote_amount' => '345000.0',
                'created_at' => '2020-08-04 16:50:18.870',
                'updated_at' => '2020-08-04 16:50:52.600',
                'awarded_at' => '2020-08-04 16:50:52.600',
                'is_awarded' => '0',
            ),
            26 => 
            array (
                'id' => '10027',
                'supplier_id' => '3',
                'request_id' => '10034',
                'request_item_id' => '10078',
                'quote_amount' => '79000.0',
                'created_at' => '2020-08-04 16:50:18.927',
                'updated_at' => '2020-08-04 16:51:09.470',
                'awarded_at' => '2020-08-04 16:51:09.470',
                'is_awarded' => '1',
            ),
            27 => 
            array (
                'id' => '10028',
                'supplier_id' => '3',
                'request_id' => '10034',
                'request_item_id' => '10084',
                'quote_amount' => '56000.0',
                'created_at' => '2020-08-04 16:50:18.960',
                'updated_at' => '2020-08-04 16:51:30.657',
                'awarded_at' => '2020-08-04 16:51:30.657',
                'is_awarded' => '0',
            ),
            28 => 
            array (
                'id' => '10029',
                'supplier_id' => '3',
                'request_id' => '10034',
                'request_item_id' => '10085',
                'quote_amount' => '89000.0',
                'created_at' => '2020-08-04 16:50:19.000',
                'updated_at' => '2020-08-04 16:51:58.887',
                'awarded_at' => '2020-08-04 16:51:58.887',
                'is_awarded' => '0',
            ),
            29 => 
            array (
                'id' => '10030',
                'supplier_id' => '3',
                'request_id' => '10034',
                'request_item_id' => '10086',
                'quote_amount' => '76000.0',
                'created_at' => '2020-08-04 16:50:19.057',
                'updated_at' => '2020-08-04 16:52:33.197',
                'awarded_at' => '2020-08-04 16:52:33.197',
                'is_awarded' => '1',
            ),
            30 => 
            array (
                'id' => '10031',
                'supplier_id' => '2',
                'request_id' => '10047',
                'request_item_id' => '10135',
                'quote_amount' => '123000.0',
                'created_at' => '2020-08-05 10:27:28.203',
                'updated_at' => '2020-08-05 10:29:08.410',
                'awarded_at' => '2020-08-05 10:29:08.410',
                'is_awarded' => '0',
            ),
            31 => 
            array (
                'id' => '10032',
                'supplier_id' => '2',
                'request_id' => '10047',
                'request_item_id' => '10136',
                'quote_amount' => '123000.0',
                'created_at' => '2020-08-05 10:27:28.270',
                'updated_at' => '2020-08-05 10:29:24.200',
                'awarded_at' => '2020-08-05 10:29:24.200',
                'is_awarded' => '1',
            ),
            32 => 
            array (
                'id' => '10033',
                'supplier_id' => '3',
                'request_id' => '10047',
                'request_item_id' => '10135',
                'quote_amount' => '123.0',
                'created_at' => '2020-08-05 10:27:49.717',
                'updated_at' => '2020-08-05 10:29:08.410',
                'awarded_at' => '2020-08-05 10:29:08.410',
                'is_awarded' => '1',
            ),
            33 => 
            array (
                'id' => '10034',
                'supplier_id' => '3',
                'request_id' => '10047',
                'request_item_id' => '10136',
                'quote_amount' => '234.0',
                'created_at' => '2020-08-05 10:27:49.773',
                'updated_at' => '2020-08-05 10:29:24.200',
                'awarded_at' => '2020-08-05 10:29:24.200',
                'is_awarded' => '0',
            ),
            34 => 
            array (
                'id' => '10035',
                'supplier_id' => '3',
                'request_id' => '10047',
                'request_item_id' => '10137',
                'quote_amount' => '345.0',
                'created_at' => '2020-08-05 10:27:49.827',
                'updated_at' => '2020-08-05 10:29:36.157',
                'awarded_at' => '2020-08-05 10:29:36.157',
                'is_awarded' => '1',
            ),
            35 => 
            array (
                'id' => '10036',
                'supplier_id' => '2',
                'request_id' => '10054',
                'request_item_id' => '10158',
                'quote_amount' => '30000.0',
                'created_at' => '2020-08-05 11:04:01.000',
                'updated_at' => '2020-08-05 11:04:40.457',
                'awarded_at' => '2020-08-05 11:04:40.457',
                'is_awarded' => '1',
            ),
            36 => 
            array (
                'id' => '10037',
                'supplier_id' => '2',
                'request_id' => '10054',
                'request_item_id' => '10160',
                'quote_amount' => '20000.0',
                'created_at' => '2020-08-05 11:04:01.060',
                'updated_at' => '2020-08-05 11:04:51.017',
                'awarded_at' => '2020-08-05 11:04:51.017',
                'is_awarded' => '0',
            ),
            37 => 
            array (
                'id' => '10038',
                'supplier_id' => '3',
                'request_id' => '10054',
                'request_item_id' => '10158',
                'quote_amount' => '65000.0',
                'created_at' => '2020-08-05 11:04:18.567',
                'updated_at' => '2020-08-05 11:04:40.457',
                'awarded_at' => '2020-08-05 11:04:40.457',
                'is_awarded' => '0',
            ),
            38 => 
            array (
                'id' => '10039',
                'supplier_id' => '3',
                'request_id' => '10054',
                'request_item_id' => '10160',
                'quote_amount' => '34000.0',
                'created_at' => '2020-08-05 11:04:18.627',
                'updated_at' => '2020-08-05 11:04:51.017',
                'awarded_at' => '2020-08-05 11:04:51.017',
                'is_awarded' => '1',
            ),
            39 => 
            array (
                'id' => '10040',
                'supplier_id' => '2',
                'request_id' => '10057',
                'request_item_id' => '10166',
                'quote_amount' => '67000.0',
                'created_at' => '2020-08-05 12:22:30.877',
                'updated_at' => '2020-08-05 12:24:10.797',
                'awarded_at' => '2020-08-05 12:24:10.797',
                'is_awarded' => '0',
            ),
            40 => 
            array (
                'id' => '10041',
                'supplier_id' => '2',
                'request_id' => '10057',
                'request_item_id' => '10167',
                'quote_amount' => '34000.0',
                'created_at' => '2020-08-05 12:22:30.940',
                'updated_at' => '2020-08-05 12:24:21.383',
                'awarded_at' => '2020-08-05 12:24:21.383',
                'is_awarded' => '0',
            ),
            41 => 
            array (
                'id' => '10042',
                'supplier_id' => '2',
                'request_id' => '10057',
                'request_item_id' => '10168',
                'quote_amount' => '23456.0',
                'created_at' => '2020-08-05 12:22:30.987',
                'updated_at' => '2020-08-05 12:24:36.407',
                'awarded_at' => '2020-08-05 12:24:36.407',
                'is_awarded' => '1',
            ),
            42 => 
            array (
                'id' => '10043',
                'supplier_id' => '3',
                'request_id' => '10057',
                'request_item_id' => '10166',
                'quote_amount' => '56788.0',
                'created_at' => '2020-08-05 12:23:17.160',
                'updated_at' => '2020-08-05 12:24:10.797',
                'awarded_at' => '2020-08-05 12:24:10.797',
                'is_awarded' => '1',
            ),
            43 => 
            array (
                'id' => '10044',
                'supplier_id' => '3',
                'request_id' => '10057',
                'request_item_id' => '10167',
                'quote_amount' => '25357.0',
                'created_at' => '2020-08-05 12:23:17.227',
                'updated_at' => '2020-08-05 12:24:21.383',
                'awarded_at' => '2020-08-05 12:24:21.383',
                'is_awarded' => '1',
            ),
            44 => 
            array (
                'id' => '10045',
                'supplier_id' => '3',
                'request_id' => '10057',
                'request_item_id' => '10168',
                'quote_amount' => '34567.0',
                'created_at' => '2020-08-05 12:23:17.270',
                'updated_at' => '2020-08-05 12:24:36.407',
                'awarded_at' => '2020-08-05 12:24:36.407',
                'is_awarded' => '0',
            ),
        ));
        
        
    }
}