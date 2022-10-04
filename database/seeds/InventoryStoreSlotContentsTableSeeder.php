<?php

use Illuminate\Database\Seeder;

class InventoryStoreSlotContentsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_store_slot_contents')->delete();
        
        \DB::table('inventory_store_slot_contents')->insert(array (
            0 => 
            array (
                'id' => '2',
                'inventory_store_slot_id' => '1',
                'inventory_item_id' => '5',
                'created_at' => '2020-06-21 07:37:47.440',
                'updated_at' => '2020-06-21 07:37:47.440',
            ),
            1 => 
            array (
                'id' => '3',
                'inventory_store_slot_id' => '1',
                'inventory_item_id' => '6',
                'created_at' => '2020-06-21 07:59:13.140',
                'updated_at' => '2020-06-21 07:59:13.140',
            ),
            2 => 
            array (
                'id' => '4',
                'inventory_store_slot_id' => '1',
                'inventory_item_id' => '8',
                'created_at' => '2020-06-21 07:59:27.050',
                'updated_at' => '2020-06-21 07:59:27.050',
            ),
            3 => 
            array (
                'id' => '6',
                'inventory_store_slot_id' => '3',
                'inventory_item_id' => '20032',
                'created_at' => '2020-06-21 20:54:11.323',
                'updated_at' => '2020-06-21 20:54:11.323',
            ),
            4 => 
            array (
                'id' => '7',
                'inventory_store_slot_id' => '3',
                'inventory_item_id' => '20046',
                'created_at' => '2020-06-21 23:24:06.947',
                'updated_at' => '2020-06-21 23:24:06.947',
            ),
            5 => 
            array (
                'id' => '8',
                'inventory_store_slot_id' => '1',
                'inventory_item_id' => '20047',
                'created_at' => '2020-06-22 01:20:10.207',
                'updated_at' => '2020-06-22 01:20:10.207',
            ),
            6 => 
            array (
                'id' => '9',
                'inventory_store_slot_id' => '3',
                'inventory_item_id' => '20048',
                'created_at' => '2020-06-22 01:25:15.530',
                'updated_at' => '2020-06-22 01:25:15.530',
            ),
            7 => 
            array (
                'id' => '10',
                'inventory_store_slot_id' => '3',
                'inventory_item_id' => '20049',
                'created_at' => '2020-06-22 01:40:41.280',
                'updated_at' => '2020-06-22 01:40:41.280',
            ),
            8 => 
            array (
                'id' => '12',
                'inventory_store_slot_id' => '3',
                'inventory_item_id' => '20051',
                'created_at' => '2020-06-22 01:42:55.723',
                'updated_at' => '2020-06-22 01:42:55.723',
            ),
            9 => 
            array (
                'id' => '10002',
                'inventory_store_slot_id' => '3',
                'inventory_item_id' => '30047',
                'created_at' => '2020-06-22 10:08:08.327',
                'updated_at' => '2020-06-22 10:08:08.327',
            ),
            10 => 
            array (
                'id' => '10003',
                'inventory_store_slot_id' => '3',
                'inventory_item_id' => '30049',
                'created_at' => '2020-06-22 10:22:43.940',
                'updated_at' => '2020-06-22 10:22:43.940',
            ),
            11 => 
            array (
                'id' => '10004',
                'inventory_store_slot_id' => '10003',
                'inventory_item_id' => '30052',
                'created_at' => '2020-07-03 13:08:12.940',
                'updated_at' => '2020-07-03 13:08:12.940',
            ),
            12 => 
            array (
                'id' => '20004',
                'inventory_store_slot_id' => '1',
                'inventory_item_id' => '40052',
                'created_at' => '2020-07-21 12:30:06.307',
                'updated_at' => '2020-07-21 12:30:06.307',
            ),
            13 => 
            array (
                'id' => '20013',
                'inventory_store_slot_id' => '0',
                'inventory_item_id' => '40067',
                'created_at' => '2020-07-24 14:23:05.383',
                'updated_at' => '2020-07-24 14:23:05.383',
            ),
            14 => 
            array (
                'id' => '20014',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-24 14:23:05.433',
                'updated_at' => '2020-07-24 14:23:05.433',
            ),
            15 => 
            array (
                'id' => '20015',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40068',
                'created_at' => '2020-07-24 18:35:37.283',
                'updated_at' => '2020-07-24 18:35:37.283',
            ),
            16 => 
            array (
                'id' => '20016',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-24 18:35:37.337',
                'updated_at' => '2020-07-24 18:35:37.337',
            ),
            17 => 
            array (
                'id' => '20017',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40069',
                'created_at' => '2020-07-26 18:17:16.090',
                'updated_at' => '2020-07-26 18:17:16.090',
            ),
            18 => 
            array (
                'id' => '20018',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-26 18:17:16.143',
                'updated_at' => '2020-07-26 18:17:16.143',
            ),
            19 => 
            array (
                'id' => '20019',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '40070',
                'created_at' => '2020-07-26 18:30:05.867',
                'updated_at' => '2020-07-26 18:30:05.867',
            ),
            20 => 
            array (
                'id' => '20020',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-26 18:30:05.950',
                'updated_at' => '2020-07-26 18:30:05.950',
            ),
            21 => 
            array (
                'id' => '20021',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '40071',
                'created_at' => '2020-07-26 18:31:34.670',
                'updated_at' => '2020-07-26 18:31:34.670',
            ),
            22 => 
            array (
                'id' => '20022',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-26 18:31:34.717',
                'updated_at' => '2020-07-26 18:31:34.717',
            ),
            23 => 
            array (
                'id' => '20023',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40072',
                'created_at' => '2020-07-26 20:46:44.297',
                'updated_at' => '2020-07-26 20:46:44.297',
            ),
            24 => 
            array (
                'id' => '20024',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-26 20:46:44.350',
                'updated_at' => '2020-07-26 20:46:44.350',
            ),
            25 => 
            array (
                'id' => '20025',
                'inventory_store_slot_id' => '10010',
                'inventory_item_id' => '40073',
                'created_at' => '2020-07-26 20:47:18.007',
                'updated_at' => '2020-07-26 20:47:18.007',
            ),
            26 => 
            array (
                'id' => '20026',
                'inventory_store_slot_id' => '10010',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-26 20:47:18.057',
                'updated_at' => '2020-07-26 20:47:18.057',
            ),
            27 => 
            array (
                'id' => '20028',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-26 22:50:01.860',
                'updated_at' => '2020-07-26 22:50:01.860',
            ),
            28 => 
            array (
                'id' => '20029',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40075',
                'created_at' => '2020-07-26 22:50:02.290',
                'updated_at' => '2020-07-26 22:50:02.290',
            ),
            29 => 
            array (
                'id' => '20030',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-26 22:50:02.323',
                'updated_at' => '2020-07-26 22:50:02.323',
            ),
            30 => 
            array (
                'id' => '20031',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40076',
                'created_at' => '2020-07-26 22:50:02.753',
                'updated_at' => '2020-07-26 22:50:02.753',
            ),
            31 => 
            array (
                'id' => '20032',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-26 22:50:02.820',
                'updated_at' => '2020-07-26 22:50:02.820',
            ),
            32 => 
            array (
                'id' => '20033',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40077',
                'created_at' => '2020-07-27 02:53:16.337',
                'updated_at' => '2020-07-27 02:53:16.337',
            ),
            33 => 
            array (
                'id' => '20034',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-27 02:53:16.373',
                'updated_at' => '2020-07-27 02:53:16.373',
            ),
            34 => 
            array (
                'id' => '20035',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40078',
                'created_at' => '2020-07-27 02:53:16.783',
                'updated_at' => '2020-07-27 02:53:16.783',
            ),
            35 => 
            array (
                'id' => '20036',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-27 02:53:16.827',
                'updated_at' => '2020-07-27 02:53:16.827',
            ),
            36 => 
            array (
                'id' => '20037',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40079',
                'created_at' => '2020-07-27 02:53:17.187',
                'updated_at' => '2020-07-27 02:53:17.187',
            ),
            37 => 
            array (
                'id' => '20038',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-27 02:53:17.223',
                'updated_at' => '2020-07-27 02:53:17.223',
            ),
            38 => 
            array (
                'id' => '20039',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40080',
                'created_at' => '2020-07-27 02:53:17.610',
                'updated_at' => '2020-07-27 02:53:17.610',
            ),
            39 => 
            array (
                'id' => '20040',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-27 02:53:17.650',
                'updated_at' => '2020-07-27 02:53:17.650',
            ),
            40 => 
            array (
                'id' => '20042',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-27 02:53:18.090',
                'updated_at' => '2020-07-27 02:53:18.090',
            ),
            41 => 
            array (
                'id' => '20043',
                'inventory_store_slot_id' => '10010',
                'inventory_item_id' => '40082',
                'created_at' => '2020-07-27 02:55:29.317',
                'updated_at' => '2020-07-27 02:55:29.317',
            ),
            42 => 
            array (
                'id' => '20044',
                'inventory_store_slot_id' => '10010',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-27 02:55:29.357',
                'updated_at' => '2020-07-27 02:55:29.357',
            ),
            43 => 
            array (
                'id' => '20045',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '40083',
                'created_at' => '2020-07-27 02:56:16.463',
                'updated_at' => '2020-07-27 02:56:16.463',
            ),
            44 => 
            array (
                'id' => '20046',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '20014',
                'created_at' => '2020-07-27 02:56:16.500',
                'updated_at' => '2020-07-27 02:56:16.500',
            ),
            45 => 
            array (
                'id' => '20047',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40103',
                'created_at' => '2020-08-04 19:40:39.093',
                'updated_at' => '2020-08-04 19:40:39.093',
            ),
            46 => 
            array (
                'id' => '20048',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40104',
                'created_at' => '2020-08-04 19:44:00.650',
                'updated_at' => '2020-08-04 19:44:00.650',
            ),
            47 => 
            array (
                'id' => '20049',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40105',
                'created_at' => '2020-08-04 19:49:30.443',
                'updated_at' => '2020-08-04 19:49:30.443',
            ),
            48 => 
            array (
                'id' => '20050',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40106',
                'created_at' => '2020-08-04 19:50:38.247',
                'updated_at' => '2020-08-04 19:50:38.247',
            ),
            49 => 
            array (
                'id' => '20051',
                'inventory_store_slot_id' => '10007',
                'inventory_item_id' => '40107',
                'created_at' => '2020-08-04 19:50:38.477',
                'updated_at' => '2020-08-04 19:50:38.477',
            ),
            50 => 
            array (
                'id' => '20052',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40108',
                'created_at' => '2020-08-04 19:54:33.290',
                'updated_at' => '2020-08-04 19:54:33.290',
            ),
            51 => 
            array (
                'id' => '20053',
                'inventory_store_slot_id' => '10007',
                'inventory_item_id' => '40109',
                'created_at' => '2020-08-04 19:54:33.553',
                'updated_at' => '2020-08-04 19:54:33.553',
            ),
            52 => 
            array (
                'id' => '20054',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40110',
                'created_at' => '2020-08-04 19:58:02.483',
                'updated_at' => '2020-08-04 19:58:02.483',
            ),
            53 => 
            array (
                'id' => '20055',
                'inventory_store_slot_id' => '10007',
                'inventory_item_id' => '40111',
                'created_at' => '2020-08-04 19:58:02.700',
                'updated_at' => '2020-08-04 19:58:02.700',
            ),
            54 => 
            array (
                'id' => '20056',
                'inventory_store_slot_id' => '10003',
                'inventory_item_id' => '40112',
                'created_at' => '2020-08-04 19:58:41.430',
                'updated_at' => '2020-08-04 19:58:41.430',
            ),
            55 => 
            array (
                'id' => '20057',
                'inventory_store_slot_id' => '10010',
                'inventory_item_id' => '40113',
                'created_at' => '2020-08-04 19:58:41.683',
                'updated_at' => '2020-08-04 19:58:41.683',
            ),
            56 => 
            array (
                'id' => '20058',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40122',
                'created_at' => '2020-08-05 09:52:05.133',
                'updated_at' => '2020-08-05 09:52:05.133',
            ),
            57 => 
            array (
                'id' => '20059',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-08-05 09:52:05.183',
                'updated_at' => '2020-08-05 09:52:05.183',
            ),
            58 => 
            array (
                'id' => '20060',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40123',
                'created_at' => '2020-08-05 10:02:31.677',
                'updated_at' => '2020-08-05 10:02:31.677',
            ),
            59 => 
            array (
                'id' => '20061',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-08-05 10:02:31.717',
                'updated_at' => '2020-08-05 10:02:31.717',
            ),
            60 => 
            array (
                'id' => '20062',
                'inventory_store_slot_id' => '10003',
                'inventory_item_id' => '40126',
                'created_at' => '2020-08-05 10:32:05.103',
                'updated_at' => '2020-08-05 10:32:05.103',
            ),
            61 => 
            array (
                'id' => '20063',
                'inventory_store_slot_id' => '10007',
                'inventory_item_id' => '40127',
                'created_at' => '2020-08-05 10:32:05.440',
                'updated_at' => '2020-08-05 10:32:05.440',
            ),
            62 => 
            array (
                'id' => '20065',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-08-05 11:12:08.243',
                'updated_at' => '2020-08-05 11:12:08.243',
            ),
            63 => 
            array (
                'id' => '20067',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-08-05 11:12:58.657',
                'updated_at' => '2020-08-05 11:12:58.657',
            ),
            64 => 
            array (
                'id' => '20069',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-08-05 11:12:59.130',
                'updated_at' => '2020-08-05 11:12:59.130',
            ),
            65 => 
            array (
                'id' => '20071',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-08-05 11:12:59.563',
                'updated_at' => '2020-08-05 11:12:59.563',
            ),
            66 => 
            array (
                'id' => '20073',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '20014',
                'created_at' => '2020-08-05 11:13:00.020',
                'updated_at' => '2020-08-05 11:13:00.020',
            ),
            67 => 
            array (
                'id' => '20074',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '40133',
                'created_at' => '2020-08-05 11:15:28.010',
                'updated_at' => '2020-08-05 11:15:28.010',
            ),
            68 => 
            array (
                'id' => '20075',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '20014',
                'created_at' => '2020-08-05 11:15:28.053',
                'updated_at' => '2020-08-05 11:15:28.053',
            ),
            69 => 
            array (
                'id' => '20076',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '40134',
                'created_at' => '2020-08-05 11:16:34.550',
                'updated_at' => '2020-08-05 11:16:34.550',
            ),
            70 => 
            array (
                'id' => '20077',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '20014',
                'created_at' => '2020-08-05 11:16:34.603',
                'updated_at' => '2020-08-05 11:16:34.603',
            ),
            71 => 
            array (
                'id' => '20078',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '40135',
                'created_at' => '2020-08-05 11:16:34.997',
                'updated_at' => '2020-08-05 11:16:34.997',
            ),
            72 => 
            array (
                'id' => '20079',
                'inventory_store_slot_id' => '10008',
                'inventory_item_id' => '20014',
                'created_at' => '2020-08-05 11:16:35.033',
                'updated_at' => '2020-08-05 11:16:35.033',
            ),
            73 => 
            array (
                'id' => '20080',
                'inventory_store_slot_id' => '10007',
                'inventory_item_id' => '40137',
                'created_at' => '2020-08-05 12:29:50.677',
                'updated_at' => '2020-08-05 12:29:50.677',
            ),
            74 => 
            array (
                'id' => '20081',
                'inventory_store_slot_id' => '10003',
                'inventory_item_id' => '40138',
                'created_at' => '2020-08-05 12:30:13.250',
                'updated_at' => '2020-08-05 12:30:13.250',
            ),
            75 => 
            array (
                'id' => '20082',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40139',
                'created_at' => '2020-08-05 12:30:31.537',
                'updated_at' => '2020-08-05 12:30:31.537',
            ),
            76 => 
            array (
                'id' => '20083',
                'inventory_store_slot_id' => '10003',
                'inventory_item_id' => '40143',
                'created_at' => '2020-08-05 12:41:00.053',
                'updated_at' => '2020-08-05 12:41:00.053',
            ),
            77 => 
            array (
                'id' => '20084',
                'inventory_store_slot_id' => '10009',
                'inventory_item_id' => '40144',
                'created_at' => '2020-08-05 12:41:00.353',
                'updated_at' => '2020-08-05 12:41:00.353',
            ),
        ));
        
        
    }
}