<?php

namespace App\Http\Controllers\CRM\Complaint;

use App\Models\CRM\Complaintattachment;

use App\Http\Controllers\Controller;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplaintAttachmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add(Request $request,$id){
        $new_attachment = new Complaintattachment();

        $new_attachment->title = $request->title;
        $new_attachment->type = $request->type;
        $new_attachment->description = $request->description;
        $new_attachment->complaint_id = $id;
        $new_attachment->posted_by = auth()->user()->name;
        if ($request->hasFile('certificate')){
            $path = $request->certificate->path();
            $file = Storage::putFile('complaints',new File($path));
            $file = explode('/',$file);

            $fname = '/storage/complaints/'.urlencode(end($file));

            $new_attachment->file_path = $fname;
        }
        $new_attachment->save();

        return redirect()->back()->with('sucess','Attachment added successfully');
    }
    public function edit(Request $request,$id){
        $attachment = Complaintattachment::find($id);
        $attachment->title = $request->title;
        $attachment->type = $request->type;
        $attachment->description = $request->description;
        $attachment->is_delete = $request->status;
        if ($request->hasFile('certificate')){
            $path = $request->certificate->path();
            $file = Storage::putFile('complaints',new File($path));
            $file = explode('/',$file);

            $fname = '/storage/complaints/'.urlencode(end($file));

            $attachment->file_path = $fname;
        }
        $attachment->save();
    }
}
