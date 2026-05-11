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
            'ug/L',
            'g/L',
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
            'mS/cm',
            'pH',
            'NTU',
            'CFU/mL',
            'CFU/g',
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
