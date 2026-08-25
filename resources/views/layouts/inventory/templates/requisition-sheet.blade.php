<?php
	$getRFQ = \App\RequestEntity::where('parent_request_id', $entity->id)->where('request_type', 'Request for Quotation')->first();
	$approver1 = \App\User::join('entity_approvals as ea', 'ea.user_id', 'users.id')->where('ea.status', 'Approved')
		->where('model', $entity->request_type)->where('model_id', $entity->id)->selectRaw('users.*, ea.updated_at as update_date')->first();
	if($getRFQ){
		$approver2 = \App\User::join('entity_approvals as ea', 'ea.user_id', 'users.id')->where('ea.status', 'Approved')
		->where('model', $getRFQ->request_type)->where('model_id', $getRFQ->id)->selectRaw('users.*, ea.updated_at as update_date')->first();
	}
	$currency = isset($getRFQ->currency) ? getCurrencyById($getRFQ->currency) : '';

	$PO_TOTAL = 0;
	$active = getActiveCompany();
?>
<style type="text/css">
	@media print{
		button{
				display:none;
		}
	}
</style>
@if(isset($isHTML) && !$isHTML)
	<div style="padding: 10px 15px">
		<button style="padding: 5px 10px; font-size: 13px" onclick="window.print()">Print</button>
	</div>
@endif
@if(isETCU())
<?php $theUser = \App\User::find($entity->request_initiator) ?? \Auth::user(); ?>
<table style="border-collapse: collapse; width: 100%; height: 84px; margin-bottom: 10px;" border="0">
	<tbody>
		<tr style="height: 21px;">
			<td style="width: 100%; text-align: center; font-size: 18px; font-weight: 700" colspan="3">ETHIOPIA CUTTINGS</td>
		</tr>
		<tr>
			<td style="width: 100%; text-align: center; font-size: 16px; font-weight: 500; padding-top: 20px" colspan="3">PURCHASE REQUISITION</td>
		</tr>
		<tr style="height: 21px;">
			<td style="width: 18%;">&nbsp;</td>
			<td style="text-align: center; width: 68%;"></td>
			<td style="text-align: right; width: 42%;">Date
				<span style="border-bottom: 1px solid #000; padding: 5px 10px; min-width: 150px">{{ \Carbon\Carbon::parse($entity->created_at)->format('Y-m-d') }}</span></span>
			</td>
		</tr>
		<tr style="height: 21px;">
			<td colspan="3">
				<table style="width: 100%;" border="0">
					<tr>
						<td style="width: 40%; height: 21px;">
							<span>DEPARTMENT: <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $theUser->department()->name }}</span></span> <br>
							<br>
							@if($entity->request_type == "Purchase Request")
								<span>PURPOSE: <span style="border-bottom: 1px solid #000; padding: 5px 10px; min-width: 150px">{{ $entity->description }}</span></span>
							@endif
						</td>
						<td style="width: 20%; height: 21px;"></td>
						<td style="width: 40%; text-align:right"><span>P.R NO
							<span style="border-bottom: 1px solid #000; padding: 5px 10px; min-width: 150px">{{ $entity->request_code }}</span></span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<br>
