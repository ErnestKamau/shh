<?php

namespace App\Http\Controllers\Personel;

use App\User;
use App\Models\Personnel\PersonelCertification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class PersonnelCertificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add(Request $request,$id){
        $personnel = User::find($id);
        if(isset($personnel->name) && $personnel->id == $request->personnel_id){
            $certification = new PersonelCertification();
            $certification->personnel_id = $personnel->id;
            
            $certification->role_certification_id = $request->certificate_name;
            $certification->certificate_body = $request->certificate_body;
            $certification->certificate_date = $request->certificate_date;
            
            $certification->expire_date = $request->expire_date;
            if($request->hasFile('certificate')){
                $path = $request->certificate->path();
                $file = Storage::putFile('certificate',new File($path));
                $file = explode('/',$file);
                $fname = '/storage/certificate/'.urlencode(end($file));
                $certification->certificate = (String) $fname;

                $certification->save();

                return redirect()->back()->with('success','Personnel certification added successfully!');
            }else{
                return redirect()->back()->with('error','Certificate imagve is required!');
            }
        }else{
            return redirect()->back()->with('error','No personel with specified ID!');
        }
    }

    public function edit(Request $request,$id){
        $certification = PersonelCertification::find($id);
        if(isset($certification->personnel_id) && $certification->id == $request->certification_id){
            $certification->role_certification_id = $request->certificate_name;
            $certification->certificate_body = $request->certificate_body;
            $certification->certificate_date = $request->certificate_date;
            $certification->expire_date = $request->expire_date;

            if($request->hasFile('certificate')){
                $path = $request->certificate->path();
                $file = Storage::putFile('certificate',new File($path));
                $file = explode('/',$file);
                $fname = '/storage/certificate/'.urlencode(end($file));

                $certification->certificate = (String) $fname;
            }
            $certification->edited_by = auth()->user()->name;
            $certification->save();
            return redirect()->back()->with('success','Personnel certificate edited successfully!');
        }else{
            return redirect()->back()->with('eror','No personnel certification with specified ID!');
        }
    }

    public function delete(Request $request,$id){
        $certification = PersonelCertification::find($id);
        if(isset($certification->id) && $certification->id == $request->cert_id ){
            $certification->status = 1;
            $certification->edited_by = auth()->user()->name;

            $certification->save();

            return redirect()->back()->with('success','Personnel certfication archived successfully!');
        }else{
            return redirect()->back()->with('error','No certification with specified ID!');
        }
    }



}
