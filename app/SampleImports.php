<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SampleImports extends Model
{
    protected $table = 'sample_imports';

    protected $fillable = ['short_code','material_status','variety_name','sample_no','no_of_sample','no_of_pots_plants','standard_tests','compartiment_lot','planting_week','species','results','sample_header_id','sample_condition'];
    

}
