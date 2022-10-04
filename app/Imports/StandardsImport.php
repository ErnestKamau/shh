<?php

namespace App\Imports;

use App\MyTestUsers;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;


class StandardsImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        
        return new MyTestUsers([
            'firstname'=>$row["firstname"],
            'lastname'=>$row["lastname"],
            'gender'=>$row["gender"],
            'country'=>$row["country"],
            'age'=>$row["age"],
            'date'=> date_create_from_format('d/m/Y',$row['date']),
            'id'=>$row['id'],
        ]);
    }
}
