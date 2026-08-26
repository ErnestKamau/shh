<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
    return view('layouts.configuration.company.index');
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function add(Request $request)
  {
    $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'code' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/', 'unique:companies,code'],
    ]);

    $company = new Company;
    $company->name = $request->name;
    $company->code = $request->code;
    $company->location = $request->location;
    $company->address = $request->address;
    $company->country_id = $request->country_id;
    $company->website = $request->website;
    $company->email = $request->email;
    $company->cell_phone = $request->cell_phone;
    $company->telephone = $request->telephone;
    $company->street = $request->street;
    $company->fax = $request->fax;

    if ($request->hasFile('logo')){
      $path = $request->logo->path();
      $file = Storage::putFile('companies', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/companies/'.urlencode(end($file));

      $company->logo = (String) $fName;
    }
    if ($request->hasFile('report_logo')){
      $path = $request->report_logo->path();
      $file = Storage::putFile('companies', new File($path));
      $file = explode('/', $file);

      $frName = '/storage/companies/'.urlencode(end($file));

      $company->report_logo = (String) $frName;
    }

    $company->save();

    return redirect()->back()->with('success', 'Company added.');
  }

  public function edit(Request $request, $id)
  {
    $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'code' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/', 'unique:companies,code,'.$id.',id'],
    ]);

    $company = Company::find($id);
    $company->name = $request->name;
    $company->code = $request->code;
    $company->location = $request->location;
    $company->address = $request->address;
    $company->country_id = $request->country_id;
    $company->website = $request->website;
    $company->email = $request->email;
    $company->cell_phone = $request->cell_phone;
    $company->telephone = $request->telephone;
    $company->street = $request->street;
    $company->fax = $request->fax;

    if ($request->hasFile('logo')){
      $path = $request->logo->path();
      $file = Storage::putFile('companies', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/companies/'.urlencode(end($file));

      $company->logo = (String) $fName;
    }
    if ($request->hasFile('report_logo')){
      $path = $request->report_logo->path();
      $file = Storage::putFile('companies', new File($path));
      $file = explode('/', $file);

      $frName = '/storage/companies/'.urlencode(end($file));

      $company->report_logo = (String) $frName;
    }

    $company->save();

    return redirect()->back()->with('success', 'Company edited.');
  }

  public function activate_company(Request $request){
    $companies = Company::all();
    foreach($companies as $cmpny){
      $cmpny->active = 0;
      $cmpny->save();
    }
    $company = Company::find($request->company_id);
    if(isset($request->show_reports)){
      $company->show_on_reports = 1;
    }else{
      $company->show_on_reports = 0;
    }
    if(isset($request->set_default)){
      $company->active = 1;
    }else{
      $company->active = 0;
    }
    $company->save();

    return redirect()->back()->with('success','Company activated successfully!');
  }

  

}
