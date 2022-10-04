<?php

use Illuminate\Database\Seeder;

class ApprovalsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('approvals')->delete();
        
        \DB::table('approvals')->insert(array (
            0 => 
            array (
                'id' => '1',
                'title' => 'Line Manager Approval',
                'for' => 'Requisition',
                'stage' => 'Material Requisition',
                'role_id' => '2',
                'level' => '1',
                'created_at' => '2020-07-04 15:11:58.233',
                'updated_at' => '2020-07-04 16:11:05.247',
                'inventory_location_id' => '3',
            ),
            1 => 
            array (
                'id' => '2',
                'title' => 'Acceptance',
                'for' => 'Requisition',
                'stage' => 'Request for Quotation',
                'role_id' => '3',
                'level' => '1',
                'created_at' => '2020-07-05 19:52:34.780',
                'updated_at' => '2020-07-06 04:43:58.330',
                'inventory_location_id' => '3',
            ),
            2 => 
            array (
                'id' => '3',
                'title' => 'Approval 1',
                'for' => 'Requisition',
                'stage' => 'Purchase Orders',
                'role_id' => '3',
                'level' => '2',
                'created_at' => '2020-07-05 19:52:56.153',
                'updated_at' => '2020-07-06 04:47:43.347',
                'inventory_location_id' => '3',
            ),
            3 => 
            array (
                'id' => '4',
                'title' => 'Approval 2',
                'for' => 'Requisition',
                'stage' => 'Purchase Orders',
                'role_id' => '2',
                'level' => '2',
                'created_at' => '2020-07-05 19:53:09.777',
                'updated_at' => '2020-07-05 19:53:09.777',
                'inventory_location_id' => '3',
            ),
            4 => 
            array (
                'id' => '5',
                'title' => 'Approval 3',
                'for' => 'Requisition',
                'stage' => 'Purchase Orders',
                'role_id' => '2',
                'level' => '3',
                'created_at' => '2020-07-05 19:53:21.097',
                'updated_at' => '2020-07-05 19:53:21.097',
                'inventory_location_id' => '3',
            ),
            5 => 
            array (
                'id' => '6',
                'title' => 'Logistics Approval',
                'for' => 'Requisition',
                'stage' => 'Purchase Orders',
                'role_id' => '2',
                'level' => '1',
                'created_at' => '2020-07-06 04:47:28.213',
                'updated_at' => '2020-07-06 04:47:35.227',
                'inventory_location_id' => '3',
            ),
            6 => 
            array (
                'id' => '10002',
                'title' => 'Store Manager Approval',
                'for' => 'Requisition',
                'stage' => 'Goods Receipt',
                'role_id' => '3',
                'level' => '1',
                'created_at' => '2020-07-13 00:26:27.837',
                'updated_at' => '2020-07-13 00:45:25.493',
                'inventory_location_id' => '3',
            ),
        ));
        
        
    }
}