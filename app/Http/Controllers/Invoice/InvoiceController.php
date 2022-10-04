<?php

namespace App\Http\Controllers\Invoice;

use App\TaxRegime;
use App\SampleHeader;
use App\SampleDetails;
use App\Invoice;
use App\InvoiceDetails;
use App\PricelistCustomer;
use App\Pricelist;
use App\InvoicePaymentDetail;
use App\Models\CRM\CustomerContact;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;


use App\Http\Controllers\Controller;
use App\PricelistItem;
use Illuminate\Http\Request;
use SebastianBergmann\CodeCoverage\Report\Xml\Totals;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request){
        $start = isset($request->start_date) ? \Carbon\Carbon::parse($request->start_date)->format('Y-m-d') : \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
        $end = isset($request->end_date) ? \Carbon\Carbon::parse($request->end_date)->format('Y-m-d') : \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');
        $selection = $request->selection_date ?? 'receipt_date';
        if($request->selection_date == 'invoice_date'){
            $headers = SampleHeader::where('invoice_id','!=',0)->orderBy('receipt_date','desc')->join('customer_invoice as ci','ci.id','=','sample_headers.invoice_id')->where('ci.created_at','>=',$start)->where('ci.created_at','<=',$end)->selectRaw('sample_headers.*')->get();
        }else{
            $headers = SampleHeader::where('invoice_id','!=',0)->where('receipt_date','>=',$start)->where('receipt_date','<=',$end)->orderBy('receipt_date','desc')->join('customer_invoice as ci','ci.id','=','sample_headers.invoice_id')->selectRaw('sample_headers.*')->get();
        }
        
        foreach($headers as $header){
            $invoice = Invoice::find($header->invoice_id);
            if(isset($invoice->id)){

                $payments= InvoicePaymentDetail::where('invoice_id',$invoice->id)->get();
                $methods = array();
                $ref = array();
                $trans = array();
                // $amount = array();
                foreach($payments as $p){
                    array_push($methods, $p->payment_method);
                    array_push($ref,$p->ref_no);
                    array_push($trans,$p->transaction_no);
                    // array_push($ammount,intval($p->amount));
                }
                $m = array_unique($methods);
                $header->invoice_number = $invoice->invoice_number;
                $header->payment_method = implode(' , ',$m);
                $header->transaction = implode(' , ',$trans);
                // $header->ammount = array_sum($ammount);
                $header->p_ref_no = implode(' , ',$ref);
            }
            

        }
        // return response()->json($headers);
        return view('layouts.lab.invoice.index',compact('headers','start','end','selection'));
    }
    public function show($id){
        $details = SampleDetails::where('sample_header_id',$id)->get();
        $header = SampleHeader::find($id);
        $invoice = Invoice::find($header->invoice_id);
        $payments= InvoicePaymentDetail::where('invoice_id',$invoice->id)->get();
        $methods = array();
        $ref = array();
        $trans = array();
        foreach($payments as $p){
            array_push($methods, $p->payment_method);
            array_push($ref,$p->ref_no);
            array_push($trans,$p->transaction_no);
        }
        $invoice->payment_method = implode(',',$methods);
        $invoice->transaction_no = implode(',',$trans);
        $invoice->ref_no = implode(',',$ref);
        $customer = getCrmCustomerByID($header->crm_customer_id);
        // return response()->json($customer,200);
        
        return view('layouts.lab.invoice.show',compact('details','header','customer','invoice','payments'));
    }
    public function edit_invoice(Request $request){
        $loop = 0;
        $invoice = Invoice::find($request->invoice_id);
        

        foreach($request->detail_id as $id){
            $detail = InvoiceDetails::find($id);
            if($detail->selling_price != $request->selling_price[$loop]){
                $tax_unit = (intval($detail->tax_rate) *  $request->selling_price[$loop])/100;
                $selling_amount = intval($request->selling_price[$loop]) + $tax_unit;
                $total = $selling_amount * $detail->quantity;
                $detail->tax_amount = $tax_unit * $detail->quantity;
                $detail->selling_price = $request->selling_price[$loop];
                $detail->selling_amount = $selling_amount;
                $detail->total = $total;
            }
            $detail->cost_price = $request->cost_price[$loop];
            $detail->save();
            $loop = $loop + 1;
        }
        $taxs = [];
        $totals = [];
        $details = InvoiceDetails::where('invoice_id',$invoice->id)->get();
        // return response()->json($details);
        foreach($details as $det){
            array_push($taxs,$det->tax_amount);
            array_push($totals,$det->total);
        }
        $total_ = array_sum($totals);
        $tax_ = array_sum($taxs);
        $invoice->total = $total_;
        $invoice->total_tax = $tax_;
        $invoice->save();
        return redirect()->back()->with('success','Invoice updated successfully!');
    }
    public function generateinvoice($id){
        $header = SampleHeader::find($id);
        $customer = getCrmCustomerByID($header->crm_customer_id);
        $details = SampleDetails::where('sample_header_id',$id)->get();
        $customer_pricelist = PricelistCustomer::where('customer_id',$header->crm_customer_id)->get();
        $pricelist = Pricelist::find($customer_pricelist[0]->pricelist_id);     
        $invoice = new Invoice();
        $invoice->sample_header_id = $header->id;       
        $invoice->pricelist_id = $customer_pricelist[0]->pricelist_id;
        $invoice->reference_number = $header->reference_number;
        $invoice->currency_id = $pricelist->currency_id;
        $invoice->customer_id = $header->crm_customer_id;
        
        $invoice->save();
        if($customer->credit_days > 0){
            $date = date('Y-m-d',strtotime($invoice->created_at.'+'.$customer->credit_days .' days'));
        }else{
            $date = date('Y-m-d',strtotime($invoice->created_at.'+ 30 days'));
        }
        // return response()->json($date,200);
        $invoice->due_date = $date;
        $invoice->save();
        $id_str = strval($invoice->id);
        if(strlen($id_str)<4){
            $count = 4-strlen($id_str);
            $zeros = str_repeat('0',$count);
            $number = 'INV-'.$zeros.$id_str;
        }else{
            $number = 'INV-'.$id_str;
        }
        $invoice->invoice_number = $number;
        $invoice->save();

        
        foreach($details as $detail){
            if(strlen($detail->analysis_type_id)>1){
                $name = array();
                $analysis_ids = explode(',',$detail->analysis_type_id);
                foreach($analysis_ids as $ids){

                    $price = PricelistItem::where('pricelist_id',$pricelist->id)->where('analysis_id',(int)$ids)->where('sample_type_id',$header->sample_type_id)->get();
                    $check_invoice_detail = InvoiceDetails::where('sample_header_id',$header->id)->where('analysis_type',(int)$ids)->get();
                    // return response()->json($price,200);
                    if(!isset($check_invoice_detail[0]->id)){
                        $invoice_detail = new InvoiceDetails();
                        $invoice_detail->crm_customer_id = $header->crm_customer_id;
                        $invoice_detail->analysis_type = $detail->analysis_type_id;
                        $analysis_types_ids = explode(',',$detail->analysis_type_id);
                        $analysis_result = array();
                        if(sizeof($analysis_types_ids)>1){
                            foreach($analysis_types_ids as $type_id){
                                $analysis_name = getAnalysisTypeID($type_id);
                                array_push($analysis_result,$analysis_name->name);
                            }
                        }else{
                            $analysis_name = getAnalysisTypeID($analysis_types_ids[0]);
                            array_push($analysis_result,$analysis_name->name);
                        }
                        $invoice_detail->analysis_type_name = implode(',',$analysis_result);
                        $invoice_detail->sample_header_id = $header->id;
                        $invoice_detail->sample_detail_id = $detail->id;
                        $invoice_detail->invoice_id = $invoice->id;
                        $invoice_detail->cost_price = $price[0]->cost_price;
                        $invoice_detail->selling_price = $price[0]->selling_price;
                        if($price[0]->vat == 1){
                            $rate = TaxRegime::where('active',1)->get();
                            $tax = $rate[0]->value/100 * $price[0]->selling_price;
                            $total_price = $tax + $price[0]->selling_price;
            
                            $invoice_detail->selling_amount = $total_price;
                            $invoice_detail->tax_rate = strval($rate[0]->value);
                            $invoice_detail->tax_amount = $tax;
                            $invoice_detail->total = $total_price;
                        }else{
                            $invoice_detail->selling_amount = $price[0]->selling_price;
                            $invoice_detail->total = $price[0]->selling_price;
                        }
                        $invoice_detail->save();
        
                    }else{
                        $current = $check_invoice_detail[0]->quantity;
                        $current_tax = $check_invoice_detail[0]->tax_amount;
                        $unit_price = $check_invoice_detail[0]->selling_amount;
                        $current_total = $check_invoice_detail[0]->total;
                        // return response()->json($current_total,200);
                        $check_invoice_detail[0]->total = $unit_price + $current_total;
                        $check_invoice_detail[0]->quantity = $current + 1; 
                        if($check_invoice_detail[0]->tax_rate != '0'){
                            $exist_tax = strval($check_invoice_detail[0]->tax_rate/100 * $check_invoice_detail[0]->selling_price);
                            $check_invoice_detail[0]->tax_amount = $exist_tax + $current_tax;
                        }
                        $check_invoice_detail[0]->save();
                    }
                }
            }else{
                

                    $price = PricelistItem::where('pricelist_id',$pricelist->id)->where('analysis_id',$detail->analysis_type_id)->where('sample_type_id',$header->sample_type_id)->get();
                    $check_invoice_detail = InvoiceDetails::where('sample_header_id',$header->id)->where('analysis_type',$detail->analysis_type_id)->get();
                    // return response()->json($header->id,200);
                    if(!isset($check_invoice_detail[0]->id)){
                        $invoice_detail = new InvoiceDetails();
                        $invoice_detail->crm_customer_id = $header->crm_customer_id;
                        $invoice_detail->analysis_type = $detail->analysis_type_id;
                        $analysis_types_ids = explode(',',$detail->analysis_type_id);
                        $analysis_result = array();
                        if(sizeof($analysis_types_ids)>1){
                            foreach($analysis_types_ids as $type_id){
                                $analysis_name = getAnalysisTypeID($type_id);
                                array_push($analysis_result,$analysis_name->name);
                            }
                        }else{
                            $analysis_name = getAnalysisTypeID($analysis_types_ids[0]);
                            array_push($analysis_result,$analysis_name->name);
                        }
                        $invoice_detail->analysis_type_name = implode(',',$analysis_result);
                        $invoice_detail->sample_header_id = $header->id;
                        $invoice_detail->sample_detail_id = $detail->id;
                        $invoice_detail->invoice_id = $invoice->id;
                        $invoice_detail->cost_price = $price[0]->cost_price;
                        $invoice_detail->selling_price = $price[0]->selling_price;
                        if($price[0]->vat == 1){
                            $rate = TaxRegime::where('active',1)->get();
                            // return response()->json($rate,200);
                            $tax = $rate[0]->value/100 * $price[0]->selling_price;
                            $total_price = $tax + $price[0]->selling_price;
                            
                            $invoice_detail->selling_amount = $total_price;
                            $invoice_detail->tax_rate = strval($rate[0]->value);
                            $invoice_detail->tax_amount = $tax;
                            $invoice_detail->total = $total_price;
                        }else{
                            $invoice_detail->selling_amount = $price[0]->selling_price;
                            $invoice_detail->total = $price[0]->selling_price;
                        }
                        $invoice_detail->save();
        
                    }else{
                        $current = $check_invoice_detail[0]->quantity;
                        $unit_price = $check_invoice_detail[0]->selling_amount;
                        $current_total = $check_invoice_detail[0]->total;
                        $current_tax = $check_invoice_detail[0]->tax_amount;
                        // return response()->json($current_tax,200);
                        $check_invoice_detail[0]->total = $unit_price + $current_total;
                        if($check_invoice_detail[0]->tax_rate != '0'){
                            $exist_tax = strval($check_invoice_detail[0]->tax_rate/100 * $check_invoice_detail[0]->selling_price);
                            $check_invoice_detail[0]->tax_amount = $exist_tax + $current_tax;
                        }
                        $check_invoice_detail[0]->quantity = $current + 1;        
                        $check_invoice_detail[0]->save();
                    }
            }
        }
        $details_invoice = InvoiceDetails::where('invoice_id',$invoice->id)->get();
        $details_invoice_total_including_tax = InvoiceDetails::where('invoice_id',$invoice->id)->pluck('total')->toarray();
        $details_invoice_total_tax = InvoiceDetails::where('invoice_id',$invoice->id)->pluck('tax_amount')->toarray();
        foreach($details_invoice as $detail){
            $selling_amount = $detail->selling_price * $detail->quantity;
            $detail->selling_price_amount = $selling_amount;
            $detail->save();
        }
        $total_including_tax = array_sum($details_invoice_total_including_tax);
        $total_invoice_tax = array_sum($details_invoice_total_tax);
 
        $invoice->total = $total_including_tax;
        $invoice->total_tax = $total_invoice_tax;
        $invoice->save();

        return redirect()->route('invoice-sample-header',['id'=>$id])->with('success','Invoice generated successfuly!');  
    }

    public function print_invoice(Request $request,$id){
        $invoice = Invoice::find($id);
        
        if(!isset($invoice->id)){
            return redirect()->back()->with('error','No Invoice with the specified ID');
        }
        if($invoice->id != $request->invoice_id){
            return redirect()->back()->with('error','There is no Invoice with the specified route ID');
        }
        $header = SampleHeader::where('invoice_id',$invoice->id)->first();
        $customer = getCrmCustomerByID($header->crm_customer_id);
        // return response()->json($invoice->id,200);
        return view('layouts.lab.invoice.print',compact('invoice','customer'));
    }

    public function upload_invoice(Request $request){

       $path = $request->invoice->path();
       $file = Storage::putFile('customer_invoice',new File($path));
       $file = explode('/',$file);
       $fname = '/app/customer_invoice/'.urlencode(end($file));

       $invoice = Invoice::find($request->invoice_id);
       $invoice->upload_url = (String)$fname;
       $invoice->save();
       return redirect()->back()->with('success','Invoice uploaded successfully!');
    }

    public function email_invoice(Request $request){
        $companyDetails = getCompanyDetails();
        $invoice = Invoice::find($request->invoice_id);
        $header = SampleHeader::find($request->header_id);
        $customer = getCrmCustomerByID($invoice->customer_id);
        $active = getActiveCompany();
        $contacts = getCrmCustomerContacts($customer->id);
        // return response()->json($contacts,200);
        $message = 'Invoice for batch '.$header->batch_code.' has been processed. Please find the invoice attached below.';
        $body = 'Hi '.$customer->name.',<br><br>'
                .$message.'<br><br>
                Regards, <br>
                '.$active->name.' ';
        
        $subject = '['.$active->name.'] Invoice '.$invoice->invoice_number ;
        $file = \storage_path().$invoice->upload_url;
        foreach($contacts as $c){
            $notify  = notify_user($body,$c->email,$subject,$file);
        }

        return redirect()->back()->with('success','Invoice email notification sent successfully to the customer.');
    }

    public function add_tax(Request $request){
        $new_tax = new TaxRegime();
        $new_tax->registered_by = auth()->user()->id;
        $new_tax->value = $request->value;
        if(isset($request->active)){
            $current_tax = TaxRegime::where('active',1)->get();
            if(isset($current_tax->id)){
                $current_tax->active = 0;
                $current_tax->end_date = date("Y-m-d");
                $current_tax->save();
                
            }
            $new_tax->active = 1;
        }
        $new_tax->save();

        return redirect()->back()->with('success','Tax Value added successfully!');
    }

    public function edit_tax(Request $request,$id){
        $update_tax = TaxRegime::find($id);
        $current_tax = TaxRegime::where('active',1)->get();
        $update_tax->registered_by = auth()->user()->id;
        $update_tax->value = $request->value;
        if(isset($current_tax->id)){
            if($update_tax->id != $current_tax->id && isset($request->active)){
                $current_tax->active = 0;
                $current_tax->save();
                $update_tax->active = 1;
            }
        }
        if(isset($request->active)){
            $update_tax->active = 1;
        }else{
            $update_tax->active = 0;
        }

        $update_tax->save();

        return redirect()->back()->with('success','Tax value edited successfully!');
    }

    public function tax_index(){
        $taxes = TaxRegime::all();
        return view('layouts.lab.tax-regime.index',compact('taxes'));
    }
    public function add_tax_invoice(Request $request){
        $invoice = getInvoiceById($request->invoice_id);
        $invoice->tax_invoice = $request->tax_invoice;
        $invoice->save();
        return redirect()->back()->with('success','Tax Invoice Number updated successfully!');
    }   
}
