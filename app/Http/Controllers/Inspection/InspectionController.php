<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use App\Models\Inspection\InspectionDetail;
use App\User;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(){
        $certs = InspectionDetail::whereNull('deleted_at')->orderBy('created_at','DESC')->get();
        $users = User::where('active',1)->get();
        $module = "VGM Module";
        return view('layouts.vgm_module.index', compact('users','certs','module'));

    }

    public function store(Request $request){
        return response()->json($request->all());
        $detail = InspectionDetail::find($request->detail_id) ?? new InspectionDetail();
        $detail->approved_vgm_no = $request->approved_vgm_no;
        $detail->authorized_vgm_contact = $request->authorized_vgm_contact;
        $detail->inspector_phone_no = $request->phone_no;
        $detail->inspector_email = $request->email;
        $detail->submission_date = $request->submission_date;
        $detail->shipper_company_name = $request->shipper_company_name;
        $detail->shipper_adress = $request->address;
        $detail->shipper_authorized_contact_name = $request->shipper_authorirized_contact_id;
        $detail->shipper_authorized_contact_date  = $request->date;
        $detail->carrier_booking_number = $request->carrier_book_no;
        $detail->shipper_invoice_no = $request->shipper_invoice_no;
        $detail->shipper_po_number = $request->shipper_po_no;
        $detail->name_booked_vessel = $request->name_booked_vessel;
        $detail->voyage_number = $request->voyage_number;
        $detail->etd_eta = $request->etd;
        $detail->place_of_receipt = $request->place_receipt;
        $detail->port_of_depature = $request->port_depature;
        $detail->port_of_discharge = $request->port_discharge;
        $detail->final_destination = $request->final_destination;
        $detail->container_number = $request->container_number;
        $detail->seal_number = $request->seal_number;
        $detail->size_of_container = $request->size_container;
        $detail->description_goods = $request->description_goods;
        $detail->container_mac_gross = $request->container_max_gross;
        $detail->cargo_weight = $request->cargo_weight;
        $detail->empty_container_weight = $request->empty_container_weight;
        $detail->packaging_material_weight = $request->packaging_material_weight;
        $detail->dunnage_weight = $request->dunnage_weight;
        $detail->total_verified_gross_mass = $request->total_verified_gross_mass;
        $detail->vgm_evaluation_method = $request->evaluation_method;
        $detail->first_surveyor_id = $request->first_surveyor_id;
        $detail->first_surveyor_date = $request->first_date;
        $detail->sec_surveyor_id = $request->sec_surveyor_id;
        $detail->sec_surveyor_date = $request->sec_date;
        if(!isset($detail->id)){
            $cert_count = InspectionDetail::all()->count() + 1;
            $cert_no = date('Y').'1702'.sprintf('%0' . '4' . 'd', $cert_count);
            $randomsum = 501 + $cert_count;
            $randomf = intdiv($randomsum,3);
            $serial_no = date('Y').$randomf.'1702'.sprintf('%0' . '4' . 'd', $cert_count);
            $detail->cert_no = $cert_no;
            $detail->serial_no = $serial_no;
        }
        $detail->save();
        return response()->json($detail);
    }
    public function show($id){
        $detail = InspectionDetail::find($id);
        return response()->json($detail);
    }
    public function delete(Request $request){
        InspectionDetail::where('id',$request->detail_id)->update(['deleted_at'=>date('Y-m-d'),'delete_reason'=>$request->reason,'deleted_by'=>auth()->user()->id]);
        return redirect()->back()->with('success','VGM Certificated deleted successfully!');
    }

}
