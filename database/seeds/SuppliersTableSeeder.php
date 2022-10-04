<?php

use Illuminate\Database\Seeder;

class SuppliersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('suppliers')->delete();
        
        \DB::table('suppliers')->insert(array (
            0 => 
            array (
                'id' => '2',
                'name' => 'Nuvemite Technologies',
                'logo' => '/storage/suppliers/8DdOTfgsTa1i7jXg9RqnkJp9ZnHMOXOlHYoXKlhL.jpeg',
                'email' => 'colman@nuvemite.com',
                'phone' => '0789239852',
                'building' => 'Victoria Hse',
                'street' => 'Muguga Green',
                'town' => 'Nairobi',
                'address' => 'Some address goes here.',
                'created_at' => '2020-04-16 02:39:52.983',
                'updated_at' => '2020-08-03 18:40:23.230',
                'company_id' => '1',
                'active' => '1',
                'inventory_location_id' => '3',
            ),
            1 => 
            array (
                'id' => '3',
                'name' => 'TenderSoko Ltd',
                'logo' => '/storage/suppliers/5PEQu5UiqRdzCwLiVYFIMIu70qdxczVnsIBq3xUU.png',
                'email' => 'david.kimari@gmail.com',
                'phone' => '+254727500128',
                'building' => 'Victoria Hse',
                'street' => '8543',
                'town' => 'Nairobi',
                'address' => 'Something something',
                'created_at' => '2020-05-21 03:58:46.357',
                'updated_at' => '2020-08-03 18:40:38.333',
                'company_id' => '1',
                'active' => '1',
                'inventory_location_id' => '3',
            ),
            2 => 
            array (
                'id' => '4',
                'name' => 'Internal',
                'logo' => '/storage/suppliers/famsdjgLm0KeIN3PjTsZLfYDzgisgvJDursDnANV.jpeg',
                'email' => 'internal@test.com',
                'phone' => '0727500128',
                'building' => 'Same as company',
                'street' => 'Same as company',
                'town' => 'Same as company',
                'address' => 'Same as company',
                'created_at' => '2020-07-24 13:19:02.170',
                'updated_at' => '2020-07-24 13:19:02.170',
                'company_id' => '1',
                'active' => '1',
                'inventory_location_id' => '3',
            ),
        ));
        
        
    }
}