<style>
	table{
		border-collapse: collapse;
		width: 100%;
	}

	tr td{
		border: 1px solid #000;
		padding: 5px 0px;
	}

	.nb tr td{
		border: none !important;
	}
</style>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<table class="nb">
	<tr>
		<td colspan="50%">
			<img src="https://aqualytic.imaralims.com/storage/companies/EixLy7nPubNy0uaGNFGlF4Ot9nzVHXpcDnMsEc0u.jpg" style="height: 150px" />
		</td>
		<td colspan="50%" style="text-align: right; color: #000066">
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
<br>
<hr width="100%" color="#07b2f0" />
<br>
<div style="clear: both;">
	<div style="float: left"><strong>AQL-LMS-F660-004 PURCHASE REQUISITION </strong></div>
	<div style="float: right; color: rgb(182, 4, 4)"><strong>PR No. {{ $grequest->code }}</strong></div>
</div>
<div dir="ltr">
  <p></p>
  <table style="width: 100%" cellspacing="0" cellpadding="1">
    <tr>
			<td colspan="5">
				<p lang="en-GB"><strong>Requesting Department</strong></p>
				<span style="font-family: monospace">{{ $grequest->requesting_department }}</span>
			</td>
			<td colspan="4">
				<p><strong>Laboratory</strong></p>
				<span style="font-family: monospace">{{ $grequest->laboratory }}</span>
			</td>
			<td colspan="3">
				<p><strong>Date Required </strong></p>
				<span style="font-family: monospace">{{ $grequest->date_required }}</span>
			</td>
			<td colspan="4">
				&nbsp;
			</td>
		</tr>
	</table>
	<table style="width: 100%; border-top: none" cellspacing="0">
		<tr>
			<td colspan="1">
				<strong>Item No.</strong>
			</td>
			<td colspan="5" rowspan="2">
				<strong>Item Description</strong>
			</td>
			<td colspan="2" rowspan="2">
				<strong>Product No.</strong>
			</td>
			<td colspan="2" rowspan="2">
				<strong>Purpose </strong>
			</td>
			<td colspan="2" rowspan="2">
				<strong>Qty</strong>
			</td>
			<td colspan="4" rowspan="2">
				<table style="width: 100%; height: 100%">
					<tr colspan="2">
						<td style="border: none !important;"><strong>Approved Quotation</strong></td>
					</tr>
					<tr>
						<td style="border: none; border-top: 1px solid #000; border-right: 1px solid #000; width: 50%">Last Unit Price</td>
						<td style="border: none; border-top: 1px solid #000; width: 50%">Ext. Cost - KES</td>
					</tr>
				</table>
			</td>
		</tr>
		@foreach ($grequest->items as $item)
			<tr>
				<td colspan="1">{{ $loop->iteration }}</td>
				<td colspan="5">{{ $item->item_description }}</td>
				<td colspan="2">{{ $item->product_no }}</td>
				<td colspan="2">{{ $item->purpose }}</td>
				<td colspan="2">{{ $item->qty }}</td>
				<td colspan="2">{{ $item->last_unit_price }}</td>
				<td colspan="2">{{ $item->ext_cost }}</td>
			</tr>
		@endforeach
  </table>
</div>
<div title="footer">
  <p>REVISION [01] ISSUE DATE: 22.04.2020 | Authorized by: GM | Approved by: TM Page 1 of 2</p>
  <p>AQL-LMS-F-660-004 &ndash; Purchase Requisition</p>
</div>