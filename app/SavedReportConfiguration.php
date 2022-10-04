<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SavedReportConfiguration extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  protected $fillable = [
    'name','configuration','raw_sql'
  ];
  protected $table = 'report_table_configurations';
}
