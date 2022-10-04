<?php

namespace App\Http\Controllers\System;

use App\BatchNotification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemNotifications extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function batchNotification($batch,$position,$message,$status){
        
        
        $notifications = BatchNotification::where('batch_id',$batch->id)->get();
        foreach($notifications as $note){
            if($note->status != $status){
                $note->active = 0;
                $note->save();

            }
        }
        $notify = new BatchNotification();
        $notify->position_id = $position;
        $notify->created_by = auth()->user()->id;
        $notify->notification = $message;
        $notify->batch_id = $batch->id;
        $notify->status = $status;
        $notify->save();

        
        return true;
    }
    // public function view_notification()
}
