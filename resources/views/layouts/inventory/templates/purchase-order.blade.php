<?php
	$parentEntity = \App\RequestEntity::find($entity->parent_request_id);
	$supplierDetails = \App\Supplier::find($entity->supplier_id);
	$extras = \App\RequestEntityExtraCharge::join('module_pre_configs as mpc', 'mpc.id', 'request_entity_extra_charges.currency_id')
				->selectRaw('request_entity_extra_charges.*, mpc.name as currency')->where('request_id', $entity->id)->get();
?>
<html>
	<style type="text/css">
		@media print{
			button{
				display:none;
			}
	
			.page-break-inside{
				break-inside: avoid;
				page-break-inside: avoid;
			}
		}
	</style>
	<script type="text/php">
		if (isset($pdf)) {
			$x = 250;
			$y = 10;
			$text = "Page {PAGE_NUM} of {PAGE_COUNT}";
			$font = null;
			$size = 14;
			$color = array(255,0,0);
			$word_space = 0.0;  //  default
			$char_space = 0.0;  //  default
			$angle = 0.0;   //  default
			$pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
		}
	</script>
	<link href='https://fonts.googleapis.com/css?family=EB Garamond' rel='stylesheet'>
  <body style="font-family: 'EB Garamond', Arial, sans-serif">
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
			<tr>
        <th style="text-align: left;">
          <img src="https://www.polucon.co.ke/assets/front/img/64c152a6a6a73.png" alt="Logo" style="max-width: 200px;" />
        </th>
				<td style="text-align: right; font-size: 13px">
					<div style="font-size: 32px; clear: both">Purchase Order</div>
					<div>LPO NO: 6718</div>
				</td>
      </tr>
			<tr>
        <td>
					<strong style="color:darkgreen">Polucon Services (K) Ltd</strong> <br/>
          P.O Box 99344-80107.<br />
          Mombasa, Kenya.<br />
          Tel:0722229944 / 041 4470775.<br />
          Email: procurement@polucon.com.<br />
          Polucon House, Nyati Road, Off Links Road- Nyali.<br />
          PIN: P051097402V
        </td>
			</tr>
      <tr>
        <td style="text-align: left;">
          {{ strtoupper($supplierDetails->name) }}<br />
          Vendor Address - 
		  {{ $supplierDetails->address }}</br>
          VAT {{ $supplierDetails->vat_number }} PIN {{ $supplierDetails->pin_number }}
        </td>
				<td style="font-size: 12px; white-space: nowrap">
					<table>
						<tr>
							<th colspan="3">Shipment preference</th>
							<td colspan="2">Deliver To Office</td>
						</tr>
						<tr>
							<td style="text-align:right">Date:</td>
							<td style="text-align:right">{{ \Carbon\Carbon::parse($entity->created_at)->format('d.m.Y') }}</td>
						</tr>
						<tr>
							<td style="text-align:right">Terms:</td>
							<td style="text-align:right">Net 30</td>
						</tr>
						<tr>
							<td style="text-align:right">Quotation Ref#:</td>
							<td style="text-align:right">{{ $parentEntity->request_code }}</td>
						</tr>
					</table>
				</td>
      </tr>
    </table>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
			<thead>
				<tr>
					<th>#</th>
					<th>Description</th>
					<th>Qty</th>
					<th>Rate</th>
					<th>VAT Vatable</th>
					<th>Amount</th>
				</tr>
			</thead>
      <tbody>
				<?php
					$allItems = $entity->items($entity->ammendment, true) ?? array();
					$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

					// echo json_encode($getRFQ);
					$PO_TOTAL = 0;
					$PO_VAT = 0;
				?>
				@foreach ($normalItems as $item)
				<?php $isKitRow = !is_numeric($item->catalog_number); ?>
					<tr style="font-size: 11px">
						<td style=" text-align: center; font-size: 9px; width: 6%;">{{ $loop->iteration }}</td>
						<td style=" font-size: 9px; width: 28%;">
							@if($isKitRow)
								<strong>{{ $item->kit_item_name }}</strong>
							@else
								<strong>{{ $item->item_name }} / {{ $item->code }}</strong><br>
								{{ $item->comments }}
							@endif
						</td>
						<td style=" text-align:right">{{ $isKitRow ? 1 : number_format($item->quantity, 3) }}{{ $isKitRow ? '-' : $item->unit_type }}</td>
						<td style=" text-align:right">{{ $isKitRow ? '1' : number_format($item->net_value/$item->quantity,2) }}</td>
						<td style=" text-align:right">{{ number_format($item->net_value*(0.16), 2) }}</td>
						<td style=" text-align:right">{{ number_format($item->net_value, 2) }}</td>
					</tr>
					<?php $PO_TOTAL+=floatval($item->net_value); ?>
					<?php $PO_VAT+=floatval($item->net_value*(0.16)); ?>
				@endforeach
				@foreach ($extras as $extra)
					<?php $entity_currency_total = convert_currency($extra->cost, $extra->currency_id, $entity->currency) ?>
					<tr style="font-size: 12px;">
						<td colspan="4" style="padding-right: 2px; text-align:right"><b>{{ $extra->title }}</b></td>
						<td style="padding-right: 2px; text-align:right" nowrap>{{ $entity->print_price_on_po == "YES" ? number_format($entity_currency_total,2) : "" }}</td>
						<td style="padding-right: 2px; text-align:right">{{ $entity->print_price_on_po == "YES" ? getCurrencyById($entity->currency)->name : "" }}</td>
					</tr>
					<?php $PO_TOTAL+=floatval($entity_currency_total); ?>
				@endforeach
				<tr style="">
					<td style=" text-align: center; font-size: 9px; width: 6%;">&nbsp;</td>
					<td style=" width: 108%;" colspan="7">
						<p style="font-size: 11px">Delivery Schedule: {{ \Carbon\Carbon::parse($entity->due_date)->format('d.m.Y') }}</p>
					</td>
				</tr>
			</tbody>
    </table>
    <table style="width: 100%; border-collapse: collapse;">
      
      <tr>
        <td colspan="5" style="text-align: right; font-size: 12px;">
          PA/GF/02 Rev.04<br />
          Issued On: {{ \Carbon\Carbon::parse($entity->created_at)->format('d.m.Y') }}.
        </td>
      </tr>
      <tr>
        <th colspan="3" style="text-align: right;">Sub Total</th>
        <td colspan="2" style="text-align: right;">{{ $PO_TOTAL-$PO_VAT }}</td>
      </tr>
      <tr>
        <th colspan="3" style="text-align: right;">General Rate (16%)</th>
        <td colspan="2" style="text-align: right;">{{ $PO_VAT }}</td>
      </tr>
      <tr>
        <th colspan="3" style="text-align: right;">Total KES</th>
        <td colspan="2" style="text-align: right;">{{ $PO_TOTAL}}</td>
      </tr>
      <tr>
        <th colspan="3">Shipment preference</th>
        <td colspan="2">Deliver To Office</td>
      </tr>
    </table>
    <p style="margin-top: 20px;">Terms &amp; Conditions</p>
    <ul>
      <li>This purchase order is valid for 30 days from date of issue.</li>
      <li>Delivery Notes &amp; Invoices must accompany deliveries.</li>
      <li>Delivery accepted subject to count, weight and condition.</li>
      <li>This Purchase order must be quoted in the invoice.</li>
      <li>This purchase order is ONLY valid with official accepted signatures and official company stamp.</li>
    </ul>
    <p>Authorized Signature &amp; Stamp</p>
  </body>
</html>