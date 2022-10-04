<?php

namespace App\Http\Controllers\CRM;

use App\Models\CRM\CustomerCertification;
use App\Models\CRM\CRMCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;


class CustomerCertificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add(Request $request,$id){
        $new_certification = new CustomerCertification();
        $customer = CRMCustomer::find($id);
        if(isset($customer->name)){
            $new_certification->name = $request->name;
            $new_certification->customer_id = $customer->id;
            $new_certification->certification_date = $request->certification_date;
            $new_certification->expire_date = $request->expired_date;
            $new_certification->certification_body = $request->certification_body;
            if($request->hasFile('certificate')){
                $path = $request->certificate->path();
                $file = Storage::putFile('certificate', new File($path));
                $file = explode('/',$file);
                $fname = '/storage/certificate/'.urlencode(end($file));

                $new_certification->certificate = (String) $fname;
                $new_certification->save();
                return redirect()->back()->with('success','Attachment added successfully!');
            }else{
                return redirect()->back()->with('error','File is required!');
            }

        }else{
            return redirect()->back()->with('error','No customer with the specified ID!');
        }
    }

    public function edit(Request $request,$id){
        $certification = CustomerCertification::find($id);
        if($certification->id == $request->cert_id){
            $certification->name = $request->name;
            $certification->certification_date = $request->certification_date;
            $certification->expire_date = $request->expire_date;
            $certification->certification_body = $request->certification_body;
            $certification->edited = auth()->user()->name;
            $certification->status = $request->status;
            if($request->hasFile('certificate')){
                $path = $request->certificate->path();
                $file = Storage::putFile('certificate',new File($path));
                $file = explode('/',$file);
                $fname = '/storage/certificate/'.urlencode(end($file));

                $certification->certificate = (String) $fname;
            }
            $certification->save();
            return redirect()->back()->with('success','Edited customer attachment successfully!');
        }else{
            return redirect()->back()->with('error','No file with the specified ID!');
        }
    }

    public function delete(Request $request,$id){
        $certification = CustomerCertification::find($id);
        if($certification->id == $request->qualification){
            $certification->edited = auth()->user()->name;
            $certification->status = 1;
            $certification->save();
            return redirect()->back()->with('success','Attachment deleted successfully');
        }else{
            return redirect()->back()->with('error','No Attachment with the specified ID!');
        }
    }


}
