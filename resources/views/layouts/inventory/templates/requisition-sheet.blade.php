<?php
	$getRFQ = \App\RequestEntity::where('parent_request_id', $entity->id)->first();
	$approver1 = \App\User::join('entity_approvals as ea', 'ea.user_id', 'users.id')
		->where('model', $entity->request_type)->where('model_id', $entity->id)->first();
	if($getRFQ){
		$approver2 = \App\User::join('entity_approvals as ea', 'ea.user_id', 'users.id')
		->where('model', $getRFQ->request_type)->where('model_id', $getRFQ->id)->first();
	}
	
?>
<div style="width: 99%; margin: auto">
	<table border="0" style="border-collapse: collapse; width: 100%; height: 84px; margin-bottom: 10px;">
		<tbody>
		<tr style="height: 21px;">
		<td colspan="3" style="width: 55.0001%; height: 21px; text-align: center;"><img src="https://aqualytic.imaralims.com/storage/companies/EixLy7nPubNy0uaGNFGlF4Ot9nzVHXpcDnMsEc0u.jpg" width="100" height="auto" alt="" /></td>
		</tr>
		<tr style="height: 35px;">
		<td colspan="2" style="width: 90.5001%; height: 21px; text-align: center;"><strong>REQUISITION SHEET</strong></td>
		<td style="width: 42.4999%; height: 21px;" nowrap><span><strong>No.</strong>
			<span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->request_code }}</span></span>
		</td>
		</tr>
		<tr style="height: 35px;">
		<td colspan="2" style="width: 90.5001%; height: 21px;">
			<span>Description of task for the purpose of materials or services <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->description }}</span></span>
		</td>
		<td style="width: 42.4999%; height: 21px;" nowrap><span>Date <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->created_at->toDateString() }}</span></span></td>
		</tr>
		<?php $theUser = \App\User::find($entity->request_initiator) ?? \Auth::user(); ?>
		<tr style="height: 35px;">
		<td style="width: 55.0001%; height: 21px;"><span>Nature of Expense <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $entity->nature_of_purchase }}</span></span></td>
		<td style="width: 35.5%; height: 21px;"><span>Unit Price <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ number_format(floatval($getRFQ->net_value ?? 0), 2) }}</span></span></td>
		<td style="width: 42.4999%; height: 21px;" nowrap><span>Cost Center <span style="border-bottom: 1px solid #000; padding: 0px 10px 5px">{{ $theUser->department()->name }}</span></span></td>
		</tr>
		</tbody>
		</table>
		<table border="1" style="border-collapse: collapse; width: 100%; margin-bottom: 10px;">
		<thead>
		<tr style="height: 21px;">
		<th style="height: 21px; text-align: center; font-size: 8px;">NO</th>
		<th style="height: 21px; text-align: center; font-size: 8px;">ITEM</th>
		<th style="height: 21px; text-align: center; font-size: 8px;">QUANTITY IN STOCK</th>
		<th style="height: 21px; text-align: center; font-size: 8px;">QUANTITY ORDERED</th>
		<th style="height: 21px; text-align: center; font-size: 8px;">QUANTITY APPROVED</th>
		<th style="height: 21px; text-align: center; font-size: 8px;">COMMENTS</th>
		<th style="height: 21px; text-align: center; font-size: 8px;">SUPPLIER 1</th>
		<th style="height: 21px; text-align: center; font-size: 8px;">SUPPLIER 2</th>
		<th style="height: 21px; text-align: center; font-size: 8px;">SUPPLIER 3</th>
		</tr>
		
		<tbody>
			<?php
				$allItems = isset($getRFQ) ? $getRFQ->items($getRFQ->ammendment) : array();

				$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

				// echo json_encode($getRFQ);
			?>
			@foreach ($normalItems as $item)
				<?php
					$getRItem = \App\RequestEntityItem::where('request_id', $entity->id)->where('inventory_sub_category_id', $item->inventory_sub_category_id)
					->where('item_brand_id', $item->item_brand_id)->first();

					$itemQuotes = [];

					foreach($getRFQ->quotes() as $quote){
						$itemQuotes[] = $quote;
					}
				?>
				<tr style="height: 21px; text-align: left; font-size: 12px">
					<td style="padding: 3px; height: 21px; text-align: center; font-size: 9px;">{{ $loop->iteration }}</td>
					<td style="padding: 3px; height: 21px;">{{ $item['item_name'] }}</td>
					<td style="padding: 3px; height: 21px; text-align:right">{{ number_format($item['available_stock'], 1) }} {{ $item['unit_type'] }}</td>
					<td style="padding: 3px; height: 21px; text-align:right">{{ number_format($getRItem->quantity, 1) }} {{ $item['unit_type'] }}</td>
					<td style="padding: 3px; height: 21px; text-align:right">{{ number_format($item['quantity'], 1) }} {{ $item['unit_type'] }}</td>
					<td style="padding: 3px; height: 21px;">{{ $item['comments'] }}</td>
					<td style="padding: 3px; height: 21px;">{!! '<span style="color: green">'.count($itemQuotes) >=1 ? ($itemQuotes[0]->is_awarded == 1 ? "✓" : "") : "" .'</span>' !!} {{ isset($itemQuotes[0]) ? $itemQuotes[0]->supplier->name : '' }} - {{ isset($itemQuotes[0]) ? number_format($itemQuotes[0]->quote_amount, 2) : '' }}</td>
					<td style="padding: 3px; height: 21px;">{!! '<span style="color: green">'.count($itemQuotes) >=2 ? ($itemQuotes[1]->is_awarded == 1 ? "✓" : "") : "" .'</span>' !!} {{ isset($itemQuotes[1]) ? $itemQuotes[1]->supplier->name : '' }} - {{ isset($itemQuotes[1]) ? number_format($itemQuotes[1]->quote_amount, 2) : '' }}</td>
					<td style="padding: 3px; height: 21px;">{!! '<span style="color: green">'.count($itemQuotes) >= 3 ? ($itemQuotes[2]->is_awarded == 1 ? "✓" : "") : "" .'</span>' !!} {{ isset($itemQuotes[2]) ? $itemQuotes[2]->supplier->name : '' }} - {{ isset($itemQuotes[2]) ? number_format($itemQuotes[2]->quote_amount, 2) : '' }}</td>
				</tr>
			@endforeach
		</tbody>
		</table>
		<table border="0" style="border-collapse: collapse; width: 100%; margin-bottom: 10px;">
		<thead>
		<tr style="height: 21px;">
		<th style="height: 21px; font-size: 9px; width: 33%; text-align: left;">Requested by</th>
		<th style="height: 21px; font-size: 9px; width: 33%; text-align: left;">Approved by</th>
		<th style="height: 21px; font-size: 9px; width: 34%; text-align: left;">Approved for PO</th>
		</tr>
		</thead>
		<tbody>
		<tr>
		<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Name <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $theUser->name }}</span></td>
		<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Name <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $approver1->name ?? '' }}</span></td>
		<td style="padding: 5px 10px; width: 33%; font-size: 12px;">Name <span style="border-bottom: 1px solid #000; padding: 5px 10px">{{ $approver2->name ?? '' }}</span></td>
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
		<td colspan="3" style="height: 21px; width: 33%; font-size: 9px; text-align: left;"><span>For each item requested, the approver must tick the acceptable unit price after reviewing the alternative quotations by suppliers.</span></td>
		</tr>
		<tr style="height: 21px;">
		<td colspan="3" style="height: 21px; width: 33%; font-size: 9px; text-align: left;"><span>For each The LPO willbe written to the suppliers whose unit price has been ticked</span></td>
		</tr>
		<tr style="height: 21px;">
		<td colspan="3" style="height: 21px; width: 33%; font-size: 9px; text-align: left;"><span>For store re-order, items requested must be supported by a material re-order analysis showing the current stock levels and the re-order quantities</span></td>
		</tr>
		</tbody>
	</table>
</div>
<script>
	window.onload = function(){
		window.print();
	}
</script>