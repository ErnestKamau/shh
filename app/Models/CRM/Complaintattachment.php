<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Complaintattachment extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    
    protected $connection = 'developer_code';
    
    protected $fillable = [
        'complaint_id',
        'title',
        'upload_title',
        'type',
        'file_type',
        'file_path',
        'file_size',
        'description',
        'posted_by',
        'is_delete',
    ];
}
