<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestRequestReportDelivery extends Model
{
    protected $fillable = [
        'batch_id',
        'revision_no',
        'channel',
        'recipient_name',
        'recipient_contact',
        'status',
        'error',
        'sent_by',
    ];
}
