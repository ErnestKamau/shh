<?php

namespace App\Http\Controllers;

use Auth;
use App\Lab;
use App\Company;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LabController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    // Use new Livewire component
    return view('layouts.lab.livewire-index');
    
    // Old implementation kept for reference (can be removed later)
    // $companies = Company::all();
    // $labs = Lab::join('companies as c', 'c.id', '=', 'labs.company_id')->selectRaw('labs.*, c.name as company')->get();
    // return view('layouts.lab.index', compact('companies', 'labs'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function add(Request $request)
  {
    $lab = new Lab;
    $lab->code = $request->code;
    $lab->name = $request->name;
    $lab->location = $request->location;
    $lab->address = $request->address;
    $lab->company_id = getUserCompany();
    $lab->website = $request->website;
    $lab->email = $request->email;
    $lab->fax = $request->fax;
    $lab->phone1 = $request->phone1;
    $lab->phone2 = $request->phone2;
    $lab->phone3 = $request->phone3;
    $lab->is_external = $request->is_external ?? 0;
    $lab->active = $request->active ?? 0;
    $lab->is_external = $request->is_external ?? 0;
    $lab->start_sample_no = $request->start_sample_no;

    $lab->save();

    return redirect()->back()->with('success', 'Lab added.');
  }

  public function edit(Request $request, $id)
  {
    $lab = Lab::find($id);
    $lab->code = $request->code;
    $lab->name = $request->name;
    $lab->location = $request->location;
    $lab->address = $request->address;
    $lab->company_id = getUserCompany();
    $lab->website = $request->website;
    $lab->email = $request->email;
    $lab->fax = $request->fax;
    $lab->phone1 = $request->phone1;
    $lab->phone2 = $request->phone2;
    $lab->phone3 = $request->phone3;
    $lab->is_external = $request->is_external ?? 0;
    $lab->active = $request->active ?? 0;
    $lab->is_external = $request->is_external ?? 0;
    $lab->start_sample_no = $request->start_sample_no;
    $lab->save();

    return redirect()->back()->with('success', 'Lab edited.');
  }
}
