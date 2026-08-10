<?php

namespace Database\Seeders;

use App\TypeOfAnalysis;
use Illuminate\Database\Seeder;

class TypesOfAnalysisSeeder extends Seeder
{
    public function run(): void
    {
        foreach (TypeOfAnalysis::defaultNames() as $index => $name) {
            TypeOfAnalysis::query()->updateOrCreate(
                ['name' => $name],
                [
                    'sort_order' => $index + 1,
                    'active' => true,
                ]
            );
        }
    }
}
