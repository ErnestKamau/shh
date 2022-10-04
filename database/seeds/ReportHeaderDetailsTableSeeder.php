<?php

use Illuminate\Database\Seeder;

class ReportHeaderDetailsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('report_header_details')->delete();
        
        \DB::table('report_header_details')->insert(array (
            0 => 
            array (
                'id' => '1',
                'title' => 'This is a test report',
                'to' => 'Chief Engineer',
                'cc' => 'Unit Manager',
                'date' => '2020-07-17',
                'ref' => 'BCCB00070023',
                're' => '<p>This is a Re description</p>',
                'for' => 'Chief Engineer',
                'sample_header_id' => '30017',
                'specific_analyst_id' => '2',
                'approved_by_id' => '2',
                'verified_by_id' => '2',
                'model' => 'App\\SampleHeader',
                'model_id' => '30017',
                'created_at' => '2020-07-17 22:01:04.123',
                'updated_at' => '2020-07-18 05:26:21.557',
                'from' => 'Chief Manager',
                'outgoing_email_body' => '<p>Hi Chief,<br /><br />Please find attached the report. Please get back to me after reviewing it.</p>',
            ),
            1 => 
            array (
                'id' => '2',
                'title' => 'This is a test report',
                'to' => 'Chief Engineer',
                'cc' => 'Unit Manager',
                'date' => '2020-07-17',
                'ref' => 'BCCB00070023',
                're' => '<p>This is a Re description</p>',
                'for' => 'Chief Engineer',
                'sample_header_id' => '30017',
                'specific_analyst_id' => '2',
                'approved_by_id' => '2',
                'verified_by_id' => '2',
                'model' => 'App\\CRMCustomer',
                'model_id' => '4',
                'created_at' => '2020-07-17 22:31:44.150',
                'updated_at' => '2020-07-17 22:31:44.167',
                'from' => 'Chief Manager',
                'outgoing_email_body' => NULL,
            ),
            2 => 
            array (
                'id' => '3',
                'title' => 'This is a test report',
                'to' => 'Chief Engineer',
                'cc' => 'Unit Manager',
                'date' => '2020-07-17',
                'ref' => 'XTG1234',
                're' => '<p>Title 1<br /><strong><span style="color: #ba372a;">Title 2</span></strong><br /><strong><span style="color: #ba372a;">Title 3</span></strong></p>',
                'for' => 'Chief Engineer',
                'sample_header_id' => '30018',
                'specific_analyst_id' => '3',
                'approved_by_id' => '2',
                'verified_by_id' => '2',
                'model' => 'App\\SampleHeader',
                'model_id' => '30018',
                'created_at' => '2020-07-18 18:51:34.110',
                'updated_at' => '2020-07-21 08:00:16.820',
                'from' => 'Chief Manager',
                'outgoing_email_body' => '<p>Email Body</p>',
            ),
            3 => 
            array (
                'id' => '7',
                'title' => 'This is a test report',
                'to' => 'Chief Engineer',
                'cc' => 'Unit Manager',
                'date' => '2020-07-17',
                'ref' => 'RN 0789',
                're' => '<p>This is a Re description</p>',
                'for' => 'Chief Engineer',
                'sample_header_id' => '30016',
                'specific_analyst_id' => '3',
                'approved_by_id' => '2',
                'verified_by_id' => '2',
                'model' => 'App\\SampleHeader',
                'model_id' => '30016',
                'created_at' => '2020-07-19 21:36:24.690',
                'updated_at' => '2020-07-20 23:42:41.617',
                'from' => 'Chief Manager',
                'outgoing_email_body' => '<p>This is it</p>',
            ),
            4 => 
            array (
                'id' => '8',
                'title' => 'This is a test report',
                'to' => 'Chief Engineer',
                'cc' => 'Unit Manager',
                'date' => '2020-07-17',
                'ref' => 'RN 0789',
                're' => '<p>This is a Re description</p>',
                'for' => 'Chief Engineer',
                'sample_header_id' => '30016',
                'specific_analyst_id' => '3',
                'approved_by_id' => '2',
                'verified_by_id' => '2',
                'model' => 'App\\CRMCustomer',
                'model_id' => '4',
                'created_at' => '2020-07-20 23:42:41.687',
                'updated_at' => '2020-07-20 23:42:41.740',
                'from' => 'Chief Manager',
                'outgoing_email_body' => '<p>This is it</p>',
            ),
            5 => 
            array (
                'id' => '9',
                'title' => 'This is a test report',
                'to' => 'Chief Engineer',
                'cc' => 'Unit Manager',
                'date' => '2020-07-17',
                'ref' => 'RN 0879',
                're' => '<p>This is a Re description</p>',
                'for' => 'Chief Engineer',
                'sample_header_id' => '30023',
                'specific_analyst_id' => '3',
                'approved_by_id' => '0',
                'verified_by_id' => '2',
                'model' => 'App\\SampleHeader',
                'model_id' => '30023',
                'created_at' => '2020-07-21 13:27:55.360',
                'updated_at' => '2020-07-21 13:27:55.360',
                'from' => 'Chief Manager',
                'outgoing_email_body' => '<p>Dear Sir,<br /><br />Please find attached the reports for your samples</p>',
            ),
        ));
        
        
    }
}