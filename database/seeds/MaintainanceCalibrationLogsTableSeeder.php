<?php

use Illuminate\Database\Seeder;

class MaintainanceCalibrationLogsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('maintainance_calibration_logs')->delete();
        
        \DB::table('maintainance_calibration_logs')->insert(array (
            0 => 
            array (
                'id' => '2',
                'equipment_id' => '2',
                'service_provider' => 'TenderSoko LTD',
                'notes' => 'This is a test',
                'type' => 'Maintainance',
                'date' => '2020-06-17',
                'certificate' => '/storage/certificate/Xfpki8gOXqKlJ6OCjOOXUH0WozBP4uo9hsxa76GN.jpeg',
                'overseen_by' => '1',
                'edit_by' => '1',
                'created_at' => '2020-06-17 00:31:47.287',
                'updated_at' => '2020-06-28 09:51:29.267',
                'maintainance_notification_in_days' => '0',
                'calibration_notification_in_days' => '0',
            ),
            1 => 
            array (
                'id' => '3',
                'equipment_id' => '2',
                'service_provider' => 'JapakGIS LTD',
                'notes' => 'This is a major question that I got for you guys.',
                'type' => 'Calibration',
                'date' => '2020-06-15',
                'certificate' => '/storage/certificate/jTIbMhD7cHBIXAljY43VODVGfX4NMfdm2eTTYeUN.png',
                'overseen_by' => '1',
                'edit_by' => '1',
                'created_at' => '2020-06-17 01:02:57.260',
                'updated_at' => '2020-06-17 03:14:52.093',
                'maintainance_notification_in_days' => '0',
                'calibration_notification_in_days' => '0',
            ),
        ));
        
        
    }
}