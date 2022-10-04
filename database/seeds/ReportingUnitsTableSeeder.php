<?php

use Illuminate\Database\Seeder;

class ReportingUnitsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('reporting_units')->delete();
        
        \DB::table('reporting_units')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'ppm',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'water',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'meq/100g',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            3 => 
            array (
                'id' => '4',
                'name' => '%',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            4 => 
            array (
                'id' => '5',
                'name' => 'ppb',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            5 => 
            array (
                'id' => '6',
                'name' => 'mS/cm',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            6 => 
            array (
                'id' => '7',
                'name' => 'NTU',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            7 => 
            array (
                'id' => '8',
                'name' => 'mg/l',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            8 => 
            array (
                'id' => '9',
            'name' => '(% salts as Na)',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            9 => 
            array (
                'id' => '10',
                'name' => 'C',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            10 => 
            array (
                'id' => '11',
                'name' => 'cfu/100 ml',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            11 => 
            array (
                'id' => '12',
                'name' => 'g/kg',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            12 => 
            array (
                'id' => '13',
                'name' => 'mS cm -1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            13 => 
            array (
                'id' => '14',
                'name' => 'TCU',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            14 => 
            array (
                'id' => '15',
                'name' => 'ug/kg',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            15 => 
            array (
                'id' => '16',
                'name' => 'ug/l',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            16 => 
            array (
                'id' => '17',
                'name' => '% DW',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            17 => 
            array (
                'id' => '18',
                'name' => '100 ml',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            18 => 
            array (
                'id' => '19',
                'name' => '% Saturation',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            19 => 
            array (
                'id' => '20',
                'name' => 'mg/kg',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            20 => 
            array (
                'id' => '21',
                'name' => 'g of dry roots',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            21 => 
            array (
                'id' => '22',
                'name' => 'mmol/l',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            22 => 
            array (
                'id' => '23',
                'name' => 'Kg/Ha',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            23 => 
            array (
                'id' => '24',
                'name' => 't/Ha',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            24 => 
            array (
                'id' => '25',
                'name' => 'H.U',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            25 => 
            array (
                'id' => '26',
                'name' => 'cfu/25g',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            26 => 
            array (
                'id' => '27',
                'name' => 'cfu/g',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            27 => 
            array (
                'id' => '28',
                'name' => 'cfu/ml',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            28 => 
            array (
                'id' => '29',
                'name' => 'Kg/t',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            29 => 
            array (
                'id' => '30',
                'name' => 'me%',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            30 => 
            array (
                'id' => '31',
                'name' => 'H2O',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            31 => 
            array (
                'id' => '32',
                'name' => 'uS/cm',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            32 => 
            array (
                'id' => '33',
                'name' => '0 C',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            33 => 
            array (
                'id' => '34',
                'name' => ' TCU',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            34 => 
            array (
                'id' => '35',
                'name' => 'cfu/100ml',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            35 => 
            array (
                'id' => '36',
                'name' => 'pH',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            36 => 
            array (
                'id' => '37',
                'name' => 'EC',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            37 => 
            array (
                'id' => '38',
                'name' => 'mScm',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            38 => 
            array (
                'id' => '39',
                'name' => 'g/cm3',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            39 => 
            array (
                'id' => '40',
                'name' => 'me/l',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            40 => 
            array (
                'id' => '41',
                'name' => 'mmol',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            41 => 
            array (
                'id' => '42',
                'name' => 'kg',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            42 => 
            array (
                'id' => '43',
                'name' => 'g',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            43 => 
            array (
                'id' => '44',
                'name' => 'g/tree',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            44 => 
            array (
                'id' => '45',
                'name' => 'NULL',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            45 => 
            array (
                'id' => '46',
                'name' => 'mS cm -2',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            46 => 
            array (
                'id' => '47',
                'name' => 'mS cm -3',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            47 => 
            array (
                'id' => '48',
                'name' => 'mS cm -4',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            48 => 
            array (
                'id' => '49',
                'name' => 'mS cm -5',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            49 => 
            array (
                'id' => '50',
                'name' => 'MJ/Kg',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            50 => 
            array (
                'id' => '51',
                'name' => 'Kg/Acre',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            51 => 
            array (
                'id' => '52',
                'name' => 'g/m2',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            52 => 
            array (
                'id' => '53',
                'name' => 'Bags/Acre',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            53 => 
            array (
                'id' => '54',
                'name' => '5g',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            54 => 
            array (
                'id' => '55',
                'name' => '1000 ml',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            55 => 
            array (
                'id' => '56',
                'name' => 'cfu/25 ml',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            56 => 
            array (
                'id' => '57',
                'name' => 'PCM',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            57 => 
            array (
                'id' => '58',
                'name' => 'cfu',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            58 => 
            array (
                'id' => '59',
                'name' => 'mpn/100ml',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            59 => 
            array (
                'id' => '60',
                'name' => '5 gm ',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            60 => 
            array (
                'id' => '61',
                'name' => 'mpn/g',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            61 => 
            array (
                'id' => '62',
                'name' => '3000 ml',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            62 => 
            array (
                'id' => '63',
                'name' => 'in 25g',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            63 => 
            array (
                'id' => '64',
                'name' => 'in 100ml',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            64 => 
            array (
                'id' => '65',
                'name' => 'us cm -1',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            65 => 
            array (
                'id' => '67',
                'name' => 'Colman',
                'active' => '0',
                'created_at' => '2020-04-22 10:28:57.013',
                'updated_at' => '2020-04-22 10:29:15.293',
            ),
            66 => 
            array (
                'id' => '10001',
                'name' => 'FT',
                'active' => '1',
                'created_at' => '2020-06-26 13:02:22.030',
                'updated_at' => '2020-06-26 13:02:22.030',
            ),
            67 => 
            array (
                'id' => '10002',
                'name' => 'g/ml',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            68 => 
            array (
                'id' => '10004',
                'name' => 'v/v',
                'active' => '1',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            69 => 
            array (
                'id' => '10005',
            'name' => 'Piece(s)',
                'active' => '1',
                'created_at' => '2020-07-31 18:55:29.477',
                'updated_at' => '2020-07-31 18:55:29.477',
            ),
        ));
        
        
    }
}