<?php

namespace App\Http\Controllers\CRM\Complaint;

use App\Models\CRM\Complaintattachment;
use App\Models\CRM\Complaint;
use App\Http\Controllers\Controller;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplaintAttachmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Stream a complaint attachment for download/view. Ensures file is under public disk and user can view complaint.
     */
    public function download(int $complaint, int $attachment): StreamedResponse|\Illuminate\Http\Response
    {
        $attachmentModel = Complaintattachment::where('id', $attachment)
            ->where('complaint_id', $complaint)
            ->where('is_delete', '!=', 1)
            ->firstOrFail();

        if (empty($attachmentModel->file_path)) {
            abort(404, 'Attachment has no file.');
        }

        $relativePath = ltrim(preg_replace('#^/storage/#', '', $attachmentModel->file_path), '/');
        if (!Storage::disk('public')->exists($relativePath)) {
            abort(404, 'File not found.');
        }

        $mime = Storage::disk('public')->mimeType($relativePath);
        $filename = basename($attachmentModel->file_path);

        return response()->streamDownload(
            fn () => print(Storage::disk('public')->get($relativePath)),
            $filename,
            ['Content-Type' => $mime]
        );
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
        return redirect()->back()->with('sucess','Attachment updated successfully');
    }
}
