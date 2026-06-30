<?php

/**
 * Food + Food and Feed taxonomy: 3 + 7 analysis types, 5 elements each (50 total).
 * Codes use spaces and "and" (no &, _, -). E. coli analyte code kept as "E. coli".
 *
 * @return list<array<string, mixed>>
 */
$microTests = [
    [
        'analyte_name' => 'Mesophilic Aerobic Plate Count',
        'analyte_code' => 'MESOPHILIC AEROBIC PLATE COUNT',
        'method' => 'AMS/M/SOP/023',
        'reporting_unit' => 'CFU/g',
        'decimal_places' => 1,
    ],
    [
        'analyte_name' => 'Enterobacteriaceae',
        'analyte_code' => 'ENTEROBACTERIACEAE',
        'method' => 'AMS/M/SOP/025',
        'reporting_unit' => 'CFU/g',
        'decimal_places' => 1,
    ],
    [
        'analyte_name' => 'E. coli',
        'analyte_code' => 'E. coli',
        'method' => 'AMS/M/SOP/026',
        'reporting_unit' => 'CFU/g',
        'decimal_places' => 1,
    ],
    [
        'analyte_name' => 'Coliforms',
        'analyte_code' => 'COLIFORMS',
        'method' => 'AMS/M/SOP/024',
        'reporting_unit' => 'CFU/g',
        'decimal_places' => 1,
    ],
    [
        'analyte_name' => 'Yeast and Molds',
        'analyte_code' => 'YEAST AND MOLDS',
        'method' => 'AMS/M/SOP/028',
        'reporting_unit' => 'CFU/g',
        'decimal_places' => 1,
    ],
];

$foodAndFeedAnalysisTypes = [
    ['GENERAL FOODS', 'General Foods'],
    ['CEREAL AND CEREAL PRODUCTS', 'Cereal and Cereal Products'],
    ['SPICE AND CONDIMENTS', 'Spice and condiments'],
    ['FRUITS AND VEGETABLES', 'Fruits and Vegetables'],
    ['BEVERAGE', 'Beverage'],
    ['MEAT AND POULTRY', 'Meat and Poultry'],
    ['SEA FOOD', 'Sea Food'],
];

$rows = [];

foreach ($foodAndFeedAnalysisTypes as [$analysisTypeCode, $analysisTypeName]) {
    foreach ($microTests as $test) {
        $rows[] = [
            'section_department' => 'Microbiology',
            'sample_type_code' => 'FOOD AND FEED',
            'sample_type_name' => 'Food and Feed',
            'analysis_type_code' => $analysisTypeCode,
            'analysis_type_name' => $analysisTypeName,
            'analyte_code' => $test['analyte_code'],
            'analyte_name' => $test['analyte_name'],
            'method' => $test['method'],
            'reporting_unit' => $test['reporting_unit'],
            'decimal_places' => $test['decimal_places'],
            'accreditation' => 'Yes',
            'instrument' => null,
            'lab_section_code' => 'MICROBIOLOGY',
            'lab_section_name' => 'Microbiology',
        ];
    }
}

$foodChemicalTests = [
  // Raw (5)
    ['RAW', 'Raw', 'Moisture and volatile matter', 'MOISTURE AND VOLATILE MATTER', 'AMS/C/SOP/024', 'g/100g, %', 2, 'Balance/Hot Air Oven'],
    ['RAW', 'Raw', 'Total Ash', 'TOTAL ASH', 'AMS/C/SOP/025', 'g/100g, %', 2, 'Balance/Muffle Furnace'],
    ['RAW', 'Raw', 'Total Fat', 'TOTAL FAT', 'AMS/C/SOP/028', 'g/100g, %', 2, 'Balance/BUCHI Fat Extractor /Hot Air Oven'],
    ['RAW', 'Raw', 'Total Protein', 'TOTAL PROTEIN', 'AMS/C/SOP/029', 'g/100g, %', 2, null],
    ['RAW', 'Raw', 'Crude Fiber', 'CRUDE FIBER', 'AMS/C/SOP/081', 'g/100g, %', 2, null],
  // Cooked (5)
    ['COOKED', 'Cooked', 'Carbohydrates', 'CARBOHYDRATES', 'AMS/C/SOP/026', 'g/100g, %', 2, null],
    ['COOKED', 'Cooked', 'Energy', 'ENERGY', 'AMS/C/SOP/027', 'g/100g, %', 2, null],
    ['COOKED', 'Cooked', 'Moisture and volatile matter', 'MOISTURE AND VOLATILE MATTER', 'AMS/C/SOP/024', 'g/100g, %', 2, 'Balance/Hot Air Oven'],
    ['COOKED', 'Cooked', 'Total Protein', 'TOTAL PROTEIN', 'AMS/C/SOP/029', 'g/100g, %', 2, null],
    ['COOKED', 'Cooked', 'Total Ash', 'TOTAL ASH', 'AMS/C/SOP/025', 'g/100g, %', 2, 'Balance/Muffle Furnace'],
  // Ready to Eat (5)
    ['READY TO EAT', 'Ready to Eat', 'Total Fat', 'TOTAL FAT', 'AMS/C/SOP/028', 'g/100g, %', 2, 'Balance/BUCHI Fat Extractor /Hot Air Oven'],
    ['READY TO EAT', 'Ready to Eat', 'Total Ash', 'TOTAL ASH', 'AMS/C/SOP/025', 'g/100g, %', 2, 'Balance/Muffle Furnace'],
    ['READY TO EAT', 'Ready to Eat', 'Energy', 'ENERGY', 'AMS/C/SOP/027', 'g/100g, %', 2, null],
    ['READY TO EAT', 'Ready to Eat', 'Carbohydrates', 'CARBOHYDRATES', 'AMS/C/SOP/026', 'g/100g, %', 2, null],
    ['READY TO EAT', 'Ready to Eat', 'Moisture and volatile matter', 'MOISTURE AND VOLATILE MATTER', 'AMS/C/SOP/024', 'g/100g, %', 2, 'Balance/Hot Air Oven'],
];

foreach ($foodChemicalTests as [$atCode, $atName, $analyteName, $analyteCode, $method, $unit, $decimals, $instrument]) {
    $rows[] = [
        'section_department' => 'Chemical',
        'sample_type_code' => 'FOOD',
        'sample_type_name' => 'Food',
        'analysis_type_code' => $atCode,
        'analysis_type_name' => $atName,
        'analyte_code' => $analyteCode,
        'analyte_name' => $analyteName,
        'method' => $method,
        'reporting_unit' => $unit,
        'decimal_places' => $decimals,
        'accreditation' => 'Yes',
        'instrument' => $instrument,
        'lab_section_code' => 'CHEMICAL',
        'lab_section_name' => 'Chemical',
    ];
}

return $rows;