<table style="border-collapse: collapse; width: 100.167%; margin-bottom: 10px;" border="1">
	<thead>
		<tr style="font-size: 11px !important; ">
			<th style="height: 1%; text-align: right;  width: 3.83333%;">No</th>
			<th style="text-align: center; width: 24.1939%;">Item Description</th>
			<th style="text-align: center; width: 12.6645%;">Identification or Part No.</th>
			<th style="text-align: center; width: 10.3361%;">Unit of Measure</th>
			<th style="text-align: right; width: 12.1692%;">Quantity</th>
			<th style="text-align: right; width: 11.8361%;">Unit Price</th>
			<th style="text-align: right; width: 16.0101%;">Amount Birr</th>
			<th style="text-align: center; width: 23.9735%;">For Accounts use only</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$allItems = isset($getRFQ) ? $getRFQ->items($getRFQ->ammendment, true) : array();

			$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

			// echo json_encode($getRFQ);
		?>
		@foreach ($normalItems as $item)
			<?php
				if($getRFQ && isset($getRFQ->id)){
					$getRItem = \App\RequestEntityItem::where('request_id', $getRFQ->id)->where('inventory_sub_category_id', $item->inventory_sub_category_id)
					->where('item_brand_id', $item->item_brand_id)->first();
				}
				else{
					$getRItem = \App\RequestEntityItem::where('request_id', $entity->id)->where('inventory_sub_category_id', $item->inventory_sub_category_id)
					->where('item_brand_id', $item->item_brand_id)->first();
				}

				$itemQuotes = [];
				$in_suppliers = [];

				foreach($getRFQ->quotes($item['inventory_sub_category_id']) as $quote){
					$itemQuotes[] = $quote;
				}

				$isKitRow = filled($item->catalog_number)
					&& ! is_numeric($item->catalog_number)
					&& ! \Illuminate\Support\Str::isUuid((string) $item->catalog_number);

				// echo "<pre>".json_encode($getRFQ->quotes($item['inventory_sub_category_id']), JSON_PRETTY_PRINT)."</pre>";
			?>
			<tr style="font-size: 11px; ">
				<td style="text-align: center; width: 3.83333%;">{{ $loop->iteration }}</td>
				<td style="width: 24.1939%; font-size: 11px; padding: 5px">{{ strtoupper(strtolower($isKitRow ? $item['catalog_number'].' - '.$item['kit_item_name'] : $item['item_name'])) }}</td>
				<td style="width: 12.6645%;">{{ $isKitRow ? $item['catalog_number'] : $item['code'] }}</td>
				<td style="width: 10.3361%;">{{ $isKitRow ? '-' : $item['unit_type'] }}</td>
				<td style="width: 12.1692%; text-align: right;">{{ $isKitRow ? 1 : number_format($item['quantity'], 3) }}</td>
				<td style="width: 11.8361%; text-align: right;">{{ $isKitRow ? '-' : $item['price'] }}</td>
				<td style="width: 16.0101%; text-align: right;">{{ $isKitRow ? 1 : number_format(floatval($item['quantity'])*floatval($item['price']), 3) }}</td>
				<td style="width: 23.9735%;">&nbsp;</td>
			</tr>
		@endforeach
	</tbody>
</table>
<br/>
<table style="border-collapse: collapse; width: 100%; margin-bottom: 10px; font-size: 12px !important" border="0">
	<tbody>
		<tr style="height: 21px;" valign="top">
			<td style="width: 50%; font-size: 12px;">
				<table style="width:100%">
					<?php
						$approversRFQ = \App\User::join('entity_approvals as ea', 'ea.user_id', 'users.id')->where('ea.status', 'Approved')
							->where('model', $getRFQ->request_type)->where('model_id', $getRFQ->id)->selectRaw('users.*, ea.updated_at as update_date')
							->orderBy('ea.updated_at')->get();
					?>
					<tr>
						<td colspan="2">
							Prepared By <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%; font-size: 14px">{{ $approversRFQ[0]->name ?? '' }}</span>
						</td>
					</tr>
					<tr>
						<td>Sign <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%; font-size: 14px">{!! isset($approversRFQ[0]->electronic_sig) && trim($approversRFQ[0]->electronic_sig) != "" ?
							'<img src="'.$approversRFQ[0]->electronic_sig.'" style="height: 25px" />' : '' !!}</span></td>
						<td>Date <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%; font-size: 14px">{{ isset($approversRFQ[0]->update_date) ? \Carbon\Carbon::parse($approversRFQ[0]->update_date)->format('Y-m-d') : '' }}</span></td>
					</tr>
					<?php
						$rfq_approval_id = getConfigByName('rfq_approval_id');
						$rfq_approval_id = count($rfq_approval_id) > 0 ? $rfq_approval_id[0]->value : 0;

						$finalApproval = \App\User::join('entity_approvals as ea', 'users.id', 'ea.user_id')
							->where('ea.model_id', $entity->id)
							->where('ea.approval_id', $rfq_approval_id)->first();
					?>
					<tr style="padding-top: 20px">
						<td style="padding-top: 20px">Approved By
							<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%; font-size: 14px">{{ $finalApproval->name ?? '' }}</span>
						</td>
						<td style="padding-top: 20px">Title
							<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%; font-size: 14px">{{ $finalApproval->personnel_title()->name ?? '' }}</span>
						</td>
					</tr>
					<tr>
						<td>Sign
							<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%;">{!! isset($finalApproval->electronic_sig) && trim($finalApproval->electronic_sig) != "" ?
								'<img src="'.$finalApproval->electronic_sig.'" style="height: 25px" />' : '' !!}</span>
						</td>
						<td>Date
							<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%; font-size: 14px">{{ \Carbon\Carbon::parse($finalApproval->updated_at)->format('Y-m-d') ?? '' }}</span>
						</td>
					</tr>
				</table>
			</td>
			<td style="width: 50%; font-size: 12px;">
				<table style="width:100%">
					<tr>
						<td>Authorised By
							<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%; font-size: 14px">{{ isset($approversRFQ[1]->name) ? $approversRFQ[1]->name : '' }}</span>
						</td>
						<td>Title
							<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%; font-size: 14px">{{ isset($approversRFQ[1]->name) ? $approversRFQ[1]->personnel_title()->name : '' }}</span>
						</td>
					</tr>
					<tr>
						<td>Sign
							<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%;">{!! isset($approversRFQ[1]->electronic_sig) && trim($approversRFQ[1]->electronic_sig) != "" ?
								'<img src="'.$approversRFQ[1]->electronic_sig.'" style="height: 25px" />' : '' !!}</span>
						</td>
						<td>Date
							<span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%; font-size: 14px">{{ isset($approversRFQ[1]->name) ? \Carbon\Carbon::parse($approversRFQ[1]->update_date)->format('Y-m-d') : '' }}</span>
						</td>
					</tr>
					@if($entity->request_type != "Purchase Request")
						<tr>
							<td colspan="2" style="padding-top: 20px">Item Issued By ...................................</td>
						</tr>
						<tr>
							<td>Sign .............</td>
							<td>Date .............</td>
						</tr>
					@endif
				</table>
			</td>
		</tr>
		<tr style="height: 21px;">
			<td style="width: 33%; font-size: 10px; text-align: left;" colspan="3">Distribution: Original Store, 1<sup>st</sup> Copy Accountants, 2<sup>nd</sup> Copy Requisitioned </td>
		</tr>
	</tbody>
