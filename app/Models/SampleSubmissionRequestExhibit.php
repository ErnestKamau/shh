<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleSubmissionRequestExhibit extends Model
{
    protected $fillable = [
        'sample_submission_request_id',
        'sample_detail_id',
        'serial_number',
        'number_of_items',
        'item_description',
        'suspected_item',
    ];

    public function request()
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }
}