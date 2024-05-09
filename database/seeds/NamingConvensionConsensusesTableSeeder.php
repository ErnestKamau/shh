<?php

use Illuminate\Database\Seeder;

class NamingConvensionConsensusesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('naming_convension_consensuses')->delete();
        
        \DB::table('naming_convension_consensuses')->insert(array (
            0 => 
            array (
                'id' => '1',
                'string_part' => 'CB',
                'integer_part' => '0007',
                'model' => 'Customers',
                'created_at' => '2020-04-30 06:01:22.463',
                'updated_at' => '2020-04-30 06:25:25.187',
                'company_id' => NULL,
            ),
            1 => 
            array (
                'id' => '2',
                'string_part' => 'CD',
                'integer_part' => '0003',
                'model' => 'Customers',
                'created_at' => '2020-04-30 06:07:18.983',
                'updated_at' => '2020-04-30 06:08:54.680',
                'company_id' => NULL,
            ),
            2 => 
            array (
                'id' => '3',
                'string_part' => 'CN',
                'integer_part' => '0001',
                'model' => 'Customers',
                'created_at' => '2020-04-30 06:26:50.193',
                'updated_at' => '2020-04-30 06:26:50.193',
                'company_id' => NULL,
            ),
            3 => 
            array (
                'id' => '4',
                'string_part' => 'CT',
                'integer_part' => '0001',
                'model' => 'Customers',
                'created_at' => '2020-04-30 06:36:39.590',
                'updated_at' => '2020-04-30 06:36:39.590',
                'company_id' => '1',
            ),
            4 => 
            array (
                'id' => '10004',
                'string_part' => 'CZ',
                'integer_part' => '0002',
                'model' => 'Customers',
                'created_at' => '2020-05-14 04:56:45.430',
                'updated_at' => '2020-06-29 10:48:33.347',
                'company_id' => '1',
            ),
            5 => 
            array (
                'id' => '10005',
                'string_part' => 'BCCB0007',
                'integer_part' => '0035',
                'model' => 'Samples',
                'created_at' => '2020-05-18 00:00:01.417',
                'updated_at' => '2020-08-05 11:11:28.347',
                'company_id' => '1',
            ),
            6 => 
            array (
                'id' => '10006',
                'string_part' => 'CB0007SA',
                'integer_part' => '0086',
                'model' => 'Samples',
                'created_at' => '2020-05-18 00:02:37.060',
                'updated_at' => '2020-06-30 07:25:05.990',
                'company_id' => '1',
            ),
            7 => 
            array (
                'id' => '10007',
                'string_part' => 'KRA-',
                'integer_part' => '0006',
                'model' => 'ORDERS',
                'created_at' => '2020-05-26 07:09:34.323',
                'updated_at' => '2020-06-21 22:52:31.240',
                'company_id' => '1',
            ),
            8 => 
            array (
                'id' => '20007',
                'string_part' => '{-{',
                'integer_part' => '0001',
                'model' => 'Samples',
                'created_at' => '2020-06-21 19:43:09.170',
                'updated_at' => '2020-06-21 19:43:09.170',
                'company_id' => '1',
            ),
            9 => 
            array (
                'id' => '20008',
                'string_part' => 'S-S',
                'integer_part' => '0001',
                'model' => 'Samples',
                'created_at' => '2020-06-21 19:45:23.463',
                'updated_at' => '2020-06-21 19:45:23.463',
                'company_id' => '1',
            ),
            10 => 
            array (
                'id' => '20009',
                'string_part' => 'Su-Fi',
                'integer_part' => '0010',
                'model' => 'Samples',
                'created_at' => '2020-06-21 19:46:42.003',
                'updated_at' => '2020-08-05 12:30:31.390',
                'company_id' => '1',
            ),
            11 => 
            array (
                'id' => '20010',
                'string_part' => 'TE-CL',
                'integer_part' => '0020',
                'model' => 'Samples',
                'created_at' => '2020-06-21 19:59:16.610',
                'updated_at' => '2020-08-05 12:40:59.940',
                'company_id' => '1',
            ),
            12 => 
            array (
                'id' => '20011',
                'string_part' => 'IDS-TE-CL',
                'integer_part' => '0001',
                'model' => 'Samples',
                'created_at' => '2020-06-21 22:18:47.127',
                'updated_at' => '2020-06-21 22:18:47.127',
                'company_id' => '1',
            ),
            13 => 
            array (
                'id' => '20012',
                'string_part' => 'SU-BR',
                'integer_part' => '0008',
                'model' => 'Samples',
                'created_at' => '2020-06-21 23:23:14.763',
                'updated_at' => '2020-08-05 10:32:05.290',
                'company_id' => '1',
            ),
            14 => 
            array (
                'id' => '30007',
                'string_part' => 'STK-SU-FI',
                'integer_part' => '0002',
                'model' => 'Samples',
                'created_at' => '2020-06-22 10:09:54.227',
                'updated_at' => '2020-07-03 13:17:25.003',
                'company_id' => '1',
            ),
            15 => 
            array (
                'id' => '30008',
                'string_part' => 'IDS-SU-FI',
                'integer_part' => '0001',
                'model' => 'Samples',
                'created_at' => '2020-06-22 10:32:09.820',
                'updated_at' => '2020-06-22 10:32:09.820',
                'company_id' => '1',
            ),
            16 => 
            array (
                'id' => '30009',
                'string_part' => 'CB0007SA',
                'integer_part' => '0014',
                'model' => 'Samples',
                'created_at' => '2020-06-28 16:41:10.743',
                'updated_at' => '2020-06-29 05:51:30.173',
                'company_id' => '0',
            ),
            17 => 
            array (
                'id' => '30010',
                'string_part' => 'BCCB0007',
                'integer_part' => '0005',
                'model' => 'Samples',
                'created_at' => '2020-06-29 01:56:57.477',
                'updated_at' => '2020-06-29 05:54:14.967',
                'company_id' => '0',
            ),
            18 => 
            array (
                'id' => '30011',
                'string_part' => 'CB0007MA',
                'integer_part' => '0010',
                'model' => 'Samples',
                'created_at' => '2020-06-29 11:32:11.220',
                'updated_at' => '2020-06-29 19:01:26.880',
                'company_id' => '1',
            ),
            19 => 
            array (
                'id' => '40009',
                'string_part' => 'SAMP/',
                'integer_part' => '000089',
                'model' => 'Samples',
                'created_at' => '2020-06-30 07:26:52.717',
                'updated_at' => '2020-08-05 11:16:34.660',
                'company_id' => '1',
            ),
            20 => 
            array (
                'id' => '40010',
                'string_part' => 'CC',
                'integer_part' => '0001',
                'model' => 'Customers',
                'created_at' => '2020-06-30 08:23:41.550',
                'updated_at' => '2020-06-30 08:23:41.550',
                'company_id' => '1',
            ),
            21 => 
            array (
                'id' => '40011',
                'string_part' => 'BCCC0001',
                'integer_part' => '0003',
                'model' => 'Samples',
                'created_at' => '2020-06-30 08:28:17.720',
                'updated_at' => '2020-07-07 08:02:41.987',
                'company_id' => '1',
            ),
            22 => 
            array (
                'id' => '40012',
                'string_part' => 'PR',
                'integer_part' => '0020',
                'model' => 'Purchase Request',
                'created_at' => '2020-07-05 12:44:29.517',
                'updated_at' => '2020-08-05 12:07:52.340',
                'company_id' => '1',
            ),
            23 => 
            array (
                'id' => '40013',
                'string_part' => 'RFQ',
                'integer_part' => '0015',
                'model' => 'Request for Quotation',
                'created_at' => '2020-07-06 06:27:27.013',
                'updated_at' => '2020-08-05 12:17:09.797',
                'company_id' => '1',
            ),
            24 => 
            array (
                'id' => '40014',
                'string_part' => 'NJ',
                'integer_part' => '0001',
                'model' => 'SubCategories',
                'created_at' => '2020-07-06 11:41:48.483',
                'updated_at' => '2020-07-06 11:41:48.483',
                'company_id' => '1',
            ),
            25 => 
            array (
                'id' => '50012',
                'string_part' => 'PO',
                'integer_part' => '0013',
                'model' => 'Purchase Orders',
                'created_at' => '2020-07-12 22:50:57.373',
                'updated_at' => '2020-08-05 12:25:38.670',
                'company_id' => '1',
            ),
            26 => 
            array (
                'id' => '50013',
                'string_part' => 'GR',
                'integer_part' => '0008',
                'model' => 'Goods Receipt',
                'created_at' => '2020-07-13 00:39:48.390',
                'updated_at' => '2020-08-05 12:40:18.530',
                'company_id' => '1',
            ),
            27 => 
            array (
                'id' => '50014',
                'string_part' => 'LB',
                'integer_part' => '0042',
                'model' => 'SubCategories',
                'created_at' => '2020-07-24 13:43:48.080',
                'updated_at' => '2020-08-05 11:16:34.797',
                'company_id' => '1',
            ),
            28 => 
            array (
                'id' => '50015',
                'string_part' => 'LA-BC',
                'integer_part' => '0039',
                'model' => 'Samples',
                'created_at' => '2020-07-24 13:43:48.193',
                'updated_at' => '2020-08-05 11:16:34.907',
                'company_id' => '1',
            ),
            29 => 
            array (
                'id' => '50016',
                'string_part' => 'LR',
                'integer_part' => '0001',
                'model' => 'SubCategories',
                'created_at' => '2020-07-31 19:07:58.163',
                'updated_at' => '2020-07-31 19:07:58.163',
                'company_id' => '1',
            ),
            30 => 
            array (
                'id' => '50017',
                'string_part' => 'MI',
                'integer_part' => '0014',
                'model' => 'Material Issuance',
                'created_at' => '2020-08-03 03:11:58.337',
                'updated_at' => '2020-08-05 12:17:10.300',
                'company_id' => '1',
            ),
            31 => 
            array (
                'id' => '50018',
                'string_part' => 'LA-R2',
                'integer_part' => '0008',
                'model' => 'Samples',
                'created_at' => '2020-08-04 19:40:38.997',
                'updated_at' => '2020-08-05 12:41:00.240',
                'company_id' => '1',
            ),
            32 => 
            array (
                'id' => '50019',
                'string_part' => 'ST-',
                'integer_part' => '0002',
                'model' => 'StockTaking',
                'created_at' => '2020-08-05 00:13:03.120',
                'updated_at' => '2020-08-05 00:13:07.447',
                'company_id' => '1',
            ),
            33 => 
            array (
                'id' => '50020',
                'string_part' => 'IDS-LA-BC',
                'integer_part' => '0001',
                'model' => 'Samples',
                'created_at' => '2020-08-05 11:17:26.723',
                'updated_at' => '2020-08-05 11:17:26.723',
                'company_id' => '1',
            ),
            34 => 
            array (
                'id' => '50021',
                'string_part' => 'SF',
                'integer_part' => '0001',
                'model' => 'SubCategories',
                'created_at' => '2020-08-06 18:22:11.190',
                'updated_at' => '2020-08-06 18:22:11.190',
                'company_id' => '1',
            ),
        ));
        
        
    }
}