</table>
@else
	<div style="max-width: 99%; margin: auto; max-width: 1024px">
		<table border="0" style="border-collapse: collapse; width: 100%; height: 84px; margin-bottom: 10px;">
			<tbody>
				<tr style="height: 21px;">
				<td colspan="3" style="width: 100%; text-align: center; padding-bottom:20px"><img src="{{$active->logo}}" width="100" height="auto" alt="" /></td>
				</tr>
				<tr style="height: 35px;">
				<td colspan="2" style="width: 90.5001%; text-align: center; padding:20px 0px"><strong>REQUISITION SHEET</strong></td>
				<td style="width: 42.4999%; height: 21px;" nowrap><span><strong>No.</strong>
					<span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->request_code }}</span></span>
				</td>
				</tr>
				<tr style="height: 50px;">
				<td colspan="2" style="width: 90.5001%; height: 21px;">
					<span>Description of task for the purpose of materials or services <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->description }}</span></span>
				</td>
				<td style="width: 42.4999%; height: 21px;" nowrap><span>Date <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->created_at->toDateString() }}</span></span></td>
				</tr>
				<?php $theUser = \App\User::find($entity->request_initiator) ?? \Auth::user(); ?>
				<tr style="height: 35px;">
				<td style="width: 55.0001%; height: 21px;"><span>Nature of Expense <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->nature_of_purchase }}</span></span></td>
				<td style="width: 35.5%; height: 21px;"><span>Unit Price <span style="border-bottom: 1px solid #000; padding: 5px 10px"><span id="rfq-total"></span> {{ $currency->name ?? '' }}</span></span></td>
				<td style="width: 42.4999%; height: 21px;" nowrap><span>Cost Center <span style="border-bottom: 1px solid #000; padding: 0px 10px 5px">{{ trim($entity->cost_center) != "" ? $entity->cost_center : $theUser->department()->name }}</span></span></td>
				</tr>
			</tbody>
		</table>
		<table border="1" style="border-collapse: collapse; width: 100%; margin-bottom: 10px;">
			<thead>
				<tr style="height: 21px;">
					<th style="text-align: center; font-size: 8px;">NO</th>
					<th style="text-align: center; font-size: 8px;">ITEM</th>
					<th style="text-align: center; font-size: 8px;">QUANTITY IN STOCK</th>
					<th style="text-align: center; font-size: 8px;">QUANTITY ORDERED</th>
					<th style="text-align: center; font-size: 8px;">QUANTITY APPROVED</th>
					<th style="text-align: center; font-size: 8px;">COMMENTS</th>
					<th style="text-align: center; font-size: 8px;">SUPPLIER 1</th>
					<th style="text-align: center; font-size: 8px;">SUPPLIER 2</th>
					<th style="text-align: center; font-size: 8px;">SUPPLIER 3</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$allItems = isset($getRFQ) ? $getRFQ->items($getRFQ->ammendment, true) : array();

					$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

					// echo json_encode($getRFQ);
				?>
				@foreach ($normalItems as $item)
					<?php
						if($getRFQ && isset($getRFQ->id)){
							$getRItem = \App\RequestEntityItem::where('request_id', $getRFQ->id)->where('inventory_sub_category_id', $item->inventory_sub_category_id)
							->where('item_brand_id', $item->item_brand_id)->first();
						}
						else{
							$getRItem = \App\RequestEntityItem::where('request_id', $entity->id)->where('inventory_sub_category_id', $item->inventory_sub_category_id)
							->where('item_brand_id', $item->item_brand_id)->first();
						}

						$itemQuotes = [];
						$in_suppliers = [];

						foreach($getRFQ->quotes($item['inventory_sub_category_id']) as $quote){
							$itemQuotes[] = $quote;
						}

						$isKitRow = filled($item->catalog_number)
							&& ! is_numeric($item->catalog_number)
							&& ! \Illuminate\Support\Str::isUuid((string) $item->catalog_number);

						// echo "<pre>".json_encode($getRFQ->quotes($item['inventory_sub_category_id']), JSON_PRETTY_PRINT)."</pre>";
					?>
					<tr style="text-align: left; font-size: 12px">
						<td style="padding: 3px; text-align: center; font-size: 9px;">{{ $loop->iteration }}</td>
						<td style="padding: 3px; height: 21px;">{{ $isKitRow ? $item['catalog_number'].' - '.$item['kit_item_name'] : $item['item_name'] }}</td>
						<td style="padding: 3px; text-align:right">{{ $isKitRow ? 0 : number_format($item['available_stock'],3).$item['unit_type'] }}</td>
						<td style="padding: 3px; text-align:right">{{ $isKitRow ? 1 : number_format($getRItem->quantity,3).$item['unit_type'] }}</td>
						<td style="padding: 3px; text-align:right">{{ $isKitRow ? 1 : number_format($item['quantity'],3).$item['unit_type'] }}</td>
						<td style="padding: 3px; height: 21px;">{{ $isKitRow ? 'In kit item description' : $item['comments'] }}</td>
						<td style="padding: 3px; height: 21px;">
						@if(isset($itemQuotes[0]) && !in_array($itemQuotes[0]->supplier->id, $in_suppliers))
						<?php
							$itemTotal = isset($itemQuotes[0]) ? $itemQuotes[0]->quote_amount : 0;

							if($itemQuotes[0]->is_awarded){
								$PO_TOTAL+= $itemTotal;
							}
						?>
						{!! '<strong style="color: green; font-size: 1em">'.($itemQuotes[0]->is_awarded == 1 ? "✓" : "").'</strong>' !!} {{ isset($itemQuotes[0]) ? $itemQuotes[0]->supplier->name : '' }} - {{ $itemTotal > 0 ? number_format($itemTotal, 2) : '' }}
						<?php
							$in_suppliers[] = $itemQuotes[0]->supplier->id;
						?>
						@endif

						</td>
						<td style="padding: 3px; height: 21px;">
						@if(isset($itemQuotes[1]) && !in_array($itemQuotes[1]->supplier->id, $in_suppliers))
						<?php
							$itemTotal = isset($itemQuotes[1]) ? $itemQuotes[1]->quote_amount : 0;
							if($itemQuotes[1]->is_awarded){
								$PO_TOTAL+=$itemTotal;
							}
						?>
						{!! '<strong style="color: green; font-size: 1em">'.($itemQuotes[1]->is_awarded == '1' ? "✓" : "").'</strong>' !!} {{ isset($itemQuotes[1]) ? $itemQuotes[1]->supplier->name : '' }} - {{ $itemTotal > 0 ? number_format($itemTotal, 2) : '' }}

						<?php
							$in_suppliers[] = $itemQuotes[1]->supplier->id;
						?>

						@endif

						</td>
						<td style="padding: 3px; height: 21px;">

						@if(isset($itemQuotes[2]) && !in_array($itemQuotes[2]->supplier->id, $in_suppliers))
						<?php
							$itemTotal = isset($itemQuotes[2]) ? $itemQuotes[2]->quote_amount : 0;

							if($itemQuotes[2]->is_awarded){
								$PO_TOTAL+=$itemTotal;
							}
						?>
						{!! '<strong style="color: green; font-size: 1em">'.($itemQuotes[2]->is_awarded == '1' ? "✓" : "").'</strong>' !!} {{ isset($itemQuotes[2]) ? $itemQuotes[2]->supplier->name : '' }} - {{ $itemTotal > 0 ? number_format($itemTotal, 2) : '' }}
						@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		@if(isset($isHTML) && !$isHTML)
		<table border="0" style="border-collapse: collapse; width: 100%; margin-bottom: 10px;">
			<thead>
			<tr style="height: 21px;">
			<th style="font-size: 9px; width: 33%; text-align: left;">Requested by</th>
			<th style="font-size: 9px; width: 33%; text-align: left;">Approved by</th>
			<th style="font-size: 9px; width: 34%; text-align: left;">Approved for PO</th>
			</tr>
			</thead>
			<tbody>
			<tr>
			<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Name <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $theUser->name }}</span></td>
			<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Name <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ isset($approver1) ? $approver1->name : '' }}</span></td>
			<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Name <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ isset($approver2) ? $approver2->name : '' }}</span></td>
			</tr>
			<tr>
			<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Signature <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">{!! isset($theUser->electronic_sig) && trim($theUser->electronic_sig) != "" ?
			'<img src="'.$theUser->electronic_sig.'" style="height: 25px" />' : '' !!}</span></td>
			<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Signature <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">{!! isset($approver1->electronic_sig) && trim($approver1->electronic_sig) != "" ?
			'<img src="'.$approver1->electronic_sig.'" style="height: 25px" />' : '' !!}</span></td>
			<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Signature <span style="border-bottom: 1px solid #000; padding: 5px 10px; width: 100%">{!! isset($approver2->electronic_sig) && trim($approver2->electronic_sig) != "" ?
			'<img src="'.$approver2->electronic_sig.'" style="height: 25px" />' : '' !!}</span></td>
			</tr>
			<tr>
			<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Department <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $theUser->department()->name ?? '' }}</span></td>
			<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Department <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ isset($approver1) ? $approver1->department()->name : '' }}</span></td>
			<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Department <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ isset($approver2) ? $approver2->department()->name : '' }}</span></td>
			</tr>
			<tr style="height: 21px;">
			<td colspan="3" style="width: 33%; font-size: 9px; text-align: left;"><span>For each item requested, the approver must tick the acceptable unit price after reviewing the alternative quotations by suppliers.</span></td>
			</tr>
			<tr style="height: 21px;">
			<td colspan="3" style="width: 33%; font-size: 9px; text-align: left;"><span>For each The LPO willbe written to the suppliers whose unit price has been ticked</span></td>
			</tr>
			<tr style="height: 21px;">
			<td colspan="3" style="width: 33%; font-size: 9px; text-align: left;"><span>For store re-order, items requested must be supported by a material re-order analysis showing the current stock levels and the re-order quantities</span></td>
			</tr>
			</tbody>
		</table>
		@endif
	@endif
	<div style="text-align:center; padding: 15px; font-size: 12px">
		Nature of Purchase : {{ $entity->nature_of_purchase }}
		@if($entity->nature_of_purchase == "Capex")
			- <small>{{ $entity->capex_project_number }}</small>
		@endif
	</div>
	<script >

		function number_format (number, decimals, dec_point, thousands_sep) {
			// Strip all characters but numerical ones.
			number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
			var n = !isFinite(+number) ? 0 : +number,
					prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
					sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
					dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
					s = '',
					toFixedFix = function (n, prec) {
							var k = Math.pow(10, prec);
							return '' + Math.round(n * k) / k;
					};
			// Fix for IE parseFloat(0.55).toFixed(0) = 0;
			s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
			if (s[0].length > 3) {
					s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
			}
			if ((s[1] || '').length < prec) {
					s[1] = s[1] || '';
					s[1] += new Array(prec - s[1].length + 1).join('0');
			}
			return s.join(dec);
		}

		document.getElementById('rfq-total').innerHTML = number_format({{ $PO_TOTAL }});

		window.onload = window.print();
	</script>
</div>