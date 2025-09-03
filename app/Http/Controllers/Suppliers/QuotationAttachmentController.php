<?php

namespace App\Http\Controllers\Suppliers;

use App\QuotationAttachment;
use App\QuotationNotes;
use App\SupplierQuote;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;


class QuotationAttachmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id){
        
        $quotations = SupplierQuote::where('request_item_id',$id)->where('supplier_id',auth()->user()->supplier_id)->get();
        $quotation = $quotations[0];
        // return response()->json($quotation->request_id, 200);
        $rfq = getRfqsById($quotations[0]->request_id);
        $rfq_item = getRfqsItemById($quotations[0]->request_item_id);

        $supplier = auth()->user();
        $notes = QuotationNotes::where('quotation_id',$quotation->id)->get();
        $attachments = QuotationAttachment::where('quotation_id',$quotation->id)->get();

        return view('layouts.inventory.suppliers.dashboard.show_quote',compact('quotation','notes','attachments','supplier','rfq','rfq_item'));
    }
    public function addNotes(Request $request){
        $new_notes = new QuotationNotes();
        $new_notes->supplier_id = auth()->user()->supplier_id;
        $new_notes->registered_by = auth()->user()->id;
        $new_notes->request_id = $request->request_id;
        $new_notes->quotation_id = $request->quotation_id;
        $new_notes->request_item_id = $request->request_item_id;
        $new_notes->description = $request->note;

        $new_notes->save();

        return redirect()->back()->with('success','Quotation notes added successfully!');
    }
    public function addAttachment(Request $request){
        $new_attacho = new QuotationAttachment();
        $new_attacho->supplier_id = auth()->user()->supplier_id;
        $new_attacho->registered_by = auth()->user()->id;
        $new_attacho->request_id = $request->request_id;
        $new_attacho->request_item_id = $request->request_item_id;
        $new_attacho->quotation_id = $request->quotation_id;
        $new_attacho->description = $request->note;

        
        if ($request->hasFile('attachment')){
            $path = $request->attachment->path();
            $file = Storage::putFile('quotations',new File($path));
            $file = explode('/',$file);
            $filename = '/storage/quotations/'.urlencode(end($file));

            $new_attacho->attachment = (String) $filename;

            $new_attacho->save();
        }else{
            return redirect()->back()->with('error','Attachment is a requied field!');
        }

        return redirect()->back()->with('success','Attachment added successfully!');
        
    }

    public function delete_notes($id){
        $note = QuotationNotes::find($id);
        $note->delete();
        return redirect()->back()->with('success','Quotation note deleted successfully!');

    }
    public function delete_attachment($id){
        $attachment = QuotationAttachment::find($id);
        $attachment->delete();
        return redirect()->back()->with('success','Attachment deleted successfully!');
    }
}