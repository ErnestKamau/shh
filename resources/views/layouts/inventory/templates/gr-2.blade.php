<html>
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type">
  	<style>
      table{
        border-collapse: collapse;
      }
			.nb tr td{
				border: none !important;
			}

      td{
        border:1px solid;
      }

			.c1{
				font-family: monospace;
				font-size: 11px;
			}

      body{
        max-width: 210mm;
        margin: auto;
        font-family: Calibri, 'Gill Sans', 'Gill Sans MT', 'Trebuchet MS', sans-serif
      }
      
      .c60 {
        vertical-align: baseline;
        font-weight: 700;
      }
      .c91 {
        margin-left: -5pt;
        padding-top: 0pt;
        padding-bottom: 10pt;
        line-height: 1.0;
        orphans: 2;
        widows: 2;
        text-align: left;
      }

      .c54 {
        font-size: 9pt;
      }
      .c26 {
        vertical-align: baseline;
        color: #000000;
        font-weight: 700;
      }

      td{
        padding-left: 4px !important;
      }

      p {
        margin: 0;
        color: #000000;
        font-size: 10pt;
        font-family: "Calibri";
      }

      .c4.c13{
        height: 15pt;
      }
		</style>
  </head>
  <body>
    <table class="nb" style="width: 100%">
			<tr>
				<td style="width: 30%">
					<img src="https://aqualytic.imaralims.com/storage/companies/EixLy7nPubNy0uaGNFGlF4Ot9nzVHXpcDnMsEc0u.jpg" style="max-width: 150px" />
				</td>
				<td style="width: 30%; text-align: right; color: #000066">
					<span>AQUALYTIC LABORATORIES LIMITED</span><br>
					<span>P.O. Box 4600 - 00506 Nairobi</span><br>
					<span>Ramco Court</span><br>
					<span>Off Mombasa Road</span><br>
					<span>Email: lab@aqualyticlab.com</span><br>
					<span>Website: www.aqualyticlab.com</span><br>
					<span>Mobile: 0722547344</span>
				</td>
			</tr>
		</table>
    <p class="c91"><span class="c60">&nbsp; &nbsp; &nbsp; &nbsp;AQL-LMS-F660-004 PURCHASE REQUISITION &nbsp;</span></p>
    <a id="t.a95ace36f1f15a11612caacfc12ed62799e37bd0"></a><a id="t.0"></a>
    <table class="c16">
      <tbody>
        <tr class="c71">
          <td class="c50" colspan="4" rowspan="1">
            <p class="c36 c13"><span class="c26 c54">&nbsp; &nbsp;Requesting Department</span></p>
						<span class="c1">&nbsp;{{ $grequest->requesting_department }}</span>
          </td>
          <td class="c18" colspan="5" rowspan="1">
            <p class="c36 c66"><span class="c26 c54">&nbsp; &nbsp;Laboratory</span></p>
						<span class="c1">&nbsp; {{ $grequest->laboratory }}</span>
          </td>
          <td class="c95" colspan="6" rowspan="1">
            <p class="c36 c67"><span class="c26 c54">&nbsp; &nbsp;Date Required </span></p>
						<span class="c1">&nbsp;&nbsp;{{ $grequest->date_required }}</span>
          </td>
          <td class="c59" colspan="3" rowspan="1">
            <p class="c4"><span class="c1"></span></p>
          </td>
        </tr>
        <tr class="c92">
          <td class="c34" colspan="1" rowspan="2">
            <p class="c36 c73"><span class="c26">Item No.</span></p>
          </td>
          <td class="c27 c74" colspan="4" rowspan="2">
            <p class="c36 c13"><span class="c26">Item Description</span></p>
          </td>
          <td class="c44 c27" colspan="4" rowspan="2" nowrap>
            <p class="c6"><span class="c26">Product No.</span></p>
          </td>
          <td class="c44 c27" colspan="4" rowspan="2">
            <p class="c33"><span class="c60">Purpose </span></p>
          </td>
          <td class="c27 c81" colspan="3" rowspan="2">
            <p class="c36 c13"><span class="c26">Qty</span></p>
          </td>
          <td class="c27 c88" colspan="2" rowspan="1" nowrap>
            <p class="c36 c13"><span class="c26">Approved Quotation</span></p>
          </td>
        </tr>
        <tr class="c99">
          <td class="c27 c44" colspan="1" rowspan="1" nowrap>
            <p class="c36"><span class="c26 c54">Last Unit Price </span></p>
          </td>
          <td class="c44 c27" colspan="1" rowspan="1" nowrap>
            <p class="c36 c13"><span class="c26 c54">Ext. Cost - KES</span></p>
          </td>
        </tr>
				@php($total1 = 0)
				@php($total2 = 0)
				@foreach ($grequest->items as $item)
					@php($total1+=floatval($item->last_unit_price))
					@php($total2+=floatval($item->ext_cost))
					<tr class="c92">
						<td class="c10" colspan="1" rowspan="1">
							<p class="c4 c13"><span class="c1">{{ $loop->iteration }}</span></p>
						</td>
						<td class="c43 c27" colspan="4" rowspan="1">
							<p class="c4"><span class="c1">{{ $item->item_description }}</span></p>
						</td>
						<td class="c8" colspan="4" rowspan="1">
							<p class="c4 c13"><span class="c1">{{ $item->product_no }}</span></p>
						</td>
						<td class="c8" colspan="3" rowspan="1">
							<p class="c4"><small class="c1">{{ $item->purpose }}</small></p>
						</td>
						<td class="c14" colspan="4" rowspan="1">
							<p class="c4"><span class="c1">{{ $item->qty }}</span></p>
						</td>
						<td class="c8" colspan="1" rowspan="1">
							<p class="c4"><span class="c1" style="text-align: right">{{ number_format($item->last_unit_price,2) }}</span></p>
						</td>
						<td class="c8" colspan="1" rowspan="1">
							<p class="c4"><span class="c1" style="text-align: right">{{ number_format($item->ext_cost,2) }}</span></p>
						</td>
					</tr>
				@endforeach
        <tr class="c9">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c43 c27" colspan="5" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c8" colspan="4" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c8" colspan="3" rowspan="1">
            <p class="c4"><span class="c1"></span></p>
          </td>
          <td class="c14" colspan="3" rowspan="1">
            <p class="c4 c13"><span class="c1">TOTAL</span></p>
          </td>
          <td class="c8" colspan="1" rowspan="1" style="text-align: right">
            <p class="c36 c13"><span class="c26">{{ number_format($total1, 2) }}</span></p>
          </td>
          <td class="c8" colspan="1" rowspan="1" style="text-align: right">
            <p class="c4"><span class="c1">{{ number_format($total2, 2) }}</span></p>
          </td>
        </tr>
        <tr class="c56">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c17" colspan="1" rowspan="1" nowrap>
            <p class="c36 c13"><span class="c26">PR Approving</span></p>
          </td>
          <td class="c25" colspan="5" rowspan="1">
            <p class="c36 c13"><span class="c26">&nbsp;Name </span></p>
            <p class="c4"> </p>
          </td>
          <td class="c30" colspan="5" rowspan="1">
            <p class="c36 c13"><span class="c26">Position </span></p>
          </td>
          <td class="c42" colspan="4" rowspan="1">
            <p class="c36 c13"><span class="c26">Date </span></p>
          </td>
          <td class="c18" colspan="2" rowspan="1">
            <p class="c36 c13"><span class="c26">Sign </span></p>
          </td>
        </tr>
        @php($userM = json_decode($grequest->requested_by_1))
        <tr class="c79">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c17" colspan="1" rowspan="1">
            <p class="c36 c13"><span class="c36">Requested &nbsp;By:</span></p>
          </td>
          <td class="c25" colspan="5" rowspan="1" nowrap>
            <p class="c4"> <span class="c1">{{ $userM->user->name ?? '' }}</span></p>
          </td>
          <td class="c30" colspan="5" rowspan="1">
            <p class="c4"><span class="c1"><span class="c1" nowrap>{{ $userM->user->position ?? '' }}</span></span></p>
          </td>
          <td class="c42" colspan="4" rowspan="1" nowrap>
            <p class="c4"><span class="c1"><span class="c1" nowrap>{{ isset($userM->time) ? \Carbon\Carbon::parse($userM->time)->format('Y-m-d') : '' }}</span></span></p>
          </td>
          <td class="c18" colspan="2" rowspan="1">
            @if(isset($userM->user->signature))
              <p class="c4"><img src="{{ $userM->user->signature }}" style="height: 35px" /></p>
            @endif
          </td>
        </tr>
        @php($userM = json_decode($grequest->checked_by_1))
        <tr class="c82">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c17" colspan="1" rowspan="1">
            <p class="c36 c13"><span class="c36">Checked By:</span></p>
          </td>
          <td class="c25" colspan="5" rowspan="1">
            <p class="c4"> <span class="c1">{{ $userM->user->name ?? '' }}</span></p>
          </td>
          <td class="c30" colspan="5" rowspan="1">
            <p class="c4"><span class="c1"><span class="c1">{{ $userM->user->position ?? '' }}</span></span></p>
          </td>
          <td class="c42" colspan="4" rowspan="1" nowrap>
            <p class="c4"><span class="c1"><span class="c1">{{ isset($userM->time) ? \Carbon\Carbon::parse($userM->time)->format('Y-m-d') : '' }}</span></span></p>
          </td>
          <td class="c18" colspan="2" rowspan="1">
            @if(isset($userM->user->signature))
              <p class="c4"><img src="{{ $userM->user->signature }}" style="height: 35px" /></p>
            @endif
          </td>
        </tr>
        @php($userM = json_decode($grequest->approved_by_1))
        <tr class="c79">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c17" colspan="1" rowspan="1">
            <p class="c13 c36"><span class="c36">Approved By:</span></p>
          </td>
          <td class="c25" colspan="5" rowspan="1">
            <p class="c4"> <span class="c1">{{ $userM->user->name ?? '' }}</span></p>
          </td>
          <td class="c30" colspan="5" rowspan="1">
            <p class="c4"><span class="c1"><span class="c1">{{ $userM->user->position ?? '' }}</span></span></p>
          </td>
          <td class="c42" colspan="4" rowspan="1" nowrap>
            <p class="c4"><span class="c1"><span class="c1">{{ isset($userM->time) ? \Carbon\Carbon::parse($userM->time)->format('Y-m-d') : '' }}</span></span></p>
          </td>
          <td class="c18" colspan="2" rowspan="1">
            @if(isset($userM->user->signature))
              <p class="c4"><img src="{{ $userM->user->signature }}" style="height: 35px" /></p>
            @endif
          </td>
        </tr>
        <tr class="c51">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c27 c62" colspan="17" rowspan="1">
            <p class="c36 c13"><span class="c26">QUOTATIONS/TENDERS RECEIVED</span></p>
          </td>
        </tr>
        <tr class="c56">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c41 c27" colspan="4" rowspan="1">
            <p class="c36 c13"><span class="c26">Suppliers ( Quoted)</span></p>
          </td>
          <td class="c27 c70" colspan="4" rowspan="1" nowrap>
            <p class="c36 c13"><span class="c26">Supplier Approved </span></p>
          </td>
          <td class="c27 c78" colspan="5" rowspan="1" nowrap>
            <p class="c86 c87"><span class="c26">Quote Date</span></p>
          </td>
          <td class="c31 c27" colspan="4" rowspan="1" nowrap>
            <p class="c36 c13"><span class="c26">Quote Approved &nbsp; YES &nbsp;/ &nbsp; NO</span></p>
          </td>
        </tr>
				@foreach ($grequest->quotes() ?? [] as $quote)
        <tr class="c56" valign="middle">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></small></span></p>
          </td>
          <td class="c27 c41" colspan="4" rowspan="1" nowrap>
            <p class="c1">{{ $quote->supplier }}</p>
            <p class="c4 c13"><span class="c1">{{ $quote->item_description }} - <small>({{ $quote->qty }} @ KES {{ $quote->amount }})</span></p>
          </td>
          <td class="c17" colspan="2" rowspan="1">
            <p class="c36 c13"><span class="c1">YES</span> &nbsp; 
              <img src="{{ $quote->is_approved == 1 ? '/images/icons/checked.png' : '/images/icons/check-empty.png' }}" style="height: 14px; margin-top: 3px"/>
            </p>
          </td>
          <td class="c17" colspan="2" rowspan="1">
            <p class="c36 c13"><span class="c1">&nbsp;NO</span> &nbsp; 
              <img src="{{ $quote->is_approved == 0 ? '/images/icons/checked.png' : '/images/icons/check-empty.png' }}" style="height: 14px; margin-top: 3px"/>
            </p>
          </td>
          <td class="c78 c27" colspan="5" rowspan="1" nowrap>
            <p class="c4 c86"><span class="c1">{{ \Carbon\Carbon::parse($quote->created_at)->format('Y-m-d') }}</span></p>
          </td>
          <td class="c31 c27" colspan="4" rowspan="1">
            <p class="c4 c13"><span class="c1">{{ $quote->awarded }}</span></p>
          </td>
        </tr>
				@endforeach
        <tr class="c56">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c62 c27" colspan="17" rowspan="2">
            <p class="c36 c13"><span class="c1">Reason For Approving</span></p>
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
        </tr>
        <tr class="c56">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
        </tr>
        <tr class="c56">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c12" colspan="2" rowspan="1" nowrap>
            <p class="c36 c13"><span class="c26">Quote Approving</span></p>
          </td>
          <td class="c29" colspan="4" rowspan="1">
            <p class="c36 c13"><span class="c26">&nbsp;Name </span></p>
          </td>
          <td class="c30" colspan="5" rowspan="1">
            <p class="c36 c13"><span class="c26">Position </span></p>
          </td>
          <td class="c42" colspan="4" rowspan="1">
            <p class="c36 c13"><span class="c26">Date </span></p>
          </td>
          <td class="c18" colspan="2" rowspan="1">
            <p class="c36 c13"><span class="c26">Sign </span></p>
          </td>
        </tr>
        @php($userM = json_decode($grequest->requested_by_2))
        <tr class="c79">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c12" colspan="2" rowspan="1">
            <p class="c36 c13"><span class="c36">Requested &nbsp;By:</span></p>
          </td>
          <td class="c25" colspan="5" rowspan="1">
            <p class="c4"> <span class="c1">{{ $userM->user->name ?? '' }}</span></p>
          </td>
          <td class="c30" colspan="5" rowspan="1">
            <p class="c4"><span class="c1"><span class="c1">{{ $userM->user->position ?? '' }}</span></span></p>
          </td>
          <td class="c42" colspan="4" rowspan="1" nowrap>
            <p class="c4"><span class="c1"><span class="c1">{{ isset($userM->time) ? \Carbon\Carbon::parse($userM->time)->format('Y-m-d') : '' }}</span></span></p>
          </td>
          <td class="c18" colspan="2" rowspan="1">
            @if(isset($userM->user->signature))
              <p class="c4"><img src="{{ $userM->user->signature }}" style="height: 35px" /></p>
            @endif
          </td>
        </tr>
        @php($userM = json_decode($grequest->checked_by_2))
        <tr class="c84">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c12" colspan="2" rowspan="1">
            <p class="c36 c13"><span class="c36">Checked By:</span></p>
          </td>
          <td class="c25" colspan="5" rowspan="1">
            <p class="c4"> <span class="c1">{{ $userM->user->name ?? '' }}</span></p>
          </td>
          <td class="c30" colspan="5" rowspan="1">
            <p class="c4"><span class="c1"><span class="c1">{{ $userM->user->position ?? '' }}</span></span></p>
          </td>
          <td class="c42" colspan="4" rowspan="1" nowrap>
            <p class="c4"><span class="c1"><span class="c1">{{ isset($userM->time) ? \Carbon\Carbon::parse($userM->time)->format('Y-m-d') : '' }}</span></span></p>
          </td>
          <td class="c18" colspan="2" rowspan="1">
            @if(isset($userM->user->signature))
              <p class="c4"><img src="{{ $userM->user->signature }}" style="height: 35px" /></p>
            @endif
          </td>
        </tr>
        @php($userM = json_decode($grequest->approved_by_2))
        <tr class="c77">
          <td class="c10" colspan="1" rowspan="1">
            <p class="c4 c13"><span class="c1"></span></p>
          </td>
          <td class="c12" colspan="2" rowspan="1">
            <p class="c36 c13"><span class="c36">Approved By:</span></p>
          </td>
          <td class="c25" colspan="5" rowspan="1">
            <p class="c4"> <span class="c1">{{ $userM->user->name ?? ''  }}</span></p>
          </td>
          <td class="c30" colspan="5" rowspan="1">
            <p class="c4"><span class="c1"><span class="c1">{{ $userM->user->position ?? ''  }}</span></span></p>
          </td>
          <td class="c42" colspan="4" rowspan="1" nowrap>
            <p class="c4"><span class="c1"><span class="c1">{{ isset($userM->time) ? \Carbon\Carbon::parse($userM->time)->format('Y-m-d') : ''  }}</span></span></p>
          </td>
          <td class="c18" colspan="2" rowspan="1">
            @if(isset($userM->user->signature))
              <p class="c4"><img src="{{ $userM->user->signature }}" style="height: 35px" /></p>
            @endif
          </td>
        </tr>
      </tbody>
    </table>
    <p class="c4"><span class="c68"></span></p>
    <div>
      <p class="c36 c93"><span class="c1">REVISION [01] ISSUE DATE: &nbsp;22.04.2020 | Authorized by: GM &nbsp; &nbsp;| Approved by: &nbsp;TM &nbsp; &nbsp; &nbsp; &nbsp; Page </span><span class="c1">&nbsp;of </span></p>
      <p class="c36 c93"><span class="c89">AQL-LMS-F-660-004 &ndash; Purchase Requisition</span></p>
    </div>
  </body>
</html>