<?php
	$supplierDetails = \App\Supplier::find($entity->supplier_id);
	$extras = \Illuminate\Support\Facades\Schema::hasTable('request_entity_extra_charges')
		? \App\RequestEntityExtraCharge::join('module_pre_configs as mpc', function ($join) {
			$join->whereRaw('mpc.id::text = request_entity_extra_charges.currency_id::text');
		})
			->selectRaw('request_entity_extra_charges.*, mpc.name as currency')
			->where('request_id', $entity->id)
			->get()
		: collect();
?>
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
@if(!isKECU())
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
	<div style="width: {{ isset($isInternal) ? '595px' : '595px' }}; margin:auto; font-family: Tahoma">
		<table style="border-collapse: collapse; width: 100%; height: 84px; margin-bottom: 10px;" border="0">
			<tbody>
				<tr style="">
					<td style="width: 97%; " colspan="2">
						<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
							<tr>
								<td>
									Polucon Services (K) Ltd<br />
									P.O Box 99344-80107.<br />
									Mombasa, Kenya.<br />
									Tel:0722229944 / 041 4470775.<br />
									Email: procurement@polucon.com.<br />
									Polucon House, Nyati Road, Off Links Road- Nyali.<br />
									PIN: P051097402V
								</td>
								<td style="text-align: right;">
									Purchase Order<br />
									LPO NO: 6718<br /><br />
									Vendor Address<br />
									NUVEMITE TECHNOLIGIES LTD<br />
									PIN P051745007D
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr><td style="padding: 20px"></td></tr>
				<tr style="">
					<td style="width: 50%; ">
						<table style="border-collapse: collapse; width: 100%;" border="0">
							<tbody>
								<tr>
									<td style="width: 100%; font-size: 12px"><strong>Vendor number:</strong> {{ "S".str_pad($supplierDetails->id, 4,"0", STR_PAD_LEFT) }}</td>
								</tr>
								<tr style="">
									<td style="width: 100%; font-size: 12px">{{ strtoupper($supplierDetails->name) }}</td>
								</tr>
								<tr>
									<td style="width: 100%; font-size: 12px">{{ $supplierDetails->address }}</td>
								</tr>
								<tr>
									<td style="font-size: 12px">
										<?php
											$plocs = [$supplierDetails->building ?? false, $supplierDetails->building ?? false, $supplierDetails->street ?? false, $supplierDetails->town ?? false];
											$plocs = array_unique($plocs);
										?>
										{!! implode(', <br>', $plocs) !!}
									</td>
								</tr>
								<tr>
									<td style="font-size: 12px">Fax: 202400</td>
								</tr>
							</tbody>
						</table>
					</td>
					<?php
						$REQUESTER = \App\User::find($entity->request_initiator);
					?>
					<td style="width: 50%">
						<table style="border-collapse: collapse; width: 96.25%;" border="0">
							<tbody>
								<tr>
									<td style="text-align: left; font-size: 12px" colspan="2"><strong>Contact details</strong></td>
								</tr>
								<tr style="">
									<td style="font-size: 12px" colspan="2">Peter Mwaura</td>
								</tr>
								<tr>
									<td style="font-size: 12px">Tel:  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 0707 281 400 / 0709 281 000</td>
								</tr>
								<tr>
									<td style="font-size: 12px">Fax:   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; +254 020 352523</td>
								</tr>
								<tr>
									<td style="font-size: 12px">Email: &nbsp;&nbsp;peter.mwaura@syngenta.com</td>
								</tr>
								<tr>
									<td style="font-size: 12px" colspan="2">Requisitioner: &nbsp;&nbsp;&nbsp;{{ $REQUESTER->name }}</td>
								</tr>
								<tr>
									<td style="font-size: 12px" colspan="2">Cost Center: &nbsp;&nbsp;&nbsp;{{ $entity->cost_center }}</td>
								</tr>
								<tr>
									<td style="font-size: 12px" colspan="2">
										Nature of Purchase : &nbsp;&nbsp;&nbsp;{{ $entity->nature_of_purchase }}
										@if($entity->nature_of_purchase == "Capex")
											- <span>{{ $entity->capex_project_number }}</span>
										@endif
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr style="">
					<td style="width: 48.4777%;" colspan="2">&nbsp;</td>
				</tr>
				<tr style="">
					<td style="width: 48.4777%;" colspan="2">&nbsp;</td>
				</tr>
				<tr style="">
					<td style="width: 48.4777%; ">
						<table style="width: 100%; border-collapse: collapse;">
							<tr>
								<th colspan="5" style="text-align: left;">
									<img src="www.polucon.com" alt="Logo" style="max-width: 200px;" />
								</th>
							</tr>
							<tr>
								<td colspan="5" style="text-align: right; font-size: 12px;">
									PA/GF/02 Rev.04<br />
									Issued On: 11 Jul 2023.
								</td>
							</tr>
							<tr>
								<th colspan="3" style="text-align: right;">Sub Total</th>
								<td colspan="2" style="text-align: right;">142,800.00</td>
							</tr>
							<tr>
								<th colspan="3" style="text-align: right;">General Rate (16%)</th>
								<td colspan="2" style="text-align: right;">22,848.00</td>
							</tr>
							<tr>
								<th colspan="3" style="text-align: right;">Total KES</th>
								<td colspan="2" style="text-align: right;">165,648.00</td>
							</tr>
							<tr>
								<th colspan="3">Shipment preference</th>
								<td colspan="2">Deliver To Office</td>
							</tr>
							<tr>
								<th>Date</th>
								<td>11 Jul 2023</td>
								<th>Terms</th>
								<td>Net 30</td>
								<th>Quotation Ref#</th>
								<td>765</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr style="">
					<td style="width: 48.4777%;">&nbsp;</td>
				</tr>
				<tr style="">
					<td style="width: 50.4777%;" colspan="">
						<p style="font-size: 11px">Deviation from requested delivery time or volume must be indicated within 2 days from receipt of this order.<br>
							Payment terms: within 30 days Due net
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<table style="border-collapse: collapse; width: 100%; margin-bottom: 10px; height: 147px;">
			<thead>
				<tr style="">
					<th style=" text-align: center; font-size: 12px; width: 6%;">Item</th>
					<th style=" text-align: center; font-size: 12px; width: 10%;">Material</th>
					<th style=" text-align: center; font-size: 12px; width: 28%;">Description</th>
					<th style=" text-align: right; font-size: 12px; width: 12%;">Order Qty.</th>
					<th style=" text-align: center; font-size: 12px; width: 10%;">Unit</th>
					<th style=" text-align: right; font-size: 12px; width: 10%;">Price/Unit</th>
					<th style=" text-align: right; font-size: 12px; width: 14%;">Net Value</th>
					<th style=" text-align: right; font-size: 12px; width: 24%;">Curr.</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$allItems = $entity->items($entity->ammendment, true) ?? array();

					$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

					// echo json_encode($getRFQ);
					$PO_TOTAL = 0;
				?>
				@foreach ($normalItems as $item)
				<?php
					$isKitRow = filled($item->catalog_number)
						&& ! is_numeric($item->catalog_number)
						&& ! \Illuminate\Support\Str::isUuid((string) $item->catalog_number);
				?>
					<tr style="font-size: 11px">
						<td style=" text-align: center; font-size: 9px; width: 6%;">{{ $loop->iteration }}</td>
						<td style=" width: 10%;">{{ $isKitRow ? $item->catalog_number : $item->code }}</td>
						<td style=" font-size: 9px; width: 28%;">
							@if($isKitRow)
								<strong>{{ $item->kit_item_name }}</strong>
							@else
								<strong>{{ $item->item_name }} / {{ $item->code }}</strong><br>
								{{ $item->comments }}
							@endif
						</td>
						<td style=" text-align:right">{{ $isKitRow ? 1 : number_format($item->quantity, 3) }}</td>
						<td style=" width: 10%;">{{ $isKitRow ? '-' : $item->unit_type }}</td>
						<td style=" text-align:right">{{ $isKitRow ? '1' : number_format($item->net_value/$item->quantity,2) }}</td>
						<td style=" text-align:right">{{ number_format($item->net_value, 2) }}</td>
						<td style=" text-align:right">{{ getCurrencyById($item->currency)->name ?? '' }}</td>
					</tr>
					<?php $PO_TOTAL+=floatval($item->net_value); ?>
				@endforeach
				@foreach ($extras as $extra)
					<?php $entity_currency_total = convert_currency($extra->cost, $extra->currency_id, $entity->currency) ?>
					<tr style="font-size: 12px;">
						<td colspan="6" style="padding-right: 2px; text-align:right"><b>{{ $extra->title }}</b></td>
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
				<tr style="border-top: 1px solid; border-bottom: 1px solid;">
					<td style="padding-bottom: 10px; border-top: 1px solid; border-bottom: 1px solid; text-align: center; font-size: 9px; width: 6%;">&nbsp;</td>
					<td style="padding-bottom: 10px; border-top: 1px solid; border-bottom: 1px solid; text-align: center; font-size: 12px;width: 70%; font-weight: 600;" colspan="5">Net value incl. disc.</td>
					<td style="padding-bottom: 10px; border-top: 1px solid; border-bottom: 1px solid; width: 14%; font-size: 12px; text-align: right;">{{ number_format($PO_TOTAL,2) }}</td>
					<td style="padding-bottom: 10px; border-top: 1px solid; border-bottom: 1px solid; font-size: 12px;width: 24%; text-align: right;">{{ getCurrencyById($entity->currency)->name ?? '' }}</td>
				</tr>
			</tbody>
		</table>
		@if(!$isHTML)
			<?php
				$approvers = \App\Approvals::leftJoin('entity_approvals as ea', function($join) use($entity){
					$join->on('ea.approval_id', 'approvals.id');
					// $join->where('ea.model', $entity->request_type);
					$join->where('ea.model_id', $entity->id);
				})->leftJoin('users as u', 'u.id', 'ea.user_id')->where('ea.status', 'Approved')
				->where('approvals.stage', $entity->request_type)->orderBy('approvals.id', 'asc')->get()->toArray();

				// $approvers = \App\User::join('entity_approvals as ea', 'ea.user_id', 'users.id')->where('ea.status', 'Approved')
				// ->where('model', $entity->request_type)->where('model_id', $entity->id)->get()->toArray();
				// echo '<pre>'.json_encode($approvers, JSON_PRETTY_PRINT).'</pre>';

				$approvalCounts = $entity->defined_approvals()->count();
				$startKeySet = 0
			?>
			<table style="font-size: 12px; border-collapse: collapse; width: 100%;" border="0">
				<tbody>
					<tr>
						<td valign="top">
							<p>Requisition By:</p>
							<p>
								{!! trim($REQUESTER->electronic_sig) != "" ?
								'<img src="'.imageTobase64($REQUESTER->electronic_sig).'" style="margin-left: 10px; height: 25px" />' : $REQUESTER->name !!}
							</p>
						</td>
						<td>
							<p>Dept Head Signature:</p>
							<p>
								@if(isset($approvers[$startKeySet]) && (isset($approvers[$startKeySet]['approved_at']) && trim($approvers[$startKeySet]['approved_at']) != ""))
									{{ $approvers[$startKeySet]['name'] }}<br><br>
									<span style="padding: 5px 10px; width: 100%">{!! trim($approvers[$startKeySet]['electronic_sig']) != "" ?
										'<img src="'.imageTobase64($approvers[$startKeySet]['electronic_sig']).'" style="margin-left: 10px; height: 25px" />' : $approvers[$startKeySet]['name'] !!}</span>
									</p>
								@endif
							</p>
						</td>
						<?php $startKeySet = $approvalCounts == 2 ? 0 : 1 ?>
						<td>
							<p>1st Authorised Signature:</p>
							<p>
								@if(isset($approvers[$startKeySet]) && (isset($approvers[$startKeySet]['approved_at']) && trim($approvers[$startKeySet]['approved_at']) != ""))
									{{ $approvers[$startKeySet]['name'] }}<br><br>
									<span style="padding: 5px 10px; width: 100%">{!! trim($approvers[$startKeySet]['electronic_sig']) != "" ?
										'<img src="'.imageTobase64($approvers[$startKeySet]['electronic_sig']).'" style="margin-left: 10px; height: 25px" />' : $approvers[$startKeySet]['name'] !!}
									</span>
								@endif
							</p>
						</td>
					</tr>
					<tr>
						<td>
							<br><br>
							<?php $preparedBy = \App\User::find($entity->created_by); ?>
							<p>Prepared By:</p>
							<p>
								{{ $preparedBy->name }}<br>
								{!! trim($preparedBy->electronic_sig) != "" ? '<img src="'.imageTobase64($preparedBy->electronic_sig).'" style="margin-left: 10px; height: 25px" />' : $preparedBy->name !!}
							</p>
						</td>
						<td>
							<p>&nbsp;</p>
						</td>
						<?php $startKeySet = $startKeySet + 1 ?>
						<td>
							<br><br>
							<p>2nd Authorised Signature:</p>
							<p>
								@if(isset($approvers[$startKeySet]) && (isset($approvers[$startKeySet]['approved_at']) && trim($approvers[$startKeySet]['approved_at']) != ""))
								{{ $approvers[$startKeySet]['name'] }}<br><br>
									<span style="padding: 5px 10px; width: 100%">{!! trim($approvers[$startKeySet]['electronic_sig']) != "" ?
										'<img src="'.imageTobase64($approvers[$startKeySet]['electronic_sig']).'" style="margin-left: 10px; height: 25px" />' : $approvers[$startKeySet]['name'] !!}
									</span>
								@endif
							</p>
						</td>
					</tr>
					<tr>
						<td style="width: 98%;" colspan="3">
							<p style="font-size: 9px; text-align: left;"><strong>Standard Syngenta Terms and Condition apply. To view please go to <a href="https://www.syngenta.com/sites/syngenta/files/seeds/supplier-information-for-new-products/pdf/Kenya-Ts-and-Cs-2018-1.pdf">https://www.syngenta.com/sites/syngenta/files/seeds/supplier-information-for-new-products/pdf/Kenya-Ts-and-Cs-2018-1.pdf</a></strong></p>
						</td>
					</tr>
				</tbody>
			</table>
		@endif
	</div>
