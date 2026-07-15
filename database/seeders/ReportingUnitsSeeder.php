<?php

namespace Database\Seeders;

use App\ReportingUnit;
use Illuminate\Database\Seeder;

class ReportingUnitsSeeder extends Seeder
{
    /**
     * Seed the application's reporting units.
     */
    public function run(): void
    {
        $units = [
            'deg C',
            'deg F',
            'K',
            '%RH',
            'ppm',
            'ppb',
            'mg/L',
            'mg/kg',
            'ug/L',
            'µg/kg',
            'g/L',
            'g/100g',
            'g/100g, %',
            'g',
            'kg',
            'mL',
            'L',
            'm3',
            'bar',
            'psi',
            'Pa',
            'kPa',
            'mbar',
            'V',
            'mV',
            'A',
            'mA',
            'uS/cm',
            'µS/cm',
            'mS/cm',
            'pH',
            'NTU',
            'Pt-Co',
            'CFU/mL',
            'CFU/g',
            'CFU/L',
            'CFU/m3',
            'CFU/100 mL',
            'CFU/swab or CFU/cm2',
            'MPN/g',
            '/25g',
            '/swab or /cm2',
            '%',
            '°C',
            'sec',
            'min',
            'hr',
            'day',
        ];

        foreach ($units as $unit) {
            ReportingUnit::updateOrCreate(
                ['name' => $unit],
                ['active' => true]
            );
        }
    }
}
