<?php

namespace App\Http\Controllers;

use App\InvoicePaymentDetail;
use App\Invoice;
use App\SampleHeader;
use Illuminate\Http\Request;

class InvoicePaymentDetailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function add(Request $request){
        $invoice  = Invoice::find($request->invoice_id);
        if(isset($invoice->id)){
            $payment = new InvoicePaymentDetail();
            $payment->payment_method = $request->method;
            $payment->amount = $request->amount;
            $payment->received_by = auth()->user()->id;
            $payment->transaction_no = $request->transaction_number;
            $payment->credit_days = $request->credit_days;
            $payment->ref_no = $request->ref_no;
            $payment->invoice_id = $invoice->id;
            $payment->save();
            $payment_details = InvoicePaymentDetail::where('invoice_id',$invoice->id)->where('is_delete',0)->pluck('amount')->toArray();
            $total_amount = array_sum($payment_details);
            if($invoice->total < $total_amount){
                $batches = SampleHeader::where('invoice_id',$invoice->id)->get();
                foreach($batches as $batch){
                    $batch->customer_paid = 1;
                    $batch->save();
                }
            }
            // return response()->json($total_amount,200);
            return redirect()->back()->with('success','Payment Details added successfully!');
        }else{
            return redirect()->back()->with('error','No payment method with the specified name');
        }
    }
    public function edit(Request $request){
        $payment = InvoicePaymentDetail::find($request->payment_id);
        if(isset($payment->id)){
            
            $payment->payment_method = $request->method;
            $payment->amount = $request->amount;
            $payment->received_by = auth()->user()->id;
            $payment->transaction_no = $request->transaction_number;
            $payment->credit_days = $request->credit_days;
            $payment->ref_no = $request->ref_no;
            $payment->save();
            $invoice = Invoice::find($payment->invoice_id);
            $payment_details = InvoicePaymentDetail::where('invoice_id',$invoice->id)->where('is_delete',0)->pluck('amount');
            $total_amount = array_sum(json_decode( $payment_details,true));
            // return response()->json($total_amount,200);
            if($invoice->total < $total_amount){
                $batches = SampleHeader::where('invoice_id',$invoice->id)->get();
                foreach($batches as $batch){
                    $batch->customer_paid = 1;
                    $batch->save();
                }
            }
            return redirect()->back()->with('success','Payment detail edited successfully!');
        }else{
            return redirect()->back()->with('error','No payment with the specified ID');
        }
    }
    public function delete(Request $request){
        $payment = InvoicePaymentDetail::find($request->payment_id);
        if(isset($payment->id)){
            $payment->is_delete = 1;
            $payment->save();
            return redirect()->back()->with('success','Payment details deleted successfully!');

        }else{
            return redirect()->back()->with('error','No payment detail with the specified ID!');
        }
    }

}
