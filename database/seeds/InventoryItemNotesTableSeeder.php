<?php

use Illuminate\Database\Seeder;

class InventoryItemNotesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('inventory_item_notes')->delete();
        
        \DB::table('inventory_item_notes')->insert(array (
            0 => 
            array (
                'id' => '1',
                'inventory_item_id' => '20024',
                'comments' => 'Items are in good condition',
                'document' => '/storage/inventory_notes/puKJAixZukNQEPqmXsfNIu6sNhsmJDZFc0zuYv9G.jpeg',
                'created_at' => '2020-06-21 20:25:31.390',
                'updated_at' => '2020-06-21 20:25:31.390',
            ),
            1 => 
            array (
                'id' => '2',
                'inventory_item_id' => '20025',
                'comments' => 'Good Condition',
                'document' => '/storage/inventory_notes/1Olk8IljkXG3fYqzTMK80ods4WfhqrS7aRbcOSAJ.png',
                'created_at' => '2020-06-21 20:47:30.477',
                'updated_at' => '2020-06-21 20:47:30.477',
            ),
            2 => 
            array (
                'id' => '3',
                'inventory_item_id' => '20026',
                'comments' => 'Good Condition',
                'document' => '/storage/inventory_notes/uZ6N9wUmQxvWjzrzoqKBtmMMzirNl8N7jVXgNJXJ.png',
                'created_at' => '2020-06-21 20:49:44.097',
                'updated_at' => '2020-06-21 20:49:44.097',
            ),
            3 => 
            array (
                'id' => '4',
                'inventory_item_id' => '20027',
                'comments' => 'Good Condition',
                'document' => '/storage/inventory_notes/py9xqZIik0AxV05DtdYD3B9AlSpOCdTcNNqtWGoH.png',
                'created_at' => '2020-06-21 20:49:55.030',
                'updated_at' => '2020-06-21 20:49:55.030',
            ),
            4 => 
            array (
                'id' => '5',
                'inventory_item_id' => '20028',
                'comments' => 'Good Condition',
                'document' => '/storage/inventory_notes/2zPq8wZ4MlmNFtGuuMffTtH1DJTYWoMIxIJPygrE.png',
                'created_at' => '2020-06-21 20:50:25.857',
                'updated_at' => '2020-06-21 20:50:25.857',
            ),
            5 => 
            array (
                'id' => '6',
                'inventory_item_id' => '20029',
                'comments' => 'Good Condition',
                'document' => '/storage/inventory_notes/OOd4dXSNU4j5RBQzKBTRew7zoHaQ9rnaUdPXgtsM.png',
                'created_at' => '2020-06-21 20:50:55.913',
                'updated_at' => '2020-06-21 20:50:55.913',
            ),
            6 => 
            array (
                'id' => '7',
                'inventory_item_id' => '20030',
                'comments' => 'Good Condition',
                'document' => '/storage/inventory_notes/weaqy0J8bespxsATwnhPPzZOLRGabrdXTtejK2Q3.png',
                'created_at' => '2020-06-21 20:51:48.210',
                'updated_at' => '2020-06-21 20:51:48.210',
            ),
            7 => 
            array (
                'id' => '8',
                'inventory_item_id' => '20031',
                'comments' => 'Good Condition',
                'document' => '/storage/inventory_notes/sJQph6jPpYsCVeP9p58Dm31C0nXDjqMGRNIOh11z.png',
                'created_at' => '2020-06-21 20:52:16.230',
                'updated_at' => '2020-06-21 20:52:16.230',
            ),
            8 => 
            array (
                'id' => '9',
                'inventory_item_id' => '20032',
                'comments' => 'Test',
                'document' => '/storage/inventory_notes/nyLZoRrO0V1XdsFEKdKUnz3WGejO2PMm3nUGRmrK.png',
                'created_at' => '2020-06-21 20:54:11.310',
                'updated_at' => '2020-06-21 20:54:11.310',
            ),
            9 => 
            array (
                'id' => '11',
                'inventory_item_id' => '20041',
                'comments' => 'Items were found in the wrong box.',
                'document' => NULL,
                'created_at' => '2020-06-21 21:53:57.643',
                'updated_at' => '2020-06-21 21:53:57.643',
            ),
            10 => 
            array (
                'id' => '12',
                'inventory_item_id' => '20043',
                'comments' => 'Items way way beyond their expiry',
                'document' => '/storage/inventory_notes/PEJP5c5NDChn2UlHY34WfsUXBaFwrNisEitULsBu.jpeg',
                'created_at' => '2020-06-21 22:17:15.343',
                'updated_at' => '2020-06-21 22:17:15.343',
            ),
            11 => 
            array (
                'id' => '13',
                'inventory_item_id' => '20044',
                'comments' => 'Test 3',
                'document' => '/storage/inventory_notes/br1d2rl2ZZFlGO7JEuJ3kmgp2Zoyq0SKrcv0W45W.jpeg',
                'created_at' => '2020-06-21 22:18:47.177',
                'updated_at' => '2020-06-21 22:18:47.177',
            ),
            12 => 
            array (
                'id' => '14',
                'inventory_item_id' => '20051',
                'comments' => 'Goods in good condition',
                'document' => '/storage/inventory_notes/L6fxYMenZ2daO8n2LiqurfQeIK6Cl46zRNhizKta.png',
                'created_at' => '2020-06-22 01:42:55.737',
                'updated_at' => '2020-06-22 01:42:55.737',
            ),
            13 => 
            array (
                'id' => '10011',
                'inventory_item_id' => '30047',
                'comments' => 'Colman is checking',
                'document' => '/storage/inventory_notes/n83fQBOOST4zcJxsoH1G81OzsRymR8hbUDxh7pIQ.png',
                'created_at' => '2020-06-22 10:08:08.357',
                'updated_at' => '2020-06-22 10:08:08.357',
            ),
            14 => 
            array (
                'id' => '10012',
                'inventory_item_id' => '30048',
                'comments' => 'some items found missing',
                'document' => '/storage/inventory_notes/14RcG4xOf3ZE02Wyy7XHtGOcts8iK4sW2RIrXPBi.png',
                'created_at' => '2020-06-22 10:09:54.283',
                'updated_at' => '2020-06-22 10:09:54.283',
            ),
            15 => 
            array (
                'id' => '10013',
                'inventory_item_id' => '30049',
                'comments' => 'Goods in good condition',
                'document' => '/storage/inventory_notes/GMIu5LLAAxX79VqylCkJN0ZmKy2kPmfVwe9FjyaL.png',
                'created_at' => '2020-06-22 10:22:43.950',
                'updated_at' => '2020-06-22 10:22:43.950',
            ),
            16 => 
            array (
                'id' => '10014',
                'inventory_item_id' => '30050',
                'comments' => 'Items have expired',
                'document' => '/storage/inventory_notes/Z86PVlyEh6cCSpaGnE1SS8lR7z1m63VJQB4M9Wd0.png',
                'created_at' => '2020-06-22 10:32:09.853',
                'updated_at' => '2020-06-22 10:32:09.853',
            ),
            17 => 
            array (
                'id' => '10015',
                'inventory_item_id' => '30052',
                'comments' => 'goods okay',
                'document' => '/storage/inventory_notes/nEIMeruHt8bhz5SgZB8DQGxMYGPGsBUs9neQmig3.jpeg',
                'created_at' => '2020-07-03 13:08:12.980',
                'updated_at' => '2020-07-03 13:08:12.980',
            ),
            18 => 
            array (
                'id' => '10016',
                'inventory_item_id' => '30054',
                'comments' => '300 items were not found',
                'document' => '/storage/inventory_notes/XJkWMXKQkClAqBNKn6Wox8r46hkoCKdgE3yVdmYU.png',
                'created_at' => '2020-07-03 13:17:25.140',
                'updated_at' => '2020-07-03 13:17:25.140',
            ),
            19 => 
            array (
                'id' => '20015',
                'inventory_item_id' => '40052',
                'comments' => 'Goods are okay',
                'document' => '/storage/inventory_notes/BAQUSaroXX2jZi7K1DppiabmWyY1fKoW9HAeZoTx.png',
                'created_at' => '2020-07-21 12:30:06.367',
                'updated_at' => '2020-07-21 12:30:06.367',
            ),
            20 => 
            array (
                'id' => '20016',
                'inventory_item_id' => '40136',
                'comments' => 'Sampling Completed',
                'document' => '/storage/inventory_notes/P3n0i3F74nwPnJ9cIhacldwNe9jwgcDHrP979p4R.png',
                'created_at' => '2020-08-05 11:17:26.860',
                'updated_at' => '2020-08-05 11:17:26.860',
            ),
        ));
        
        
    }
}