<?php

namespace App\Http\Controllers\Invoice;

use App\TaxRegime;
use App\SampleHeader;
use App\SampleDetails;
use App\Invoice;
use App\InvoiceDetails;
use App\InvoicePaymentDetail;
use App\Models\CRM\CustomerContact;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;


use App\Http\Controllers\Controller;
use App\Services\Billing\InvoiceNumberGenerator;
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
            $sales = Invoice::with(['crmcustomer','currencyinfo'])->where('created_at','>=',$start)->where('created_at','<=',$end)->get();
        }elseif($request->selection_date == 'due_date'){
            $sales = Invoice::with(['crmcustomer','currencyinfo'])->where('due_date','>=',$start)->where('due_date','<=',$end)->get();
        }else{
            $sales = Invoice::with(['crmcustomer','currencyinfo'])->where('created_at','>=',$start)->where('created_at','<=',$end)->get();
        }
        
        
        // return response()->json($headers);
        return view('layouts.lab.invoice.index',compact('sales','start','end','selection'));
    }
    public function show($id){
        $invoice = Invoice::with(['crmcustomer','currencyinfo','details'])->find($id);
        // $customer = getCrmCustomerByID($header->crm_customer_id);
        // return response()->json($customer,200);
        
        return view('layouts.lab.invoice.show',compact('invoice'));
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
        
        // Get customer's preferred currency (or use default)
        $customerCurrency = $customer->currency_id ?? \App\ModulePreConfigs::where('type', 'Currency')->first()->id;
        
        $invoice = new Invoice();
        $invoice->sample_header_id = $header->id;       
        $invoice->pricelist_id = null; // No longer using pricelists
        $invoice->reference_number = $header->reference_number;
        $invoice->currency_id = $customerCurrency;
        $invoice->customer_id = $header->crm_customer_id;
        
        $invoice->save();
        if($customer->credit_days > 0){
            $date = date('Y-m-d',strtotime($invoice->created_at.'+'.$customer->credit_days .' days'));
        }else{
            $date = date('Y-m-d',strtotime($invoice->created_at.'+ 30 days'));
        }
        // return response()->json($date,200);
        $invoice->due_date = $date;
        $invoice->invoice_number = app(InvoiceNumberGenerator::class)->next();
        $invoice->save();

        
        foreach($details as $detail){
            if(strlen($detail->analysis_type_id)>1){
                $name = array();
                $analysis_ids = explode(',',$detail->analysis_type_id);
                foreach($analysis_ids as $ids){

                    // Get price from invoicable item mapping
                    $priceData = getPriceForAnalysisType((int)$ids, $customerCurrency);
                    
                    if (!$priceData) {
                        continue; // Skip if no invoicable item mapped
                    }

                    $check_invoice_detail = InvoiceDetails::where('sample_header_id',$header->id)->where('analysis_type',(int)$ids)->get();
                    
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
                        $invoice_detail->invoicable_item_id = $priceData['invoicable_item_id'];
                        $invoice_detail->cost_price = $priceData['unit_cost'];
                        $invoice_detail->selling_price = $priceData['unit_price'];
                        
                        // Apply tax if not included in price
                        if(!$priceData['price_includes_tax']){
                            $rate = TaxRegime::where('active',1)->first();
                            if ($rate) {
                                $tax = $rate->value/100 * $priceData['unit_price'];
                                $total_price = $tax + $priceData['unit_price'];
                
                                $invoice_detail->selling_amount = $total_price;
                                $invoice_detail->tax_rate = strval($rate->value);
                                $invoice_detail->tax_amount = $tax;
                                $invoice_detail->total = $total_price;
                            } else {
                                $invoice_detail->selling_amount = $priceData['unit_price'];
                                $invoice_detail->total = $priceData['unit_price'];
                            }
                        }else{
                            $invoice_detail->selling_amount = $priceData['unit_price'];
                            $invoice_detail->total = $priceData['unit_price'];
                        }
                        $invoice_detail->save();
        
                    }else{
                        $current = $check_invoice_detail[0]->quantity;
                        $current_tax = $check_invoice_detail[0]->tax_amount;
                        $unit_price = $check_invoice_detail[0]->selling_amount;
                        $current_total = $check_invoice_detail[0]->total;
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
                
                    // Get price from invoicable item mapping
                    $priceData = getPriceForAnalysisType($detail->analysis_type_id, $customerCurrency);
                    
                    if (!$priceData) {
                        continue; // Skip if no invoicable item mapped
                    }

                    $check_invoice_detail = InvoiceDetails::where('sample_header_id',$header->id)->where('analysis_type',$detail->analysis_type_id)->get();
                    
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
                        $invoice_detail->invoicable_item_id = $priceData['invoicable_item_id'];
                        $invoice_detail->cost_price = $priceData['unit_cost'];
                        $invoice_detail->selling_price = $priceData['unit_price'];
                        
                        // Apply tax if not included in price
                        if(!$priceData['price_includes_tax']){
                            $rate = TaxRegime::where('active',1)->first();
                            if ($rate) {
                                $tax = $rate->value/100 * $priceData['unit_price'];
                                $total_price = $tax + $priceData['unit_price'];
                                
                                $invoice_detail->selling_amount = $total_price;
                                $invoice_detail->tax_rate = strval($rate->value);
                                $invoice_detail->tax_amount = $tax;
                                $invoice_detail->total = $total_price;
                            } else {
                                $invoice_detail->selling_amount = $priceData['unit_price'];
                                $invoice_detail->total = $priceData['unit_price'];
                            }
                        }else{
                            $invoice_detail->selling_amount = $priceData['unit_price'];
                            $invoice_detail->total = $priceData['unit_price'];
                        }
                        $invoice_detail->save();
        
                    }else{
                        $current = $check_invoice_detail[0]->quantity;
                        $unit_price = $check_invoice_detail[0]->selling_amount;
                        $current_total = $check_invoice_detail[0]->total;
                        $current_tax = $check_invoice_detail[0]->tax_amount;
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
        $invoice = Invoice::query()
            ->with(['currencyinfo', 'pricelist.currency'])
            ->find($id);

        if(!$invoice){
            return redirect()->back()->with('error','No Invoice with the specified ID');
        }

        if ($invoice->details()->exists() && (float) ($invoice->total ?? 0) <= 0) {
            $invoice->syncTotalsFromDetails();
            $invoice->refresh();
        }

        $header = SampleHeader::where('invoice_id',$invoice->id)->first();
        $customer = getCrmCustomerByID($header->crm_customer_id ?? $invoice->customer_id);
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
