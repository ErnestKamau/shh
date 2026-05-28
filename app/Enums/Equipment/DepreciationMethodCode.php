<?php

namespace App\Enums\Equipment;

enum DepreciationMethodCode: string
{
    case StraightLine = 'straight_line';
    case DecliningBalance = 'declining_balance';
    case UnitsOfProduction = 'units_of_production';
    case SumOfYearsDigits = 'syd';
}
