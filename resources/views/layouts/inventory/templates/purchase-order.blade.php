<?php
	$supplierDetails = \App\Supplier::find($entity->supplier_id);
?>
<?php
	$REQUESTER = \App\User::find($entity->request_initiator);
?>
<html>
	<body style="font-size: 13px">
		<div style="">
			<table border="0" style="border:none; border-collapse: collapse; width: 100%; margin-bottom: 10px;">
				<tr style="vertical-align: middle">
					<td colspan="2">
						<img src="https://aqualytic.imaralims.com/storage/companies/EixLy7nPubNy0uaGNFGlF4Ot9nzVHXpcDnMsEc0u.jpg" style="max-width:200px" />
					</td>
					<td colspan="2" style="text-align: right">
						AQUALYTIC LABORATORIES LIMITED<br>
						P.O. Box 4600 - 00506 Nairobi<br> 
						Ramco Court <br>
						Off Mombasa Road<br>
						Email: lab@aqualyticlab.com<br>
						Website: www.aqualyticlab.com<br>
						Mobile: +254 722547344<br>
					</td>
				</tr>
			</table>
			<br>
			<div style="border: 2px solid #000"></div>
			<div style="padding: 10px 0px">AQL-LMS-F-660-005 PURCHASE ORDER</div>
			<br>
			<br>
			<br>
			<table style="border:1px solid #000; width:60% !important; border-collapse: collapse; text-align: left">
				<tr style="border-bottom:1px solid #000!important;">
					<th style="padding: 4px 2px; border-right:1px solid #000; border-top: 1px solid #000!important; text-align:left !important">Date</th>
					<th style="padding: 4px 2px; border-right:1px solid #000; border-top: 1px solid #000!important; text-align:left !important">PO No</th>
				</tr>
				<tr style="border-top:1px solid #000!important;">
					@php($time = strtotime($entity->created_at))
					<td style="height: 60px; font-family: monospace; color: #454545;padding: 4px 2px; border-right: 1px solid #000; border-top: 1px solid #000!important">{{ date('d \of F, Y', $time) }}</td>
					<td style="height: 60px; font-family: monospace; color: #454545;padding: 4px 2px; border-right:1px solid #000; border-top: 1px solid #000!important">{{ $entity->request_code }}</td>
				</tr>
			</table>
			<br>
			<table border="0" style="border: none; border-collapse: collapse; width: 100%; text-align: left">
				<tr>
					<td colspan="40%" style="vertical-align: top">
						<table style="border-collapse: collapse; width: 100%; border:1px solid #000; text-align:left">
							<tr style="border-bottom:1px solid #000 !important;">
								<th style="padding: 7px 2px;  text-align:left !important">Supplier</th>
							</tr>
							<tr style="border-top:1px solid #000; ">
								<td style="font-family: monospace; border-top: 1px solid #000!important; padding: 7px 2px">
									{{ $supplierDetails->name }}<br>
									{{ $supplierDetails->address }}<br>
									Phone: {{ $supplierDetails->phone }}<br>
									Email: {{ $supplierDetails->email }}<br>
									VAT No: {{ $supplierDetails->vat_number }}<br>
									PIN No: {{ $supplierDetails->pin_number }}
								</td>
							</tr>
						</table>
					</td>
					<td colspan="20%">&nbsp;</td>
					<td colspan="40%" style="vertical-align: top">
						<table style="border-collapse: collapse; width: 100%; padding: 7px 2px; text-align: left; border:1px solid #000; ">
							<tr style="border-bottom:1px solid #000 !important">
								<th style="padding: 7px 2px; text-align:left !important">Ship To</th>
							</tr>
							<tr style="border-top:1px solid #000; ">
								<td style="border-top: 1px solid #000!important; font-family: monospace; padding: 7px 2px">
									AQUALYTIC LABORATORIES LIMITED<br>
									P.O. Box 4600 - 00506, Nairobi<br>
									Contact: {{ getConfigByName('po_contact2_name') ? getConfigByName('po_contact2_name')[0]->value : '' }}<br>
									Phone: {{ getConfigByName('po_contact_telephone') ? getConfigByName('po_contact_telephone')[0]->value : '' }}<br>
									Email: {{ getConfigByName('po_contact_email') ? getConfigByName('po_contact_email')[0]->value : '' }}<br><br>
									Department: {{ $REQUESTER->department()->name }}<br>
									Receiver: {{ $REQUESTER->name }}
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<br><br>
			<?php
				$allItems = $entity->items($entity->ammendment) ?? array();
				$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());
			?>
			<div style="border: 1px solid #000">
				<table style="border-collapse: collapse; width:99.9%; margin:1px 0.05%; border:1px solid #000; text-align: left">
					<tr>
						<th colspan="7" style="padding: 7px 2px;  border-right:1px solid #000;  border-bottom:1px solid #000">Product Description</th>
						<th colspan="3" style="padding: 7px 2px; border-right:1px solid #000;  border-bottom:1px solid #000">Cat/Lot No</th>
						<th colspan="1" style="padding: 7px 2px; border-right:1px solid #000; text-align: right;  border-bottom:1px solid #000">Qty.</th>
						<th colspan="2" style="padding: 7px 2px; border-right:1px solid #000; text-align: right;  border-bottom:1px solid #000">Unit Cost</th>
						<th colspan="2" style="padding: 7px 2px; text-align: right; border-bottom:1px solid #000">Ext. Cost</th>
					</tr>
					@php($total = 0)
					@foreach ($normalItems as $item)
						@php($total += floatval($item->net_value))
						<tr style="border-top:1px solid #000">
							<td colspan="7" style="padding: 7px 2px; font-family: monospace; border-right:1px solid #000;  border-bottom:1px solid #000">{{ $item->item_name }}</td>
							<td colspan="3" style="padding: 7px 2px; font-family: monospace; border-right:1px solid #000;  border-bottom:1px solid #000">{{ $item->sap_code }}</td>
							<td colspan="1" style="padding: 7px 2px; font-family: monospace; border-right:1px solid #000; text-align: right;  border-bottom:1px solid #000">{{ number_format($item->quantity,2) }}</td>
							<td colspan="2" style="padding: 7px 2px; font-family: monospace; border-right:1px solid #000; text-align: right;  border-bottom:1px solid #000">{{ number_format($item->net_value/$item->quantity,2) }}</td>
							<td colspan="2" style="padding: 7px 2px; font-family: monospace; border-right:1px solid #000; text-align: right;  border-bottom:1px solid #000">{{ number_format($item->net_value, 2) }}</td>
						</tr>
					@endforeach
					<?php
						$subtotal = ((floatval($total)*100)/116);
						$tax = $total - $subtotal;
					?>
					<tfoot>
						<tr style="border-top:1px solid #000 !important">
							<th colspan="13" style="border-right:1px solid #000;  border-bottom:1px solid #000; text-align: right; padding:7px">Sub Total</th>
							<td colspan="2" style=" font-family: monospace; padding:7px;  border-bottom:1px solid #000; text-align: right">{{ number_format($subtotal, 2) }}</td>
						</tr>
						<tr style="border-top:1px solid #000 !important">
							<th colspan="13" style="border-right:1px solid #000;  border-bottom:1px solid #000; text-align: right; padding:7px">16% Tax</th>
							<td colspan="2" style=" font-family: monospace; padding:7px;  border-bottom:1px solid #000; text-align: right">{{ number_format($tax, 2) }}</td>
						</tr>
						<tr style="border-top:1px solid #000 !important">
							<th colspan="13" style="border-right:1px solid #000;  border-bottom:1px solid #000; text-align: right; padding:7px">Total - KES</th>
							<td colspan="2" style=" font-family: monospace; padding:7px;  border-bottom:1px solid #000; text-align: right">{{ number_format($total, 2) }}</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</body>
</html>