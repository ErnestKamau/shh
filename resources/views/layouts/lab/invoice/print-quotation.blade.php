<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Quotation | {{$header->quote_number}}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg==" crossorigin="anonymous"></script>
</head>
<style>
    @page {
        margin: 15px;
    }

    .header {
        position: running(header);
        /* top: 0px;
        left: 0;
        right: 0;
        height: 50;
        z-index: 1000; */


    }

    /* .header::before{
        position: running(header);
    } */


    .border_bottom {
        border-top: solid 2px grey !important;
    }

    .parameter {
        border: solid 1 rgba(0, 0, 0, 0.35) !important;
        padding: 0 !important;

    }
</style>

<body>

    <main>
        <table style="width: 100%;border-bottom:solid 2px #0000ff !important;padding-bottom:2px !important">
            <tr>
                <td style="border:solid 0 transparent !important;font-size: 10px !important" colspan="{{$header->quotation_type == 'General' ? 3 : 4 }}">
                    <img src="{{ $path }}" style="height:60px;" alt="logo"><br><br>
                    ISO 17025:2005 COMPANY
                </td>
                <td style="font-size: 10px;border:solid 0 transparent !important; " colspan="4">
                    <p style="text-align: right !important;">
                        {{$company->name}} <br>
                        {{$company->address ?? '-'}} <br>
                        {{$company->location ?? '-'}} <br>
                        {{$company->street ?? '-'}} <br>
                        Email: {{$company->email ?? '-'}} <br>
                        Website: {{$company->website ?? '-'}} <br>
                        Tel: {{$company->telephone}} Cell: {{$company->cell_phone ?? '-'}}
                    </p>
                </td>
            </tr>
        </table>
        <table style="width: 100%;">
            <tr>
                <td colspan="8" style="border:solid 0 transparent !important;" class="text-center">
                    <h5 style="font-size: 11px;"><b>QUOTATION</b></h5>

                </td>
            </tr>
            <tr>
                <td style="font-size: 10px;border:solid 0 transparent !important;" colspan="4">
                    <p>{{$header->name}} <br>
                        {{$header->physical_address}} <br>
                        {{$header->postal_address}}</p>

                    <p>{{$header->first_name}} {{$header->middle_name}} {{$header->last_name}} <br>
                        {{$header->mobile}} <br>
                        {{$header->email}}</p>
                </td>
                <td style="font-size: 10px;border:solid 0 transparent !important;" colspan="4">
                    <p style="text-align: right;"><b>Quotation Number: </b>{{$header->quote_number}} <br>
                        Quote Date: {{$header->quote_date}} <br>
                        Expiring Date: {{$header->expiring_date}} <br>
                        Prepared By : {{$header->prepared_by}} <br>
                        Position: {{$header->position}} <br>
                        Phone: {{$header->phone ?? '-'}} <br>
                        Email: {{$header->prepared_by_email}}</p>
                </td>
            </tr>
        </table>
        <table class="table table-sm table-bordered">
            <thead>
               
                <tr style="font-size: 10px !important; background-color: #75ee4a !important;">
                    <th style="width:4% !important">No</th>
                    @if($header->quotation_type == 'Analysis')
                    <th>Sample Type</th>
                    @endif
                    <th>Part No</th>
                    @if($header->quotation_type == 'General')
                    <th style="width:15% !important">Image</th>
                    @endif
                    <th style="width:35% !important">Description</th>
                    <th style="width: 8% !important;">Qty</th>
                    <th >Unit Price</th>

                    <th>Extended Price</th>
                </tr>
            </thead>
            <tbody style="font-size: 9px !important;">
                @foreach($details as $detail)
                <tr style="border-bottom: solid 1px grey !important;">
                    <td>{{$loop->iteration}}</td>
                    @if($header->quotation_type == 'Analysis')
                    <td>{{$detail->sample_type_name}}</td>
                    <td>{{$detail->part_no_final}}</td>
                    @else
                    <td>{{$detail->part_no}}</td>
                    @endif
                    @if($header->quotation_type == 'General')
                    <td>
                        @if($detail->photo_url != '')
                        <img src="{{$detail->photo_url_approved}}" style="height:80px; width:auto; margin-top:15px !important" alt="image">
                    </td>
                    @endif
                    <td>

                        <p><b>Item Name :</b> {{$detail->item_name}}</p>
                        <p><b>Description :</b> {{$detail->description}}</p>
                    </td>
                    @else
                    <td style="min-width: 130px;">
                        <p class="mb-0"><b>{{$detail->sample_type_name}}</b></p>
                        <p class="mb-0"><b>Description:</b></p>
                        @foreach($detail->default as $da)
                        <span>{{$da}}, </span>
                        @endforeach
                        @foreach($detail->sub_acc as $sb)
                        <span>{{$sb}}* <img src="{{$tick}}" height="8" width="8" alt="">, </span>
                        @endforeach
                        @foreach($detail->sub_analytes as $sa)
                        <span>{{$sa}}*, </span>

                        @endforeach
                        @foreach($detail->acc_analytes as $acc)
                        <span class="">{{$acc}} <img src="{{$tick}}" height="8" width="8" alt="">, </span>
                        @endforeach

                    </td>
                    @endif
                    <td class="text-center">{{$detail->quantity}}</td>
                    <td style="text-align: right;">{{number_format($detail->unit_price,2)}}</td>

                    <td style="text-align: right;">{{number_format($detail->extended_price,2)}}</td>

                </tr>
                @endforeach
                <tr>
                    <td colspan="{{$header->quotation_type == 'General' ? 7 : 7 }}"></td>

                </tr>
                <tr>
                    <table class="table table-sm" style="border: solid 0 transparent;width:100%">
                        <tr>
                            <td style="font-size: 12px; font-weight:600;text-align:right;  width:90%;;border: solid 0 transparent !important"><span>Sub Total:</span> </td>
                            <td style="font-size: 12px; font-weight:600;text-align:right; width:10%"> {{number_format($header->sub_total,2)}}</td>


                        </tr>
                        <tr>
                            <td style="font-size: 12px; font-weight:600;text-align:right; width:90%;;border: solid 0 transparent !important"><span>Tax: </span> </td>
                            <td style="font-size: 12px; font-weight:600;text-align:right; width:10%"> {{number_format($header->tax,2)}}</td>

                        </tr>
                        <tr>
                            <td style="font-size: 12px; font-weight:600;text-align:right; width:90%;border: solid 0 transparent !important"><span>Total ({{$currency_name}}): </span> </td>
                            <td style="font-size: 12px; font-weight:600;text-align:right; width:10%">{{number_format($header->total_amount,2)}}</td>

                        </tr>
                    </table>
                </tr>
            </tbody>
        </table>

    </main>
    <footer class="footer">
        <div style="font-size: 9px;">
            <h5 style="font-weight: 800;font-size:10px" class="mb-1">Terms of Sale</h5>
            <p>
                <b>Prices: </b>{{$terms_array['prices']}} {{$currency_name}} <br>
                <b>Service Delivery: </b>{{$header->service_delivery}} <br>
                <b>Payments: </b>{{$header->payments}} <br>
                <b>Quote Specification: </b>{{$header->quote_specification}} <br>
                <b>Approved By: </b>{{isset(getUserById($header->approved_by)->name) ? getUserById($header->approved_by)->name: '-' }}
            </p>
            <h5 style="font-size: 10px; font-weight:800" class="mt-2">Additional Information</h5>
            <p>{{$header->additional_info}}<br>
                <span style="font-weight: 510;">{{$header->payment_info}} </span> </p>
            <div class="bank text-center">
                <p>
                    Cheques made payable to <b>{{$company->name}}</b> <br>
                    <b>Bank Details: </b>Bank Name: <b>{{$bankarr['bank_name']}}</b> Account No: <b>{{$bankarr['account_no']}}</b> Swift Code: <b>{{$bankarr['swift_code']}}</b> <br>
                    Bank Code: <b>{{$bankarr['bank_code']}}</b> Branch Code: <b>{{$bankarr['branch_code']}}</b> <br>
                    <b>Mobile Remittance: </b>Mpesa Paybill: <b>{{$bankarr['paybill']}}</b> Account Name: <b>{{$bankarr['account']}}</b> <br>
                    <span style="font-size: 30px; font-weight:600">-</span> End Of Document <span style="font-size: 30px; font-weight:600">-</span>
                </p>
            </div>
            <div>
                <span style="font-size:8px;position:absolute !important;bottom:0 !important"><a target="_blank" href="https://imaralims.com">Powered by ImaraLIMS</a></span>
            </div>
        </div>
    </footer>
    <script type="text/php">
        if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $size = 10;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width) / 1.9;
        $y = $pdf->get_height() - 30;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>

</body>

</html>