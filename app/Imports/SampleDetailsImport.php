<?php

namespace App\Imports;

use App\SampleImports;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SampleDetailsImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function __construct($batch_id)
    {
        $this->batch_id = $batch_id;
    }
    public function model(array $row)
    {
        // dd($this->batch_id);
        return new SampleImports([
            'short_code'=>$row['short_code'],
            'species'=>$row['species'],
            'material_status'=>$row['material_status'],
            'variety_name'=>$row['variety_name'],
            'sample_no'=>$row['sample_no'],
            'no_of_sample'=>$row['no_of_samples'],
            'no_of_pots_plants'=>$row['no_of_pots_plants'],
            'standard_tests'=>$row['standard_tests'],
            'compartiment_lot'=>$row['compartiment_lot'],
            'planting_week'=>$row['planting_week'],
            'sample_header_id'=> (string) $this->batch_id,
            'results'=>$row['results'],
            'sample_condition'=>$row['sample_condition'],  
        ]);
       
    }
}