@else
<div style="width: {{ isset($isInternal) ? '1024px' : '595px' }}; margin:auto">
	@if(!$isHTML)
		<div style="padding: 10px 15px; display: none" id="print-button">
			<button style="padding: 5px 10px; font-size: 13px" onclick="window.print()">Print</button>
		</div>
	@endif
	<table border="0" style="border-collapse: collapse; width: 100%; margin-bottom: 10px;">
		<tbody>
			<tr style="padding: 4px 2px;">
				<td style="width: 61.8334%; padding: 4px 2px;">
					<img src="{{ imageTobase64('/storage/companies/9RUZQlhlqNlYp1icQFROCRLgLTtvqRrTtcXyms2g.png') }}" style="max-height: 45px" />
				</td>
				<td style="width: 38.1666%; padding: 4px 2px; text-align: right;">
					<p style=" font-size: 12px;">{!! getConfigByName('site_po_box')->count() > 0 ? getConfigByName('site_po_box')[0]->value : 'P.O. BOX 27774 - 0056 Nairobi' !!}<br /><span>Tel: {{ getConfigByName('po_contact_telephone')->count() > 0 ? getConfigByName('po_contact_telephone')[0]->value : '' }}</span><br /><span>Fax: {{ getConfigByName('po_contact_fax')->count() > 0 ? getConfigByName('po_contact_fax')[0]->value : '' }}</span><span></span></p>
				</td>
			</tr>
			<tr style="padding: 4px 2px;">
				<td colspasn="2"></td>
			</tr>
			</tbody>
	</table>
	<div style="clear: both !important;">
		<div style="float: left; overflow:hidden; width: 60%">
			<table style="border-collapse: collapse; width: 90%; border: 1px solid black;">
				<tbody>
					<tr>
						<td colspan="2" style="border: 1px solid black; text-align: center; font-size: 12px;">VENDOR DETAILS</td>
					</tr>
					<tr style="padding: 4px 2px;">
						<td style="border: 1px solid black; font-size: 12px;">Syngenta Vendor Number</td>
						<td style="border: 1px solid black; font-size: 12px;">{{ "S".str_pad($supplierDetails->id, 4,"0", STR_PAD_LEFT) }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black; font-size: 12px;">Name</td>
						<td style="border: 1px solid black; font-size: 12px;" nowrap>{{ $supplierDetails->name }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black; font-size: 12px;">Postal Address</td>
						<td style="border: 1px solid black; font-size: 12px;" nowrap>P.O. Box {{ $supplierDetails->address }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black; font-size: 12px; padding: 4px 2px">Physical Location</td>
						<td style="border: 1px solid black; font-size: 12px; padding: 4px 2px" nowrap>
							<?php
								$plocs = [$supplierDetails->building ?? false, $supplierDetails->building ?? false, $supplierDetails->street ?? false, $supplierDetails->town ?? false];
								$plocs = array_unique($plocs);
							?>
							{{ implode(',', $plocs) }}
						</td>
					</tr>
					<tr>
						<td style="border: 1px solid black; font-size: 12px;">Telephone</td>
						<td style="border: 1px solid black; font-size: 12px;">{{ $supplierDetails->phone }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black; font-size: 12px;">Fax</td>
						<td style="border: 1px solid black; font-size: 12px;">-</td>
					</tr>
					<tr>
						<td style="border: 1px solid black; font-size: 12px;">Email</td>
						<td style="border: 1px solid black; font-size: 12px;">{{ implode(', ', explode(';', $supplierDetails->email)) }}</td>
					</tr>
				</tbody>
			</table>
			<br>
			<table style="border-collapse: collapse; width: 90%; border: 1px solid black;">
				<tbody>
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black; text-align: center; font-size: 12px;">DELIVERY INSTRUCTIONS</td>
					</tr>
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black; text-align: center; font-size: 12px;">Delivery Address</td>
					</tr>
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black; font-size: 12px;">{!! getConfigByName('site_po_box')->count() > 0 ? getConfigByName('site_po_box')[0]->value : 'P.O. BOX 27774 - 0056 Nairobi' !!}</td>
					</tr>
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black; font-size: 11px; text-align: center;">DELIVERY TO:</td>
					</tr>
					<tr style="padding: 4px 2px;">
						<?php
							$REQUESTER = \App\User::find($entity->request_initiator);
						?>
						<td style="border: 1px solid black; font-size: 12px;">Receivers Name</td>
						<td style="border: 1px solid black; font-size: 12px;">{{ getConfigByName('po_receivers_name')->count() > 0 ? getConfigByName('po_receivers_name')[0]->value : '' ?? $REQUESTER->name }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black; font-size: 12px;">Room Number</td>
						<td style="border: 1px solid black; font-size: 12px;">-</td>
					</tr>
					<tr>
						<td style="border: 1px solid black; font-size: 12px;">Department</td>
						<td style="border: 1px solid black; font-size: 12px;">{{ $REQUESTER->department()->name }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black; font-size: 12px;">Other Delivery Information</td>
						<td style="border: 1px solid black; font-size: 12px;">0</td>
					</tr>
					</tbody>
			</table>
		</div>
		<div style="float: right; overflow: hidden; width: 40%; align-items: right">
			<table style="width: 100%; border-spacing: 0px;">
				<tbody>
					<tr>
						<td colspan="2" style="border: 1px solid black !important; border-bottom: none !important; text-align: center; font-size: 12px;">PURCHASE ORDER</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important; font-size: 12px;">PO Number</td>
						<td style="border: 1px solid black !important; border-bottom: none !important; font-size: 12px;">{{ $entity->request_code }} {{ $entity->ammendment == 1 ? '' : 'v'.$entity->ammendment }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important;  font-size: 12px;">PO Date</td>
						<?php $time = strtotime($entity->created_at); ?>
						<td style="border: 1px solid black !important; border-bottom: none !important;  font-size: 12px;">{{ date('d \of F, Y', $time) }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important; font-size: 12px;">Contact</td>
						<td style="border: 1px solid black !important; border-bottom: none !important;  font-size: 12px;">
						{{ getConfigByName('po_contact_name')->count() > 0 ? getConfigByName('po_contact_name')[0]->value : '' }}
						</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important;  font-size: 12px;">Telephone</td>
						<td style="border: 1px solid black !important; border-bottom: none !important;  font-size: 12px;">
						{{ getConfigByName('po_contact_telephone')->count() > 0 ? getConfigByName('po_contact_telephone')[0]->value : '' }}
						</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important; font-size: 12px;">Email</td>
						<td style="border: 1px solid black !important; border-bottom: none !important; font-size: 12px;">
						{{ getConfigByName('po_contact_email')->count() > 0 ? getConfigByName('po_contact_email')[0]->value : '' }}
						</td>
					</tr>
					<tr>
						<td style="border: 1px solid black; border-right: none !important; font-size: 12px;">Fax</td>
						<td style="border: 1px solid black;  font-size: 12px;">-</td>
					</tr>
				</tbody>
			</table>
			<br>
			<table style="width: 100%; border-spacing: 0px;">
				<tbody>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important; font-size: 12px;">Contact 2</td>
						<td style="border: 1px solid black !important; border-bottom: none !important; font-size: 12px;">{{ getConfigByName('po_contact2_name')->count() > 0 ? getConfigByName('po_contact2_name')[0]->value : '' }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important; font-size: 12px;">Telephone</td>
						<td style="border: 1px solid black !important;  border-bottom: none !important; font-size: 12px;">{{ getConfigByName('po_contact2_telephone')->count() > 0 ? getConfigByName('po_contact2_telephone')[0]->value : '' }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important; font-size: 12px;">Email</td>
						<td style="border: 1px solid black !important; border-bottom: none !important; font-size: 12px;">{{ getConfigByName('po_contact2_email')->count() > 0 ? getConfigByName('po_contact2_email')[0]->value : '' }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; font-size: 12px;">Fax</td>
						<td style="border: 1px solid black !important; font-size: 12px;">{{ getConfigByName('po_contact_fax')->count() > 0 ? getConfigByName('po_contact_fax')[0]->value : '' }}</td>
					</tr>
				</tbody>
			</table>
			<br>
			<table style="width: 100%; border-spacing: 0px;">
				<tbody>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important; font-size: 12px;">VAT Number</td>
						<td style="border: 1px solid black !important; border-bottom: none !important; font-size: 12px;">{{ $supplierDetails->vat_number }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important; font-size: 12px;">PIN Number</td>
						<td style="border: 1px solid black !important; border-bottom: none !important; font-size: 12px;">{{ $supplierDetails->pin_number }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important; font-size: 12px;">Payment terms</td>
						<td style="border: 1px solid black !important;  border-bottom: none !important; font-size: 12px;">{{ $supplierDetails->payment_terms }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important; font-size: 12px;">INCO Terms</td>
						<td style="border: 1px solid black !important; border-bottom: none !important; font-size: 12px;">{{ $supplierDetails->payment_method }}</td>
					</tr>
					<tr>
						<td style="border: 1px solid black !important; border-right: none !important; border-bottom: none !important; font-size: 12px;">Required Delivery Date</td>
						<td style="border: 1px solid black !important; border-bottom: none !important; font-size: 12px;">{{ $entity->due_date }}</td>
					</tr>
					<tr>
						<td colspan="2" style="border: 1px solid black !important; border-bottom: none !important; font-size: 12px; text-align: center;">PLEASE SEND THE INVOICE TO:</td>
					</tr>
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black !important; border-bottom: none !important; font-size: 11px;">{{ getConfigByName('site_name')->count() > 0 ? getConfigByName('site_name')[0]->value : 'Kenya Pollen' }}</td>
					</tr>
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black !important; border-bottom: none !important; font-size: 11px;">P.O. Box 2777 - 0056</td>
					</tr>
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black !important; font-size: 11px;">Nairobi</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div style="clear: both; padding: 4px 2px;">
		<p style="font-size: 12px; text-align: center;"><strong>Syngenta Purchase Order must appear on all packages, invoices, shipping papers and correspondence.</strong></p>
	</div>
	<div style="padding: 4px 2px;">
		<p style="font-size: 11px; text-align: right;">CURRENCY: {{ getCurrencyById($entity->currency)->name }}</p>
	</div>
	<table border="1" style="border-collapse: collapse; width: 100%; margin-bottom: 10px;">
		<thead>
		<tr style="padding: 4px 2px;">
		<th style="padding: 4px 2px; text-align: center;  font-size: 12px; width: 3.83333%;">Item</th>
		<th style="padding: 4px 2px; text-align: center;  font-size: 12px; width: 6.66667%;">Quantity</th>
		<th style="padding: 4px 2px; text-align: center;  font-size: 12px; width: 47%;">Item Code / Description</th>
		<th style="padding: 4px 2px; text-align: center;  font-size: 12px; width: 15.1667%;">Supplier Ref Number (catalogue ref if any)</th>
		<th style="padding: 4px 2px; text-align: center;  font-size: 12px; width: 14.5%;">Net Price per item (each) WITHOUT VAT</th>
		<th style="padding: 4px 2px; text-align: center;  font-size: 12px; width: 12.6666%;">Total Net Cost of items</th>
		</tr>
		</thead>
		<tbody>
		<?php
			$allItems = $entity->items($entity->ammendment, true) ?? array();

			$normalItems = empty($allItems) ? array() : ($allItems['normal'] ?? array());

			// echo json_encode($getRFQ);
			$PO_TOTAL = 0;
		?>
		@foreach ($normalItems as $item)
		<?php
			$isKitRow = filled($item->catalog_number)
				&& ! is_numeric($item->catalog_number)
				&& ! \Illuminate\Support\Str::isUuid((string) $item->catalog_number);
		?>
		<tr style="padding: 4px 2px; font-size: 12px;">
			<td style="padding-right: 2px; text-align: center; width: 3.83333%;">{{ $loop->iteration }}</td>
			<td style="padding-right: 2px; width: 6.66667%; text-align: right">{{ $isKitRow ? 1 : number_format($item->quantity, 3).$item->unit_type }}</td>
			<td style="padding-right: 2px; width: 47%; text-align: center">
				@if($isKitRow)
					<strong>{{ $item->kit_item_name }}</strong>
				@else
					<strong>{{ $item->item_name }} / {{ $item->code }}</strong><br>
					{{ $item->comments }}
				@endif
			</td>
			<td style="padding-right: 2px; width: 15.1667%; text-align: center">{{ $isKitRow ? $item->catalog_number : '' }}</td>
			<td style="padding-right: 2px; width: 14.5%; text-align: right">{{ $entity->print_price_on_po == "YES" ? ($isKitRow ? '1' : number_format($item->quantity > 0 ? $item->net_value/$item->quantity : 0,2)) : "" }}</td>
			<td style="padding-right: 2px; width: 12.6666%;  text-align: right">{{ $entity->print_price_on_po == "YES" ? number_format($item->net_value, 2) : "" }}</td>
		</tr>
		<?php $PO_TOTAL+=floatval($item->net_value); ?>
		@endforeach

		@foreach ($extras as $extra)
			<?php $entity_currency_total = convert_currency($extra->cost, $extra->currency_id, $entity->currency) ?>
			<tr style="font-size: 12px;">
				<td colspan="3" style="padding-right: 2px; text-align:right"><b>{{ $extra->title }}</b></td>
				<td colspan="2" style="padding-right: 2px; text-align:right">{{ $entity->print_price_on_po == "YES" ? (number_format($extra->cost,2)) : "" }}</td>
				<td style="padding-right: 2px; text-align:right">{{ $entity->print_price_on_po == "YES" ? (getCurrencyById($entity->currency)->name." ".number_format($entity_currency_total,2)) : "" }}</td>
			</tr>
			<?php $PO_TOTAL+=floatval($entity_currency_total); ?>
		@endforeach
		<tr>
			<td colspan="5" style="padding: 7px 15px; text-align: left; font-weight: 600">TOTAL</td>
			<td style="padding: 7px 2px; text-align: right; font-weight: 600">{{ $entity->print_price_on_po == "YES" ? (number_format($PO_TOTAL,2)) : "" }}</td>
		</tr>
		</tbody>
	</table>
	@if(!$isHTML)
		<?php
			$approvers = \App\Approvals::leftJoin('entity_approvals as ea', function($join) use($entity){
				$join->on('ea.approval_id', 'approvals.id');
				// $join->where('ea.model', $entity->request_type);
				$join->where('ea.model_id', $entity->id);
			})->leftJoin('users as u', 'u.id', 'ea.user_id')->where('ea.status', 'Approved')
			->where('approvals.stage', $entity->request_type)->orderBy('approvals.id', 'asc')->get()->toArray();

			// $approvers = \App\User::join('entity_approvals as ea', 'ea.user_id', 'users.id')->where('ea.status', 'Approved')
			// ->where('model', $entity->request_type)->where('model_id', $entity->id)->get()->toArray();
			// echo '<pre>'.json_encode($approvers, JSON_PRETTY_PRINT).'</pre>';

			$approvalCounts = $entity->defined_approvals()->count();
		?>
		<div class="page-break-inside" style="break-inside: avoid; page-break-inside: avoid;">
			<table border="0" style="border-collapse: collapse; width: 100%;">
				<tbody>
					<tr>
					<td colspan="2" style="width: 100%;">
					<p style=" font-size: 12px;"><strong>{{ getConfigByName('site_name')->count() > 0 ? getConfigByName('site_name')[0]->value : 'Kenya Pollen' }} {!! getConfigByName('site_po_box')->count() > 0 ? getConfigByName('site_po_box')[0]->value : 'P.O. BOX 27774 - 0056 Nairobi' !!}</strong></p>
					</td>
					</tr>
					<tr>
					<td colspan="2" style="width: 100%;">
					<p style="font-size: 6px;  text-align: center; ">
						<strong>Standard Syngenta Terms and Condition apply. To view please go to <a href="https://www.syngenta.com/sites/syngenta/files/seeds/supplier-information-for-new-products/pdf/Kenya-Ts-and-Cs-2018-1.pdf">https://www.syngenta.com/sites/syngenta/files/seeds/supplier-information-for-new-products/pdf/Kenya-Ts-and-Cs-2018-1.pdf</a></strong>
					</p>
					</td>
					</tr>
				</tbody>
			</table>
			<table border="0" style="border-collapse: collapse; width: 100%;">
				<tbody>
					<tr>
						<?php $startKeySet = 0 ?>
						@if(!isKECU())
							<td style="text-align: center">
								<?php
									$requestedBy = \App\User::find($entity->request_initiator);
								?>
								<p style="padding: 5px 10px; width: 100%"><strong>Requested By:</strong> <br/><span style="padding: 5px 10px; width: 100%">{!! trim($requestedBy->electronic_sig) != "" ?
									'<small>'.$requestedBy->name.'</small> <br><img src="'.imageTobase64($requestedBy->electronic_sig).'" style="margin-left: 10px; height: 25px" />' : $requestedBy->name !!}</span>
								</p>
							</td>
							<td style="text-align: center">
								<p style="padding: 5px 10px; width: 100%"><strong>Departemental Head:</strong> <br/>
								@if(isset($approvers[$startKeySet]) && (isset($approvers[$startKeySet]['approved_at']) && trim($approvers[$startKeySet]['approved_at']) != ""))
									<span style="padding: 5px 10px; width: 100%">{!! trim($approvers[$startKeySet]['electronic_sig']) != "" ?
										'<small>'.$approvers[$startKeySet]['name'].'</small><br> <img src="'.imageTobase64($approvers[$startKeySet]['electronic_sig']).'" style="margin-left: 10px; height: 25px" />' : $approvers[$startKeySet]['name'] !!}</span>
									</p>
								@endif
							</td>
						@endif
						<?php $startKeySet = $approvalCounts == 2 ? 0 : 1 ?>
						<td style="text-align: {{ !isKECU() ? 'center' : 'right' }}">
							<p></p>
							<p style="padding: 5px 10px; width: 100%"><strong>1st Signature:</strong> <br/>
							@if(isset($approvers[$startKeySet]) && (isset($approvers[$startKeySet]['approved_at']) && trim($approvers[$startKeySet]['approved_at']) != ""))
								<span style="padding: 5px 10px; width: 100%">{!! trim($approvers[$startKeySet]['electronic_sig']) != "" ?
									'<small>'.((!isKECU()) ? $approvers[$startKeySet]['name'] : '').'</small><br/> <img src="'.imageTobase64($approvers[$startKeySet]['electronic_sig']).'" style="margin-left: 10px; height: 25px" />' : $approvers[$startKeySet]['name'] !!}
								</span>
							@endif
							</p>
						</td>
					</tr>
					<tr>
						@if(!isKECU())
							<td style="text-align: center">
								<?php $preparedBy = \App\User::find($entity->created_by); ?>
								<p style="padding: 5px 10px; width: 100%"><strong>Prepared By:</strong> <br/>
								
								<span style="padding: 5px 10px; width: 100%">{!! trim($preparedBy->electronic_sig) != "" ?
									'<small>'.$preparedBy->name.'</small><br/> <img src="'.imageTobase64($preparedBy->electronic_sig).'" style="margin-left: 10px; height: 25px" />' : $preparedBy->name !!}</span>
								</p>
							</td>
							<td></td>
						@endif
						<?php $startKeySet = $startKeySet + 1 ?>
						<td style="text-align: {{ !isKECU() ? 'center' : 'right' }}">
							<p style="padding: 5px 10px; width: 100%"><strong>2nd Signature:</strong> <br/>
								@if(isset($approvers[$startKeySet]) && (isset($approvers[$startKeySet]['approved_at']) && trim($approvers[$startKeySet]['approved_at']) != ""))
									<span style="padding: 5px 10px; width: 100%">{!! trim($approvers[$startKeySet]['electronic_sig']) != "" ?
										'<small>'.((!isKECU()) ? $approvers[$startKeySet]['name'] : '').'</small><br/> <img src="'.imageTobase64($approvers[$startKeySet]['electronic_sig']).'" style="margin-left: 10px; height: 25px" />' : $approvers[$startKeySet]['name'] !!}
									</span>
								@endif
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	@endif
</div>
@endif
<div style="text-align:center; padding: 15px; font-size: 12px">
	Nature of Purchase : {{ $entity->nature_of_purchase }}
	@if($entity->nature_of_purchase == "Capex")
		- <small>{{ $entity->capex_project_number }}</small>
	@endif
</div>
<script>
	window.onload = function(){
		var elem = document.getElementById('print-button');
		elem.style.display = "unset";
	}
</script>