<?php

namespace App\Http\Controllers\CRM\Complaint;

use App\Models\CRM\Complaintnotes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComplaintNotesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add(Request $request,$id){
        $new_note = new Complaintnotes();

        $new_note->notes = $request->notes;
        $new_note->created_by = auth()->user()->name;
        $new_note->complaint_id = $id;
        $new_note->type = $request->type;

        $new_note->save();

        return redirect()->back()->with('sucess','Complaint notes added successfully!');

    }

    public function edit(Request $request,$id){
        $complaint_note = Complaintnotes::find($id);

        $complaint_note->notes = $request->notes;
        $complaint_note->type = $request->type;
        $complaint_note->is_delete = $request->status;

        $complaint_note->save();

        return redirect()->back()->with('sucess','Complain notes edited successfully!');
    }
}
