<style type="text/css">
	@media print{
		button{
				display:none;
		}
	}
</style>
<div style="padding: 10px 15px">
	<button style="padding: 5px 10px; font-size: 13px" onclick="window.print()">Print</button>
</div>
<table style="border-collapse: collapse; width: 100%; height: 84px; margin-bottom: 10px;" border="0">
	<tbody>
		<tr style="">
			<td colspan="3">
				<table style="width: 100%;" border="0">
					<tbody>
						<tr>
							<td style="width: 70%; "><img src="https://www.flowertrials.com/images/member_social_00000037_large.jpg" width="180" height="auto" alt="" />
								<br /><br /><br />
								{{ $supplier->name }}
								<br />{{ $supplier->building }}, {{ $supplier->street }}, {{ $supplier->town }}.
								<br />Tel: {{ $supplier->phone }}
								<br /> {{ $supplier->address }}
							</td>
							<td style="width: 30%; ">
								Ethiopia Cuttings PLC<br /> <strong>Farm:</strong><br /> East Showa Zone<br /> Lume District<br /> Hawassa Road<br />Koka Kebele<br /> Tel: +251 224590151/55<br /> Fax: +251 116633074 <br />www.syngenta.com<br /><br />
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<table style="border-collapse: collapse; width: 100.167%; margin-bottom: 10px;" border="1">
	<thead>
		<tr style="">
			<td style=" text-align: left; padding-left: 6px;" colspan="8">REQUEST FOR QUOTATION</td>
		</tr>
		<tr style="">
			<td style=" text-align: left; font-size: 9px; width: 3.83333%; padding-left: 6px;" colspan="2"><br /> <strong>RFQ No. {{ $entity->request_code }}</strong><br /><br /><br /> <strong>Purpose: {{ $requisition->description }}</strong><br /><br /></td>
			<td style=" text-align: left; font-size: 9px; width: 24.1939%; padding-left: 6px;" colspan="6">
					<br />
					<div>MANDATORY/SUPPLIER ADDRESS</div>
					<hr />
					Business Name :- Polucon
					<br /> Location of Business Premises:
					<br /> Postal Address:
					<br /> TEL No : 254 72222 99 44 | 716 200 222
					<br /> E-mail : polucon@polucon.com
					<br /> Date 23/08/2021
					<br /> Attn:- Ermias Akalu
					<br />
					<br />
			</td>
		</tr>
		<tr style="">
			<th style=" text-align: center; font-size: 8px; width: 3.83333%;">PR No</th>
			<th style=" text-align: center; font-size: 8px; width: 24.1939%;">Description</th>
			<th style=" text-align: center; font-size: 8px; width: 12.6645%;">Identification</th>
			<th style=" text-align: center; font-size: 8px; width: 12.1692%;">Quantity</th>
			<th style=" text-align: center; font-size: 8px; width: 10.3361%;">Unit of Measure</th>
			<th style=" text-align: center; font-size: 8px; width: 11.8361%;">Unit Price</th>
			<th style=" text-align: center; font-size: 8px; width: 11.8361%;">Total</th>
			<th style=" text-align: center; font-size: 8px; width: 16.0101%;">Remark</th>
		</tr>
	</thead>
	<tbody>
		@foreach($items as $it)
			<tr style="">
				<td style=" text-align: center; font-size: 10px;">{{ $entity->request_code }}</td>
				<td style=" width: 24.1939%; font-size: 10px; padding-left: 6px;">{{  $it->name }}</td>
				<td style=" font-size: 10px; width: 12.6645%; padding-left: 6px;">{{ $it->description }}</td>
				<td style=" width: 10.3361%; font-size: 10px; padding-right: 6px; text-align:right">{{  number_format($it->quantity,3) }}</td>
				<td style=" width: 12.1692%; font-size: 10px; padding-left: 6px;">{{ $it->unit_type }}</td>
				<td style=" width: 11.8361%; font-size: 10px; padding-left: 6px;">&nbsp;</td>
				<td style=" width: 11.8361%; font-size: 10px; padding-left: 6px;">&nbsp;</td>
				<td style=" width: 16.0101%; font-size: 10px; padding-left: 6px;">&nbsp;</td>
			</tr>
		@endforeach
	</tbody>
</table>
<div style="padding-bottom: 8px;">
	<span style="font-size: 11px; padding-left: 6px;">
		<strong>PLEASE SEND TO US YOUR FORMAL QUOTATION (PFI)</strong>
	</span>
</div>
<table style="border-collapse: collapse; width: 100%; margin-bottom: 10px;" border="1">
	<tbody>
		<tr style="">
			<td style=" width: 33%; font-size: 10px; text-align: left; padding: 5px" colspan="3">
				<strong>OUR Terms of Payment : {{ $supplier->payment_terms }}</strong>
				<br /> Expected Delivery Date :- Urgent
				<br /> Supplier Delivery Date: STRICTLY
				<br /> <strong>DELIVER TO:</strong> KOKA / ADDIS ABABA ( /*** checkbox or dropdown option here ***/)
				<br /> <strong>STORE:</strong><br /> <strong>Special Handling Requirements:</strong><br /><br />
			</td>
		</tr>
		<tr>
			<td style="width: 50%; padding: 3px 6px">Prepared by: <small>&nbsp;&nbsp;&nbsp;&nbsp;{{ $preparedBy->name }}</small></td>
			<td style="width: 25%; padding: 3px 6px">Date: <small>&nbsp;&nbsp;&nbsp;{{ \Carbon\Carbon::parse($finalApproval->created_at)->format('Y-m-d') }}</small></td>
			<td style="width: 25%; padding: 3px 6px">Sign: &nbsp;&nbsp;&nbsp;
				{!! isset($preparedBy->electronic_sig) && trim($preparedBy->electronic_sig) != "" ?
					'<img src="'.$preparedBy->electronic_sig.'" style="height: 25px" />' : '' !!}
			</td>
		</tr>
		<tr>
			<td style="width: 50%; padding: 3px 6px">Approved by: <small>&nbsp;&nbsp;&nbsp;&nbsp;{{ $finalApproval->name }}</small></td>
			<td style="width: 25%; padding: 3px 6px">Date: <small>&nbsp;&nbsp;&nbsp;{{ \Carbon\Carbon::parse($finalApproval->approved_at)->format('Y-m-d') }}</small></td>
			<td style="width: 25%; padding: 3px 6px">Sign: &nbsp;&nbsp;&nbsp;
				{!! isset($finalApproval->electronic_sig) && trim($finalApproval->electronic_sig) != "" ?
					'<img src="'.$finalApproval->electronic_sig.'" style="height: 25px" />' : '' !!}
			</td>
		</tr>
	</tbody>
</table>