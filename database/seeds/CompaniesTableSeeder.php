<?php

use Illuminate\Database\Seeder;

class CompaniesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('companies')->delete();
        
        \DB::table('companies')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'KRA',
                'logo' => '/storage/companies/VkLi99GOkIiDOHHZLVVO5FpDmYVWX4LK4HTZl8y8.png',
                'location' => 'Times Tower, Nairobi, Kenya',
                'address' => 'Times Tower',
                'country_id' => '110',
                'website' => 'https://kra.go.ke/',
                'created_at' => '2020-04-14 20:28:26.567',
                'updated_at' => '2020-07-04 11:05:27.763',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Kenya Cuttings',
                'logo' => '/storage/companies/skIbtdQXvTTKGdukLHgfuLRIHWD2kNbXe0MJkHfT.png',
                'location' => 'Avenue 5 building, Rose Avenue 6th Floor Nairobi  Kenya',
                'address' => 'P.O Box 30393 - 00100
Nairobi',
                'country_id' => '110',
                'website' => 'https://www.syngenta.co.ke/',
                'created_at' => '2020-07-04 11:03:00.187',
                'updated_at' => '2020-07-04 11:03:00.187',
            ),
        ));
        
        
    }
}