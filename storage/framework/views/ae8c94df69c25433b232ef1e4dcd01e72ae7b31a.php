<style type="text/css">
	@media  print{
		button{
				display:none;
		}
	}
</style>
<?php if(!$isHTML): ?>
<div style="padding: 10px 15px">
	<button style="padding: 5px 10px; font-size: 13px" onclick="window.print()">Print</button>
</div>
<?php endif; ?>
<table style="border-collapse: collapse; width: 100%; height: 84px; margin-bottom: 10px;" border="0">
	<tbody>
		<tr style="">
			<td colspan="3">
				<table style="width: 100%;" border="0">
					<tbody>
						<tr>
							<td style="width: 70%; "><img src="<?php echo e(imageTobase64('/images/syngenta-flowers.jpg', true)); ?>" width="180" height="auto" alt="" />
								<br /><br /><br />
								<?php echo e($supplier->name); ?>

								<br /><?php echo e($supplier->building); ?>, <?php echo e($supplier->street); ?>, <?php echo e($supplier->town); ?>.
								<br />Tel: <?php echo e($supplier->phone); ?>

								<br /> <?php echo e($supplier->address); ?>

							</td>
							<td style="width: 30%; ">
								Aqualytic LAB<br /> <strong>Off Mombasa Road</strong><br /> Mobile: +254 722547344<br /> lab@aqualyticlab.com <br />www.aqualyticlab.com<br /><br />
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
			<td style=" text-align: left; font-size: 9px; width: 3.83333%; padding-left: 6px;" colspan="2"><br /> <strong>RFQ No. <?php echo e($entity->request_code); ?></strong><br /><br /><br /> <strong>Purpose: <?php echo e($requisition->description); ?></strong><br /><br /></td>
			<td style=" text-align: left; font-size: 9px; width: 24.1939%; padding-left: 6px;" colspan="6">
					<br />
					<div>MANDATORY/SUPPLIER ADDRESS</div>
					<hr />
					Business Name :- AQUALYTIC LAB
					<br /> Postal Address : P.O. BOX 4600 - 00506 Nairobi
					<br /> TEL No : 0722547344
					<br /> E-mail : lab@aqualyticlab.com
			</td>
		</tr>
		<tr style="">
			<th style=" text-align: center; font-size: 8px; width: 3.83333%;">PR No</th>
			<th style=" text-align: center; font-size: 8px; width: 24.1939%;">Description</th>
			<th style=" text-align: center; font-size: 8px; width: 12.6645%;">Identification</th>
			<th style=" text-align: center; font-size: 8px; width: 12.6645%;">Cat No</th>
			<th style=" text-align: center; font-size: 8px; width: 12.1692%;">Quantity</th>
			<th style=" text-align: center; font-size: 8px; width: 10.3361%;">Unit of Measure</th>
			<th style=" text-align: center; font-size: 8px; width: 11.8361%;">Unit Price</th>
			<th style=" text-align: center; font-size: 8px; width: 11.8361%;">Total</th>
			<th style=" text-align: center; font-size: 8px; width: 16.0101%;">Remark</th>
		</tr>
	</thead>
	<tbody>
		<?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
			<tr style="">
				<td style=" text-align: center; font-size: 10px;"><?php echo e($entity->request_code); ?></td>
				<td style=" width: 24.1939%; font-size: 10px; padding-left: 6px;"><?php echo e($it->name); ?></td>
				<td style=" font-size: 10px; width: 12.6645%; padding-left: 6px;"><?php echo e($it->description); ?></td>
				<td style=" font-size: 10px; width: 12.6645%; padding-left: 6px;"><?php echo e($it->sap_code); ?></td>
				<td style=" width: 10.3361%; font-size: 10px; padding-right: 6px; text-align:right"><?php echo e(number_format($it->quantity,3)); ?></td>
				<td style=" width: 12.1692%; font-size: 10px; padding-left: 6px;"><?php echo e($it->unit_type); ?></td>
				<td style=" width: 11.8361%; font-size: 10px; padding-left: 6px;">&nbsp;</td>
				<td style=" width: 11.8361%; font-size: 10px; padding-left: 6px;">&nbsp;</td>
				<td style=" width: 16.0101%; font-size: 10px; padding-left: 6px;">&nbsp;</td>
			</tr>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
				<strong>OUR Terms of Payment : <?php echo e($supplier->payment_terms); ?></strong>
				<br /> Expected Delivery Date :- Urgent
				<br /> Supplier Delivery Date: STRICTLY
				<br /> <strong>DELIVER TO:</strong> <?php echo e($entity->delivery_to); ?>

				<br /> <strong>STORE:</strong><br /> <strong>Special Handling Requirements:</strong><br /><br />
			</td>
		</tr>
		<tr>
			<td style="width: 50%; padding: 3px 6px">Prepared by: <small>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo e(isset($approvals[0]) ? $approvals[0]->name : ''); ?></small></td>
			<td style="width: 25%; padding: 3px 6px">Date: <small>&nbsp;&nbsp;&nbsp;<?php echo e(isset($approvals[0]) ? \Carbon\Carbon::parse($approvals[0]->created_at)->format('Y-m-d') : ''); ?></small></td>
			<td style="width: 25%; padding: 3px 6px">Sign: &nbsp;&nbsp;&nbsp;
				<?php echo isset($approvals[0]->electronic_sig) && trim($approvals[0]->electronic_sig) != "" ?
					'<img src="'.imageTobase64($approvals[0]->electronic_sig).'" style="height: 25px" />' : ''; ?>

			</td>
		</tr>
		<tr>
			<td style="width: 50%; padding: 3px 6px">Approved by: <small>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo e(isset($approvals[1]) ? $approvals[1]->name : ''); ?></small></td>
			<td style="width: 25%; padding: 3px 6px">Date: <small>&nbsp;&nbsp;&nbsp;<?php echo e(isset($approvals[1]) ? \Carbon\Carbon::parse($approvals[1]->created_at)->format('Y-m-d') : ''); ?></small></td>
			<td style="width: 25%; padding: 3px 6px">Sign: &nbsp;&nbsp;&nbsp;
				<?php echo isset($approvals[1]->electronic_sig) && trim($approvals[1]->electronic_sig) != "" ?
					'<img src="'.imageTobase64($approvals[1]->electronic_sig).'" style="height: 25px" />' : ''; ?>

			</td>
		</tr>
	</tbody>
</table><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/inventory/templates/supplier-rfq.blade.php ENDPATH**/ ?>