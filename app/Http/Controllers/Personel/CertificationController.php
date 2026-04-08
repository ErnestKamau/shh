<?php

namespace App\Http\Controllers\Personel;

use App\Models\Personnel\RoleCertification;
use App\Role;

use App\Models\Lab\Qualification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(){
        return view('livewire.layout.personnel-app', [
            'componentType' => 'personnel-certifications',
            'pageTitle' => 'Personnel Certifications',
        ]);
    }

    public function add_role_certification(Request $request,$id){
        $role = Role::find($id);
        if(isset($role->name)){
            $new_certification = new RoleCertification();
            $new_certification->certification_id = $request->certificate;
            $new_certification->role_id = $role->id;
            if(isset($request->mandatory)){
                $new_certification->is_mandatory = 1;
            }
            $new_certification->save();
            return redirect()->back()->with('success','Role certification added successfully!');
        }else{
            return redirect()->back()->with('error','No role with the specified ID');
        }
    }

    public function edit_role_certification(Request $request,$id){
        $certification = RoleCertification::find($id);
        if(isset($certification->role_id)){
            $certification->certification_id = $request->certificate;
            $certification->status = $request->status;
            $certification->edited_by = auth()->user()->name;
            if(isset($request->mandatory)){
                $certification->is_mandatory = 1;
            }else{
                $certification->is_mandatory = 0;
            }
            $certification->save();
            return redirect()->back()->with('success','Certification edited successfully!');
        }else{
            return redirect()->back()->with('error','No certification with specified  ID!');
        }
    }
    
    public function delete_role_certiification(Request $request,$id){
        $certification = RoleCertification::find($id);
        if(isset($certification->role_id) && $certification->id = $request->cert_id){
            $certification->status = 1;
            $certification->edited_by = auth()->user()->name;
            $certification->save();
            return redirect()->back()->with('success','Role certification edited successfully!');

        }else{
            return redirect()->back()->with('error','No role certification with specified ID!');
        }
    }

}
