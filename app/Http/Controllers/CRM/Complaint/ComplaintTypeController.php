<?php

namespace App\Http\Controllers\CRM\Complaint;

use App\Models\CRM\Complaint_Type;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComplaintTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('layouts.crm.complaint-type-list');
    }

    public function add(Request $request){
        $complaint_type = new Complaint_Type();

        $complaint_type->name = $request->complaint_name;
        $complaint_type->description = $request->description;
        $complaint_type->status = $request->status;

        $complaint_type->save();
        return redirect()->back()->with('sucess','Complaints type added successful!');
    }

    public function edit(Request $request,$id){
        $complaint_type = Complaint_Type::find($id);
        $complaint_type->name = $request->complaint_name;
        $complaint_type->description = $request->description;
        $complaint_type->status = $request->status;

        $complaint_type->save();

        return redirect()->back()->with('sucess','Complaints type edited successfully!');
    }
}
