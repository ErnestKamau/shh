<?php

use Illuminate\Database\Seeder;

class SampleDatesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sample_dates')->delete();
        
        \DB::table('sample_dates')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Target Date',
                'date' => '2020-05-12 00:00:00.000',
                'created_at' => '2020-06-28 12:29:58.877',
                'updated_at' => '2020-06-28 12:29:58.877',
                'sample_header_id' => '4',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Target Date',
                'date' => '2020-07-05 00:00:00.000',
                'created_at' => '2020-06-28 12:55:12.770',
                'updated_at' => '2020-06-28 16:42:03.817',
                'sample_header_id' => '5',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Target Date',
                'date' => '2020-06-07 00:00:00.000',
                'created_at' => '2020-06-29 01:55:35.583',
                'updated_at' => '2020-06-29 01:55:35.583',
                'sample_header_id' => '12',
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'Target Date',
                'date' => '2020-07-05 00:00:00.000',
                'created_at' => '2020-06-29 02:03:33.560',
                'updated_at' => '2020-06-29 19:47:57.633',
                'sample_header_id' => '10012',
            ),
            4 => 
            array (
                'id' => '5',
                'name' => 'Target Date',
                'date' => '2020-07-02 00:00:00.000',
                'created_at' => '2020-06-29 05:50:31.447',
                'updated_at' => '2020-06-29 05:50:31.447',
                'sample_header_id' => '10009',
            ),
            5 => 
            array (
                'id' => '6',
                'name' => 'Target Date',
                'date' => '2020-07-02 00:00:00.000',
                'created_at' => '2020-06-29 06:02:43.643',
                'updated_at' => '2020-06-29 06:02:43.643',
                'sample_header_id' => '10015',
            ),
            6 => 
            array (
                'id' => '7',
                'name' => 'Target Date',
                'date' => '2020-07-08 00:00:00.000',
                'created_at' => '2020-06-29 11:32:11.273',
                'updated_at' => '2020-06-29 19:01:26.900',
                'sample_header_id' => '10016',
            ),
            7 => 
            array (
                'id' => '10002',
                'name' => 'Target Date',
                'date' => '2020-07-06 00:00:00.000',
                'created_at' => '2020-06-30 02:54:18.393',
                'updated_at' => '2020-06-30 07:11:18.367',
                'sample_header_id' => '20011',
            ),
            8 => 
            array (
                'id' => '10003',
                'name' => 'Target Date',
                'date' => '2020-07-06 00:00:00.000',
                'created_at' => '2020-06-30 06:50:31.767',
                'updated_at' => '2020-06-30 06:50:31.767',
                'sample_header_id' => '20012',
            ),
            9 => 
            array (
                'id' => '10004',
                'name' => 'Target Date',
                'date' => '2020-07-07 00:00:00.000',
                'created_at' => '2020-06-30 07:24:56.473',
                'updated_at' => '2020-06-30 07:40:03.267',
                'sample_header_id' => '20013',
            ),
            10 => 
            array (
                'id' => '10005',
                'name' => 'Target Date',
                'date' => '2020-05-11 00:00:00.000',
                'created_at' => '2020-06-30 08:28:43.433',
                'updated_at' => '2020-06-30 08:30:45.743',
                'sample_header_id' => '20014',
            ),
            11 => 
            array (
                'id' => '10006',
                'name' => 'Target Date',
                'date' => '2020-07-09 00:00:00.000',
                'created_at' => '2020-06-30 12:03:35.457',
                'updated_at' => '2020-06-30 12:03:52.240',
                'sample_header_id' => '20015',
            ),
            12 => 
            array (
                'id' => '10007',
                'name' => 'Target Date',
                'date' => '2020-07-16 00:00:00.000',
                'created_at' => '2020-07-07 08:03:57.007',
                'updated_at' => '2020-07-07 08:05:25.357',
                'sample_header_id' => '20016',
            ),
            13 => 
            array (
                'id' => '20007',
                'name' => 'Target Date',
                'date' => '2020-07-22 00:00:00.000',
                'created_at' => '2020-07-13 15:49:54.883',
                'updated_at' => '2020-07-13 15:49:54.883',
                'sample_header_id' => '30016',
            ),
            14 => 
            array (
                'id' => '20008',
                'name' => 'Target Date',
                'date' => '2020-07-31 00:00:00.000',
                'created_at' => '2020-07-15 17:32:04.363',
                'updated_at' => '2020-07-15 17:40:29.473',
                'sample_header_id' => '30017',
            ),
            15 => 
            array (
                'id' => '20009',
                'name' => 'Login Date',
                'date' => '2020-07-15 17:31:22.367',
                'created_at' => '2020-07-16 00:07:51.547',
                'updated_at' => '2020-07-16 00:07:51.547',
                'sample_header_id' => '30017',
            ),
            16 => 
            array (
                'id' => '20010',
                'name' => 'To Lab Date',
                'date' => '2020-07-16 00:09:46.773',
                'created_at' => '2020-07-16 00:09:46.803',
                'updated_at' => '2020-07-16 00:09:46.803',
                'sample_header_id' => '30016',
            ),
            17 => 
            array (
                'id' => '20011',
                'name' => 'Login Date',
                'date' => '2020-07-13 15:49:10.297',
                'created_at' => '2020-07-16 00:12:19.067',
                'updated_at' => '2020-07-16 00:12:19.067',
                'sample_header_id' => '30016',
            ),
            18 => 
            array (
                'id' => '20012',
                'name' => 'Login Date',
                'date' => '2020-07-16 14:33:41.883',
                'created_at' => '2020-07-16 14:33:41.923',
                'updated_at' => '2020-07-16 14:33:41.923',
                'sample_header_id' => '30018',
            ),
            19 => 
            array (
                'id' => '20013',
                'name' => 'Processing Date',
                'date' => '2020-07-16 16:33:30.367',
                'created_at' => '2020-07-16 16:33:30.367',
                'updated_at' => '2020-07-16 16:33:30.367',
                'sample_header_id' => '30017',
            ),
            20 => 
            array (
                'id' => '20014',
                'name' => 'Target Date',
                'date' => '2020-07-24 00:00:00.000',
                'created_at' => '2020-07-16 17:32:34.053',
                'updated_at' => '2020-07-16 17:32:34.053',
                'sample_header_id' => '30018',
            ),
            21 => 
            array (
                'id' => '20015',
                'name' => 'To Lab Date',
                'date' => '2020-07-16 18:06:20.717',
                'created_at' => '2020-07-16 18:06:20.747',
                'updated_at' => '2020-07-16 18:06:20.747',
                'sample_header_id' => '30018',
            ),
            22 => 
            array (
                'id' => '20016',
                'name' => 'To Lab Date',
                'date' => '2020-07-16 18:10:23.267',
                'created_at' => '2020-07-16 18:10:23.300',
                'updated_at' => '2020-07-16 18:10:23.300',
                'sample_header_id' => '30018',
            ),
            23 => 
            array (
                'id' => '20017',
                'name' => 'Processing Date',
                'date' => '2020-07-16 18:12:32.037',
                'created_at' => '2020-07-16 18:12:32.037',
                'updated_at' => '2020-07-16 18:12:32.037',
                'sample_header_id' => '30018',
            ),
            24 => 
            array (
                'id' => '20018',
                'name' => 'Processing Date',
                'date' => '2020-07-16 18:14:04.493',
                'created_at' => '2020-07-16 18:14:04.493',
                'updated_at' => '2020-07-16 18:14:04.493',
                'sample_header_id' => '30018',
            ),
            25 => 
            array (
                'id' => '20019',
                'name' => 'Processing Date',
                'date' => '2020-07-16 18:14:53.740',
                'created_at' => '2020-07-16 18:14:53.740',
                'updated_at' => '2020-07-16 18:14:53.740',
                'sample_header_id' => '30018',
            ),
            26 => 
            array (
                'id' => '20020',
                'name' => 'Processing Date',
                'date' => '2020-07-16 18:24:31.553',
                'created_at' => '2020-07-16 18:24:31.557',
                'updated_at' => '2020-07-16 18:24:31.557',
                'sample_header_id' => '30017',
            ),
            27 => 
            array (
                'id' => '20021',
                'name' => 'Processing Date',
                'date' => '2020-07-17 22:03:06.620',
                'created_at' => '2020-07-17 22:03:06.623',
                'updated_at' => '2020-07-17 22:03:06.623',
                'sample_header_id' => '30017',
            ),
            28 => 
            array (
                'id' => '20022',
                'name' => 'Processing Date',
                'date' => '2020-07-17 22:31:44.207',
                'created_at' => '2020-07-17 22:31:44.210',
                'updated_at' => '2020-07-17 22:31:44.210',
                'sample_header_id' => '30017',
            ),
            29 => 
            array (
                'id' => '20023',
                'name' => 'Processing Date',
                'date' => '2020-07-18 05:26:21.617',
                'created_at' => '2020-07-18 05:26:21.620',
                'updated_at' => '2020-07-18 05:26:21.620',
                'sample_header_id' => '30017',
            ),
            30 => 
            array (
                'id' => '20024',
                'name' => 'Processing Date',
                'date' => '2020-07-18 18:51:34.240',
                'created_at' => '2020-07-18 18:51:34.247',
                'updated_at' => '2020-07-18 18:51:34.247',
                'sample_header_id' => '30018',
            ),
            31 => 
            array (
                'id' => '20025',
                'name' => 'Processing Date',
                'date' => '2020-07-18 19:03:49.897',
                'created_at' => '2020-07-18 19:03:49.903',
                'updated_at' => '2020-07-18 19:03:49.903',
                'sample_header_id' => '30018',
            ),
            32 => 
            array (
                'id' => '20026',
                'name' => 'Processing Date',
                'date' => '2020-07-19 17:26:30.617',
                'created_at' => '2020-07-19 17:26:30.623',
                'updated_at' => '2020-07-19 17:26:30.623',
                'sample_header_id' => '30018',
            ),
            33 => 
            array (
                'id' => '20027',
                'name' => 'Login Date',
                'date' => '2020-07-19 18:02:18.427',
                'created_at' => '2020-07-19 18:02:18.537',
                'updated_at' => '2020-07-19 18:02:18.537',
                'sample_header_id' => '30021',
            ),
            34 => 
            array (
                'id' => '20028',
                'name' => 'Target Date',
                'date' => '2020-08-02 00:00:00.000',
                'created_at' => '2020-07-19 18:04:40.387',
                'updated_at' => '2020-08-05 10:02:31.773',
                'sample_header_id' => '30021',
            ),
            35 => 
            array (
                'id' => '20029',
                'name' => 'Processing Date',
                'date' => '2020-07-19 21:36:24.877',
                'created_at' => '2020-07-19 21:36:24.883',
                'updated_at' => '2020-07-19 21:36:24.883',
                'sample_header_id' => '30016',
            ),
            36 => 
            array (
                'id' => '20030',
                'name' => 'Processing Date',
                'date' => '2020-07-20 23:42:41.947',
                'created_at' => '2020-07-20 23:42:41.953',
                'updated_at' => '2020-07-20 23:42:41.953',
                'sample_header_id' => '30016',
            ),
            37 => 
            array (
                'id' => '20031',
                'name' => 'Processing Date',
                'date' => '2020-07-20 23:47:01.147',
                'created_at' => '2020-07-20 23:47:01.150',
                'updated_at' => '2020-07-20 23:47:01.150',
                'sample_header_id' => '30016',
            ),
            38 => 
            array (
                'id' => '20032',
                'name' => 'Login Date',
                'date' => '2020-07-21 07:52:21.247',
                'created_at' => '2020-07-21 07:52:21.357',
                'updated_at' => '2020-07-21 07:52:21.357',
                'sample_header_id' => '30022',
            ),
            39 => 
            array (
                'id' => '20033',
                'name' => 'Processing Date',
                'date' => '2020-07-21 08:00:16.967',
                'created_at' => '2020-07-21 08:00:16.973',
                'updated_at' => '2020-07-21 08:00:16.973',
                'sample_header_id' => '30018',
            ),
            40 => 
            array (
                'id' => '20034',
                'name' => 'Approval Date',
                'date' => '2020-07-21 08:00:17.007',
                'created_at' => '2020-07-21 08:00:17.017',
                'updated_at' => '2020-07-21 08:00:17.017',
                'sample_header_id' => '30018',
            ),
            41 => 
            array (
                'id' => '20035',
                'name' => 'Login Date',
                'date' => '2020-07-21 12:44:16.310',
                'created_at' => '2020-07-21 12:44:16.430',
                'updated_at' => '2020-07-21 12:44:16.430',
                'sample_header_id' => '30023',
            ),
            42 => 
            array (
                'id' => '20036',
                'name' => 'Target Date',
                'date' => '2020-07-29 00:00:00.000',
                'created_at' => '2020-07-21 12:48:30.350',
                'updated_at' => '2020-07-21 13:08:40.907',
                'sample_header_id' => '30023',
            ),
            43 => 
            array (
                'id' => '20037',
                'name' => 'To Lab Date',
                'date' => '2020-07-21 13:03:24.740',
                'created_at' => '2020-07-21 13:03:24.807',
                'updated_at' => '2020-07-21 13:03:24.807',
                'sample_header_id' => '30023',
            ),
            44 => 
            array (
                'id' => '20038',
                'name' => 'Processing Date',
                'date' => '2020-07-21 13:17:58.317',
                'created_at' => '2020-07-21 13:17:58.323',
                'updated_at' => '2020-07-21 13:17:58.323',
                'sample_header_id' => '30023',
            ),
            45 => 
            array (
                'id' => '20039',
                'name' => 'Processing Date',
                'date' => '2020-07-21 13:19:01.440',
                'created_at' => '2020-07-21 13:19:01.447',
                'updated_at' => '2020-07-21 13:19:01.447',
                'sample_header_id' => '30023',
            ),
            46 => 
            array (
                'id' => '20040',
                'name' => 'Processing Date',
                'date' => '2020-07-21 13:20:23.757',
                'created_at' => '2020-07-21 13:20:23.763',
                'updated_at' => '2020-07-21 13:20:23.763',
                'sample_header_id' => '30023',
            ),
            47 => 
            array (
                'id' => '20041',
                'name' => 'Processing Date',
                'date' => '2020-07-21 13:27:55.453',
                'created_at' => '2020-07-21 13:27:55.457',
                'updated_at' => '2020-07-21 13:27:55.457',
                'sample_header_id' => '30023',
            ),
            48 => 
            array (
                'id' => '20042',
                'name' => 'Approval Date',
                'date' => '2020-07-21 13:27:55.513',
                'created_at' => '2020-07-21 13:27:55.523',
                'updated_at' => '2020-07-21 13:27:55.523',
                'sample_header_id' => '30023',
            ),
            49 => 
            array (
                'id' => '20043',
                'name' => 'Email Date',
                'date' => '2020-07-21 13:35:21.640',
                'created_at' => '2020-07-21 13:35:21.647',
                'updated_at' => '2020-07-21 13:35:21.647',
                'sample_header_id' => '30023',
            ),
            50 => 
            array (
                'id' => '20044',
                'name' => 'Login Date',
                'date' => '2020-07-21 15:35:19.710',
                'created_at' => '2020-07-21 15:35:19.830',
                'updated_at' => '2020-07-21 15:35:19.830',
                'sample_header_id' => '30024',
            ),
            51 => 
            array (
                'id' => '20045',
                'name' => 'Target Date',
                'date' => '2020-07-27 00:00:00.000',
                'created_at' => '2020-07-21 15:39:21.477',
                'updated_at' => '2020-07-21 15:42:06.060',
                'sample_header_id' => '30024',
            ),
            52 => 
            array (
                'id' => '20046',
                'name' => 'To Lab Date',
                'date' => '2020-07-21 15:56:29.027',
                'created_at' => '2020-07-21 15:56:29.087',
                'updated_at' => '2020-07-21 15:56:29.087',
                'sample_header_id' => '30024',
            ),
            53 => 
            array (
                'id' => '20047',
                'name' => 'Login Date',
                'date' => '2020-07-23 19:24:41.073',
                'created_at' => '2020-07-23 19:24:41.203',
                'updated_at' => '2020-07-23 19:24:41.203',
                'sample_header_id' => '30025',
            ),
            54 => 
            array (
                'id' => '20048',
                'name' => 'Target Date',
                'date' => '2020-07-30 00:00:00.000',
                'created_at' => '2020-07-24 14:06:35.910',
                'updated_at' => '2020-07-24 18:37:11.977',
                'sample_header_id' => '30022',
            ),
            55 => 
            array (
                'id' => '20049',
                'name' => 'Target Date',
                'date' => '2020-07-29 00:00:00.000',
                'created_at' => '2020-07-26 18:17:16.207',
                'updated_at' => '2020-07-27 02:56:16.650',
                'sample_header_id' => '30025',
            ),
            56 => 
            array (
                'id' => '20050',
                'name' => 'Processing Date',
                'date' => '2020-07-27 04:16:08.350',
                'created_at' => '2020-07-27 04:16:08.357',
                'updated_at' => '2020-07-27 04:16:08.357',
                'sample_header_id' => '30018',
            ),
            57 => 
            array (
                'id' => '20051',
                'name' => 'Processing Date',
                'date' => '2020-07-27 04:21:35.230',
                'created_at' => '2020-07-27 04:21:35.237',
                'updated_at' => '2020-07-27 04:21:35.237',
                'sample_header_id' => '30018',
            ),
            58 => 
            array (
                'id' => '20052',
                'name' => 'Processing Date',
                'date' => '2020-07-27 20:57:40.107',
                'created_at' => '2020-07-27 20:57:40.113',
                'updated_at' => '2020-07-27 20:57:40.113',
                'sample_header_id' => '30016',
            ),
            59 => 
            array (
                'id' => '20053',
                'name' => 'Processing Date',
                'date' => '2020-07-27 20:58:52.537',
                'created_at' => '2020-07-27 20:58:52.543',
                'updated_at' => '2020-07-27 20:58:52.543',
                'sample_header_id' => '30016',
            ),
            60 => 
            array (
                'id' => '20054',
                'name' => 'Login Date',
                'date' => '2020-08-05 09:50:48.017',
                'created_at' => '2020-08-05 09:50:48.153',
                'updated_at' => '2020-08-05 09:50:48.153',
                'sample_header_id' => '30026',
            ),
            61 => 
            array (
                'id' => '20055',
                'name' => 'Target Date',
                'date' => '2020-08-09 00:00:00.000',
                'created_at' => '2020-08-05 09:52:05.233',
                'updated_at' => '2020-08-05 09:53:12.937',
                'sample_header_id' => '30026',
            ),
            62 => 
            array (
                'id' => '20056',
                'name' => 'To Lab Date',
                'date' => '2020-08-05 09:54:36.170',
                'created_at' => '2020-08-05 09:54:36.240',
                'updated_at' => '2020-08-05 09:54:36.240',
                'sample_header_id' => '30026',
            ),
            63 => 
            array (
                'id' => '20057',
                'name' => 'Processing Date',
                'date' => '2020-08-05 10:04:10.347',
                'created_at' => '2020-08-05 10:04:10.353',
                'updated_at' => '2020-08-05 10:04:10.353',
                'sample_header_id' => '30026',
            ),
            64 => 
            array (
                'id' => '20058',
                'name' => 'Login Date',
                'date' => '2020-08-05 11:11:28.410',
                'created_at' => '2020-08-05 11:11:28.520',
                'updated_at' => '2020-08-05 11:11:28.520',
                'sample_header_id' => '30027',
            ),
            65 => 
            array (
                'id' => '20059',
                'name' => 'Target Date',
                'date' => '2020-08-14 00:00:00.000',
                'created_at' => '2020-08-05 11:12:08.297',
                'updated_at' => '2020-08-05 11:16:35.087',
                'sample_header_id' => '30027',
            ),
        ));
        
        
    }
}