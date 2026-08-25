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
	$active = getActiveCompany();

	// echo json_encode($active)
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
@if(true)
<div style="width: {{ $isInternal ? '1024px' : '595px' }}; margin:auto">
	@if(!$isHTML)
		<div style="padding: 10px 15px; display: none" id="print-button">
			<button style="padding: 5px 10px; font-size: 13px" onclick="window.print()">Print</button>
		</div>
	@endif
	<table border="0" style="border-collapse: collapse; width: 100%; margin-bottom: 10px;">
		<tbody>
			<tr style="padding: 4px 2px;">
				<td style="width: 61.8334%; padding: 4px 2px;">
					<img src="{{$active->logo}}" style="max-height: 55px" />
				</td>
				<td style="width: 38.1666%; padding: 4px 2px; text-align: right;">
					<p style=" font-size: 12px;">
						<strong style="font-size: 16px">{{ $active->name }}</strong><br/>
						{!! getConfigByName('site_po_box')->count() > 0 ? getConfigByName('site_po_box')[0]->value : $active->address !!}<br />
						<span>Tel: {{ getConfigByName('po_contact_telephone')->count() > 0 ? getConfigByName('po_contact_telephone')[0]->value : '' }} / {{ $active->cell_phone }}</span><br />
						<span>Email: {{ $active->email }}</span><br/>
						<span>Website: {{ $active->website }}</span><br />
						<span>Fax: {{ getConfigByName('po_contact_fax')->count() > 0 ? getConfigByName('po_contact_fax')[0]->value : '' }}</span>
						<span></span>
					</p>
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
						<td colspan="2" style="width: 100%; border: 1px solid black; font-size: 12px;">{!! getConfigByName('site_po_box')->count() > 0 ? getConfigByName('site_po_box')[0]->value : $active->address !!}</td>
					</tr>
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black; font-size: 12px;">Physical Address : {{ $active->street.", ".$active->location }}</td>
					</tr>
					
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black; font-size: 11px; text-align: center;">DELIVERY TO:</td>
					</tr>
					<tr style="padding: 4px 2px;">
						<?php
							$REQUESTER = \App\User::find($entity->request_initiator);
						?>
						<td style="border: 1px solid black; font-size: 12px;">Receivers Name</td>
						<td style="border: 1px solid black; font-size: 12px;">{{ isset($REQUESTER->name) ? $REQUESTER->name :  getConfigByName('po_receivers_name')[0]->value }}</td>					
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
						<td style="border: 1px solid black !important; border-bottom: none !important; font-size: 12px;">{{ \Carbon\Carbon::parse($entity->due_date)->format(isETCU() ? 'd/m/Y' : 'Y-m-d') }}</td>
					</tr>
					<tr>
						<td colspan="2" style="border: 1px solid black !important; border-bottom: none !important; font-size: 12px; text-align: center;">PLEASE SEND THE INVOICE TO:</td>
					</tr>
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black !important; border-bottom: none !important; font-size: 11px;">{{ getConfigByName('site_name')->count() > 0 ? getConfigByName('site_name')[0]->value : 'Kenya Pollen' }}</td>
					</tr>
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black !important; border-bottom: none !important; font-size: 11px;">{{ !isETCU() ? 'P.O. Box 27774 - 00506' : 'PO BOX 618 Code 1250' }}</td>
					</tr>
					<tr>
						<td colspan="2" style="width: 100%; border: 1px solid black !important; font-size: 11px;">{{ !isETCU() ? 'Thika' : 'Addis Ababa' }}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div style="clear: both; padding: 4px 2px;">
		<p style="font-size: 12px; text-align: center;"><strong>Syngenta Purchase Order must appear on all packages, invoices, shipping papers and correspondence.</strong></p>
	</div>
	<div style="padding: 4px 2px;">
		<p style="font-size: 11px; text-align: right;">CURRENCY: {{ getCurrencyById($entity->currency)->name ?? '' }}</p>
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
		<tr style="padding: 4px 2px; font-size: 12px; font-family:'Courier New', Courier, monospace">
			<td style="padding-right: 2px; text-align: center; width: 3.83333%;">{{ $loop->iteration }}</td>
			<td style="padding-right: 2px; width: 6.66667%; text-align: right">{{ $isKitRow ? 1 : number_format($item->quantity, 3).$item->unit_type }}</td>
			<td style="padding-right: 2px; width: 47%; text-align: center">
				@if($isKitRow)
					<span>{{ $item->kit_item_name }}</span>
				@else
					<span>{{ $item->item_name }} / {{ $item->code }}</span><br>
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
					{{-- <tr>
					<td colspan="2" style="width: 100%;">
					<p style=" font-size: 12px;"><strong>{{ getConfigByName('site_name')->count() > 0 ? getConfigByName('site_name')[0]->value : 'Kenya Pollen' }} {!! getConfigByName('site_po_box')->count() > 0 ? getConfigByName('site_po_box')[0]->value : 'P.O. BOX 27774 - 00506 Thika' !!}</strong></p>
					</td>
					</tr> --}}
					<tr>
					<td colspan="2" style="width: 100%;">
					<p style="font-size: 10px;  text-align: center; padding:20px 0px">
						@if(isETCU())
							<strong>
								Standard Syngenta Terms and Condition apply, unless specified differently above.
							</strong>
						@else
							<strong>Standard Syngenta Terms and Condition apply. To view please go to https://www.syngenta.com/contracts/default.html</strong>
						@endif
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
						<td style="text-align: {{ isETCU() ? 'left' : 'right' }}">
							<p></p>
							<p style="padding: 5px 0px; width: 100%"><strong>1st Signature:</strong> <br/>
							@if(isset($approvers[$startKeySet]) && (isset($approvers[$startKeySet]['approved_at']) && trim($approvers[$startKeySet]['approved_at']) != ""))
								<span style="padding: 5px 0px; width: 100%">{!! trim($approvers[$startKeySet]['electronic_sig']) != "" ?
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
								<p style="padding: 5px 0px; width: 100%"><strong>Prepared By:</strong> <br/>
								<span style="padding: 5px 0px; width: 100%">{!! trim($preparedBy->electronic_sig) != "" ?
									'<small>'.$preparedBy->name.'</small><br/> <img src="'.imageTobase64($preparedBy->electronic_sig).'" style="margin-left: 10px; height: 25px" />' : $preparedBy->name !!}</span>
								</p>
							</td>
							<td></td>
						@endif
						<?php $startKeySet = $startKeySet + 1 ?>
						<td style="text-align: {{ isETCU() ? 'left' : 'right' }}">
							<p style="padding: 5px 0px; width: 100%"><strong>2nd Signature:</strong> <br/>
								@if(isset($approvers[$startKeySet]) && (isset($approvers[$startKeySet]['approved_at']) && trim($approvers[$startKeySet]['approved_at']) != ""))
									<span style="padding: 5px 0px; width: 100%">{!! trim($approvers[$startKeySet]['electronic_sig']) != "" ?
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
<div style="text-align:center; padding: 15px; font-size: 12px; font-weight:700">
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