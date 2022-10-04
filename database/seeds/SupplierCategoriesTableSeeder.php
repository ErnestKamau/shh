<?php

use Illuminate\Database\Seeder;

class SupplierCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('supplier_categories')->delete();
        
        \DB::table('supplier_categories')->insert(array (
            0 => 
            array (
                'id' => '3',
                'supplier_id' => '2',
                'inventory_sub_category_id' => '1',
                'created_at' => '2020-05-25 13:02:29.470',
                'updated_at' => '2020-08-05 12:05:18.367',
                'supplier_image' => '/storage/suppliers-items/sdCCikn1UnIXy4Ql1i8F5vwvtN30FITUQzg0EO4M.jpeg',
            ),
            1 => 
            array (
                'id' => '20002',
                'supplier_id' => '3',
                'inventory_sub_category_id' => '2',
                'created_at' => '2020-06-08 22:11:49.587',
                'updated_at' => '2020-06-08 22:11:49.587',
                'supplier_image' => '/images/no-logo.png',
            ),
            2 => 
            array (
                'id' => '30002',
                'supplier_id' => '2',
                'inventory_sub_category_id' => '10002',
                'created_at' => '2020-06-21 10:20:37.753',
                'updated_at' => '2020-08-05 12:05:42.407',
                'supplier_image' => '/storage/suppliers-items/3UfXX6oqYs23Z5ufS9PPjbohg3aBGami50ouojnm.jpeg',
            ),
            3 => 
            array (
                'id' => '30003',
                'supplier_id' => '3',
                'inventory_sub_category_id' => '1',
                'created_at' => '2020-06-21 10:50:49.740',
                'updated_at' => '2020-06-21 10:50:49.740',
                'supplier_image' => '/images/no-logo.png',
            ),
            4 => 
            array (
                'id' => '30004',
                'supplier_id' => '3',
                'inventory_sub_category_id' => '10002',
                'created_at' => '2020-06-21 10:50:49.757',
                'updated_at' => '2020-06-21 10:50:49.757',
                'supplier_image' => '/images/no-logo.png',
            ),
            5 => 
            array (
                'id' => '40002',
                'supplier_id' => '4',
                'inventory_sub_category_id' => '20035',
                'created_at' => '2020-08-04 21:35:32.963',
                'updated_at' => '2020-08-04 21:35:32.963',
                'supplier_image' => '/images/no-logo.png',
            ),
            6 => 
            array (
                'id' => '40003',
                'supplier_id' => '4',
                'inventory_sub_category_id' => '1',
                'created_at' => '2020-08-04 21:35:33.017',
                'updated_at' => '2020-08-04 22:00:22.853',
                'supplier_image' => '/storage/suppliers-items/yI0C4hbzpb13WJMuBnPfGt8lePXyu6xK2hdhY299.png',
            ),
            7 => 
            array (
                'id' => '40004',
                'supplier_id' => '4',
                'inventory_sub_category_id' => '10002',
                'created_at' => '2020-08-04 21:35:33.060',
                'updated_at' => '2020-08-04 21:35:33.060',
                'supplier_image' => '/images/no-logo.png',
            ),
        ));
        
        
    }
}