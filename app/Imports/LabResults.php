<?php

namespace App\Imports;

use App\ImportLabResults;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;

class LabResults implements ToModel,WithHeadingRow
{
    // @param array $row
    // *
    // * @return \Illuminate\Database\Eloquent\Model|null
    // */

   use Importable;
    public function model(array $row)
    {
       
       
    }
}
