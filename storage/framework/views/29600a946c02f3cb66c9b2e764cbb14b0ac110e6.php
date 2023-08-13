

<?php $__env->startSection('title2'); ?>
<title> <?php echo e($customer->name); ?> - Customer | CRM</title>
<style type="text/css">
	.tab-card {
		border: 1px solid #eee;
	}

	.tab-card-header {
		background: none;
	}

	/* Default mode */
	.tab-card-header>.nav-tabs {
		border: none;
		margin: 0px;
	}

	.tab-card-header>.nav-tabs>li {
		margin-right: 2px;
	}

	.tab-card-header>.nav-tabs>li>a {
		border: 0;
		border-bottom: 2px solid transparent;
		margin-right: 0;
		color: #737373;
		padding: 2px 15px;
	}

	.tab-card-header>.nav-tabs>li>a.show {
		border-bottom: 2px solid #007bff;
		color: #007bff;
	}

	.tab-card-header>.nav-tabs>li>a:hover {
		color: #007bff;
	}

	.tab-card .nav-link.active {
		background-color: #dadccd !important;
		border: 1px solid #cccebf !important;
	}

	.tab-card-header>.tab-content {
		padding-bottom: 0;
	}

	.my-small-text {
		font-size: 12px !important;
	}
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
	<?php
	$items = array(
		array(
			'link' => route('customers-list'),
			'name' => 'CRM',
			'icon' => null
		),
		array(
			'link' => route('customers-list'),
			'name' => 'Customer List',
			'icon' => null
		),
		array(
			'link' => '#',
			'name' => $customer->name,
			'icon' => null
		)
	);
	?>
	 <?php if (isset($component)) { $__componentOriginal30091868428b09767320233ef70f89faadea10d9 = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\BreadCrumb::class, ['items' => $items]); ?>
<?php $component->withName('bread-crumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?> <?php if (isset($__componentOriginal30091868428b09767320233ef70f89faadea10d9)): ?>
<?php $component = $__componentOriginal30091868428b09767320233ef70f89faadea10d9; ?>
<?php unset($__componentOriginal30091868428b09767320233ef70f89faadea10d9); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?> 
	<h2 class="p-4">
		<i class="mdi mdi-microscope"></i> <?php echo e($customer->name); ?> <small class="text-muted"> | CRM</small>
	</h2>
	<div class="row no-gutters">
		<div class="col-sm-12 p-2">
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" id="Elements-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="Company-Units-tab" data-toggle="tab" href="#Company-Units" role="tab" aria-controls="Company-Units" aria-selected="true"><i class="mdi mdi-sitemap"></i> <?php echo e(trim($customer->unit_configurable_name)!="" ? $customer->unit_configurable_name : 'Company Units'); ?></a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Sample-Points-tab" data-toggle="tab" href="#Sample-Points" role="tab" aria-controls="Company-Units" aria-selected="true"><i class="mdi mdi-map-marker"></i> <?php echo e(trim($customer->sample_point_configurable_name)!="" ? $customer->sample_point_configurable_name : 'Sample Points'); ?></a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Contacts-tab" data-toggle="tab" href="#Contacts" role="tab" aria-controls="Contacts" aria-selected="true"><i class="mdi mdi-account-box-outline"></i> Contacts</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Samples-tab" data-toggle="tab" href="#Orders" role="tab" aria-controls="Orders" aria-selected="true"><i class="mdi mdi-eyedropper-plus"></i> Orders</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Samples-tab" data-toggle="tab" href="#Samples" role="tab" aria-controls="Samples" aria-selected="true"><i class="mdi mdi-test-tube"></i> Reports</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Complaints-tab" data-toggle="tab" href="#Complaints" role="tab" aria-controls="Complaints" aria-selected="true"><i class="mdi mdi-comment-alert"></i> Complaints</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Feedbacks-tab" data-toggle="tab" href="#Feedbacks" role="tab" aria-controls="Feedbacks" aria-selected="true"><i class="mdi mdi-file-account"></i> Customer Feedback</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Customer-Details-tab" data-toggle="tab" href="#quotations" role="tab" aria-controls="Customer-Details" aria-selected="true"><i class="mdi mdi-file-settings"></i> Quotation</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Certification-tab" data-toggle="tab" href="#Certification" role="tab" aria-controls="Certification" aria-selected="true"><i class="mdi mdi-file-certificate"></i> Attachments</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Customer-Details-tab" data-toggle="tab" href="#Customer-Details" role="tab" aria-controls="Customer-Details" aria-selected="true"><i class="mdi mdi-information-outline"></i> Details</a>
						</li>
					</ul>
				</div>

				<div class="tab-content" id="Samples-tabs-content">
					<!-- ---------------------------------quotations-----------------------------  -->
					<div class="tab-pane fade p-3" id="quotations" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">
							<i class="mdi mdi-file-settings"></i> Quotations
						</h5>
						<div class="table-responsive">
							<table class="table table-condensed table-hover table-stripped table-sm table-bordered">
								<thead>
									<tr>
										<th>Quote No</th>
										<th>Quote Type</th>
										<th>Status</th>
										<th>Quote Date</th>
										<th>Expiry Date</th>
										<th>Prepared By</th>
										<th>Total</th>
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $quotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<tr>
										<td><a target="_blank" href="<?php echo e(route('add-qoute-details-view',['id'=>$quote->id])); ?>"><?php echo e($quote->quote_number); ?></a></td>
										<td><?php echo e($quote->quotation_type); ?></td>
										<td><?php echo e($quote->status); ?></td>
										<td><?php echo e($quote->quote_date); ?></td>
										<td><?php echo e($quote->expiring_date); ?></td>
										<td><?php echo e($quote->creator); ?></td>
										<td style="text-align: right !important;"><?php echo e(number_format($quote->total_amount,2)); ?></td>
									</tr>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- ---------------------------------quotations-----------------------------  -->
					<div class="tab-pane fade show p-3" id="Certification" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">
							<i class="mdi mdi-file-certificate-outline"></i> Attachments
							<button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-certificate"><i class="mdi mdi-plus"></i> Add</button>
						</h5>
						<div class="table-responsive bg-light p-4">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th nowrap>Name</th>
										<th nowrap>Attachment</th>
										<th>Start Date</th>
										<th>End Date</th>
										<th nowrap>Attachment Body</th>
										<th nowrap>Status</th>

										<th nowrap>Edited By</th>
										<th></th>
										<th></th>

										<!-- ---  -->

									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $certifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<?php if($item->status == 0): ?>
									<?php
									$qualification_sample = getSampleTypeQualificationById($item->qualification_id)
									?>
									<tr>
										<td valign="center"><?php echo e($loop->iteration); ?></td>

										<td><?php echo e($item->name); ?></td>
										<td style="text-align: center;"><a href="<?php echo e($item->certificate); ?>" class="btn btn-sm btn-transparent" target="_blank"><i class="mdi mdi-download text-success"></i></a></td>
										<td><small><?php echo e($item->certification_date); ?></small></td>
										<td><small><?php echo e($item->expire_date); ?></small></td>
										<td><?php echo e($item->certification_body); ?></td>
										<td class="text-small"><?php echo $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
										<td><?php echo $item->edited == '' ? 'N/a':$item->edited; ?></td>
										
										<td>


											<span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-qualifications-<?php echo e($item->id); ?>"> <i class="mdi mdi-pencil"></i> </span>

											<div id="edit-qualifications-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog">

													<form action="<?php echo e(route('edit-customer-certification',['id'=>$item->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
														<?php echo csrf_field(); ?>
														<div class="modal-header">
															<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($item->name); ?> Customer Certification </h4>
														</div>
														<div class="modal-body">
															<div class="form-group">
																<label class="control-label">Name</label>
																<input type="text" name="name" placeholder="Attachment Name..." class="form-control">
															</div>
															<div class="form-group">
																<label class="control">Certificate <small style="color: red;">*Leave it blank to retain the intial certificate</small> </label>
																<input type="file" name="certificate" class="form-control" placeholder="Choose File..." value="">
															</div>
															<div class="form-group">
																<?php
																$date = date("Y-m-d", strtotime($item->certificate_date));
																$expire = date("Y-m-d", strtotime($item->expire_date));
																?>
																<label class="control-label">Certificate date</label>

																<input type="date" name="certification_date" value="<?php echo e($date); ?>" class="form-control" placeholder="Certificate Date...">
															</div>
															<div class="form-group hidden">
																<label class="control-label">Cert Id</label>
																<input type="text" name="cert_id" class="form-group" value="<?php echo e($item->id); ?>">
															</div>
															<div class="form-group">
																<label class="control-label">Expire Date</label>
																<input type="date" name="expire_date" class="form-control" placeholder="Expire Date..." value="<?php echo e($expire); ?>">
															</div>
															<div class="form-group">
																<label class="control-label">Certification Body</label>
																<input type="text" name="certification_body" class="form-control" placeholder="Certification Body..." value="<?php echo e($item->certification_body); ?>">
															</div>
															<div class="form-group">
																<label class="control-label">Status</label>

																<select name="status" class="form-control" placeholder="Operator...">

																	<option value=0 <?php echo e($item->status == 0 ? 'selected' : ''); ?>>Active</option>
																	<option value=1 <?php echo e($item->status == 1 ? 'selected': ''); ?>>Archive</option>


																</select>
															</div>

														</div>
														<div class="modal-footer">
															<button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Update</button>
															<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
														</div>
													</form>
												</div>
											</div>
										</td>
										<td>
											<span class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#delete-qualifications-<?php echo e($item->id); ?>"> <i class="mdi mdi-delete-empty"></i> </span>
											<div class="modal fade" id="delete-qualifications-<?php echo e($item->id); ?>">
												<div class="modal-dialog">
													<form action="<?php echo e(route('delete-customer-certification',['id'=>$item->id])); ?>" method="post" class="modal-content">
														<?php echo csrf_field(); ?>
														<div class="modal-header">
															<h4 class="modal-title"> <i style="color: red;" class="mdi mdi-delete-empty"></i> Delete <?php echo e($item->name); ?> Customer Attachment</h4>
														</div>
														<div class="modal-body">
															<div class="panel panel-default">
																<div class="panel-body">
																	Are you sure you want to delete <b><?php echo e($item->name); ?></b> Customer Attachment!
																</div>
															</div>
															<div class="form-group hidden">
																<label class="control-label">Qualification</label>
																<input type="text" name="qualification" value="<?php echo e($item->id); ?>">
															</div>
														</div>
														<div class="modal-footer">
															<button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Yes</button>
															<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
														</div>
													</form>
												</div>
											</div>
										</td>
									</tr>
									<?php endif; ?>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- --------------------complaints----------  -->
					<div class="tab-pane fade p-3" id="Complaints" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">Complaints
							<!-- <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-complaint"><i class="mdi mdi-plus"></i> Add</button> -->
							<!-- ---------complaint ------  -->
							<div id="add-complaint" class="modal fade" role="dialog">
								<div class="modal-dialog">
									<!-- Modal content-->
									<form class="modal-content" method="POST" action="<?php echo e(route('customer-add-complaint')); ?>" enctype="multipart/form-data">
										<?php echo csrf_field(); ?>
										<div class="modal-header">
											<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Complaint</h4>
										</div>
										<div class="modal-body">
											<div class="form-group">
												<label class="control-label">Priority</label>
												<select name="priority" id="assign-status" class="form-control" readonly="true" placeholder="Assign Priority...">
													<option value="high">High</option>
													<option value="medium">Medium</option>
													<option value="low">Low</option>
												</select>
											</div>
											<div class="form-group">
												<label class="control-label">Complaint Type</label>
												<select name="type" class="form-control" placeholder="Recieved From...">
													<?php $__currentLoopData = $complaint_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
													<option value="<?php echo e($type->name); ?>"><?php echo e($type->name); ?></option>
													<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
												</select>
											</div>

											<div class="form-group">
												<label class="control-label">Date</label>
												<input type="date" name="date" value="" placeholder="Complaint Date..." class="form-control" required>
											</div>
											<div class="form-group">
												<label class="control-label">Complaint description</label>
												<textarea class="form-control" rows="4" name="description" value="" placeholder="Compliant Description..." required></textarea>
											</div>

										</div>
										<div class="modal-footer">
											<button type="submit" class="btn btn-primary"><i class=""></i> Save</button>
											<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
										</div>
									</form>
								</div>
							</div>
							<!-- ---------endcomplaint ------  -->
						</h5>
						<div class="table-responsive bg-light p-4">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th>Priority</th>
										<th>Complaint No</th>
										<th>Complaint Type</th>
										<th>Received From</th>
										<th nowrap>Registred_By</th>
										<th>Date</th>
										<th>Status</th>
										<!-- ---  -->
										<th nowrap>WorkFlow Stage</th>

										<th>Description</th>


									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $complaints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<tr>
										<td valign="center"><?php echo e($loop->iteration); ?></td>

										<td>
											<?php echo $item->priority == 'high' ? '<i style="color:red;" class="mdi mdi-star-four-points"></i><span style="color: red;">high</span>':$item->priority; ?>

										</td>
										<td><?php echo e($item->complaint_id); ?></td>
										<td><?php echo e($item->type); ?></td>
										<td nowrap><?php echo e($item->received_from); ?></td>
										<td><?php echo e($item->registered_by); ?></td>
										<td><?php echo e($item->date); ?></td>
										<!-- -----  -->
										<td class="text-small"><?php echo $item->rejected == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>

										<td>
											<?php
											$stage = getComplaintWorkflow()[$item->complaint_workflow]
											?>
											<?php echo e($stage); ?>

										</td>


										<td class="text-center">
											<span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#complaint-description-<?php echo e($item->id); ?>"> <i class="mdi mdi-message-text"></i></span>
											<div id="complaint-description-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<!-- Modal content-->
													<div class="modal-content">

														<div class="modal-header">
															<h4 class="modal-title"><i class="mdi mdi-eye"></i><?php echo e($item->complaint_id); ?> Complaint Description</h4>
														</div>
														<div class="modal-body">
															<h5>Complaint Description.</h5>
															<div class="pane panel-default">
																<div class="panel-body">
																	<?php echo e($item->description); ?>

																</div>
															</div>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
														</div>
													</div>
												</div>
											</div>
										</td>

									</tr>

									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- -------------------end compliants  ---------  -->
					<!-- ------------------------feedbacks -----------------  -->
					<div class="tab-pane fade p-3" id="Feedbacks" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title"><i class="mdi mdi-file-account-outline"></i>Customer Feedback
							<!-- <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-feedback"><i class="mdi mdi-plus"></i> Add</button> -->
						</h5>
						<div class="table-responsive bg-light p-4">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th nowrap>Received From</th>
										<th>User Type</th>
										<th nowrap>Registered By</th>
										<th>Date</th>
										<th>Status</th>
										<th>Feedback</th>


										<!-- ---  -->

									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $feedbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

									<tr>
										<td valign="center"><?php echo e($loop->iteration); ?></td>

										<td><?php echo e($item->received_from); ?></td>
										<td><?php echo e($item->user_type); ?></td>
										<td><?php echo e($item->registered_by); ?></td>
										<td><?php echo e($item->date); ?></td>

										<td class="text-small"><?php echo $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>

										<!-- -----  -->

										<td class="text-center">
											<span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#feedback-description-<?php echo e($item->id); ?>"> <i class="mdi mdi-message-text"></i></span>
											<div id="feedback-description-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<!-- Modal content-->
													<div class="modal-content">

														<div class="modal-header">
															<h4 class="modal-title"><i class="mdi mdi-eye"></i>Fedback <?php echo e($loop->iteration); ?> Description</h4>
														</div>
														<div class="modal-body">
															<h5>Feedback Description.</h5>
															<div class="pane panel-default">
																<div class="panel-body">
																	<?php echo e($item->feedback); ?>

																</div>
															</div>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
														</div>
													</div>
												</div>
											</div>
										</td>

									</tr>

									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- -----------end feedbacks --------------  -->
					<div class="tab-pane fade p-3" id="Orders" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">
							Orders
							<a class="btn btn-outline-primary btn-sm float-right ml-sm-2" href="<?php echo e(route('view-batch-details', ['batch'=>time(), 'client'=>$customer->id])); ?>"><i class="mdi mdi-plus"></i> Order</a>
						</h5>
						<hr>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th></th>
										<th>Batch Code</th>
										<th>Date Collected</th>
										<th>Reference Number</th>
										<th nowrap>Document Number</th>
										<th nowrap>Sample Analysis</th>
										<th>Samples</th>
										<th>Status</th>
										
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $ordersSel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<tr>
										<td>
											<a href="<?php echo e(route('view-batch-details', ['batch'=>$order->id, 'client'=>$customer->id])); ?>"><i class="mdi mdi-eye"></i></a>
										</td>
										
										<td><?php echo e($order->batch_code); ?></td>
										<td><?php echo e($order->date_collected); ?></td>
										<td><?php echo e($order->reference_number); ?></td>
										<td><?php echo e($order->document_number); ?></td>
										<td nowrap><?php echo e($order->sample_type); ?></td>
										<td><?php echo e($order->samples); ?></td>
										<td><?php echo e($order->status); ?></td>
									</tr>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane fade p-3" id="Samples" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">Results</h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-stripped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										
										<th style="min-width: 70px 1important;"></th>
										<th >Code</th>
										<th>Scope</th>
										<th>Submitted BY</th>
										<th >Ref. No.</th>

										<th >Sample Analysis</th>
										<th>Sample Codes</th>
										<th>Receipt Date</th>
										<th>Sampling Date</th>
										<th >Description</th>
										<th>Report</th>
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $samples; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<tr>

										<td class="text-center" >
											<span class="mdi mdi-file-document-edit btn-sm btn-outline-warning" data-toggle="modal" data-target="#ammendment-<?php echo e($s->id); ?>" data-toggle="tooltip" title="Raise Ammendment"></span>
											
											<div class="modal fade" id="ammendment-<?php echo e($s->id); ?>" role="dialog">
												<div class="modal-dialog">
													<div class="modal-content">
														<form action="<?php echo e(route('add-batch-ammendment')); ?>" enctype="multipart/form-data" method="post">
															<?php echo csrf_field(); ?>
															<div class="modal-header">
																<h5 class="modal-title">
																	<i class="mdi mdi-file-document-edit"></i> Ammend Batch <?php echo e($s->batch_code); ?>

																</h5>
															</div>
															<div class="modal-body">
																<div class="form-group">
																	<label class="control-label">Samples <span class="text-danger">*</span></label>
																	<select name="samples[]" id="choose-samples" class="form-control" data-placeholder="Select Samples ..." multiple required>

																		<?php $__currentLoopData = $s->all_samples(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sample): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																		<option value="<?php echo e($sample->id); ?>"><?php echo e($sample->sample_code); ?></option>
																		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
																	</select>
																</div>
																<div class="form-group">
																	<label class="control-label">Reason <span class="text-danger">*</span></label>
																	<textarea class="form-control" rows="6" name="reason" placeholder="Reason..." required></textarea>
																</div>
																<input type="hidden" name="batch_id" value="<?php echo e($s->id); ?>">
															</div>
															<div class="modal-footer">

																<button type="submit" class="btn btn-outline-success btn-sm"><i class="mdi mdi-content-save"></i> Save</button>
																<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
															</div>
														</form>
													</div>
												</div>
											</div>
										</td>
										<td><a target="_blank" href="<?php echo e(route('view-batch-details', ['batch'=>$s->id])); ?>"><?php echo e($s->batch_code); ?></a></td>
										<td><?php echo e($s->batch_scope); ?></td>
										<td><?php echo e($s->submit_by); ?></td>
										<td><?php echo e($s->reference_number); ?></td>
										<td><?php echo e($s->sample_type); ?></td>
										<td><?php echo e(getBatchSampleCodes($s->id)); ?></td>
										<td><?php echo e($s->receipt_date); ?></td>
										<td><?php echo e($s->date_collected); ?></td>
										<td><?php echo e($s->description); ?></td>
										
										<td><a target="_blank" href="<?php echo $s->batch_report_url == '' ? '' : '/storage'.$s->batch_report_url; ?>"><i class="mdi mdi-download"></i> Download Report</a></td>
										
									</tr>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane fade show active p-3" id="Company-Units" role="tabpanel" aria-labelledby="one-tab">
						<div class="p-2 row">
							<div class="col-sm-8">
								<h5><i class="mdi mdi-format-list-bulleted"></i> <?php echo e(trim($customer->unit_configurable_name)!="" ? $customer->unit_configurable_name : 'Company Units'); ?>

									<small class="btn btn-transparent text-info" data-column='unit_configurable_name' data-target="#change-label-name" data-toggle="modal" data-current="<?php echo e($customer->unit_configurable_name); ?>">
										<i class="mdi mdi-pencil"></i>
									</small>
								</h5>
							</div>
							<div class="col-sm-4 align-content-center">
								<span class="btn btn-primary float-right btn-sm" data-target="#add-company-unit" data-toggle="modal">
									<i class="mdi mdi-plus"></i> Add
								</span>
							</div>
						</div>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th nowrap>Name</th>
										<th nowrap>Active</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $customer->units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<tr>
										<td><?php echo e($loop->iteration); ?></td>
										<td><?php echo e($unit->name); ?></td>
										<td class="text-small"><?php echo $unit->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
										<td nowrap>
											<button class="btn btn-primary btn-sm" data-target="#edit-company-unit-<?php echo e($loop->iteration); ?>" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
											<div id="edit-company-unit-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<!-- Modal content-->
													<form class="modal-content" method="POST" action="<?php echo e(route('edit-company-unit', ['id'=>$unit->id, 'cust_id'=>$customer->id])); ?>" enctype="multipart/form-data">
														<?php echo csrf_field(); ?>
														<div class="modal-header">
															<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Company Unit</h4>
														</div>
														<div class="modal-body">
															<div class="form-group">
																<label class="control-label">Name</label>
																<input type="text" class="form-control" name="name" value="<?php echo e($unit->name); ?>" placeholder="Name..." required />
															</div>
															<div class="form-group">
																<label class="control-label"><input type="checkbox" value="1" name="active" <?php echo e($unit->active == 1 ? 'checked' : ''); ?> /> Is Active?</label>
															</div>
														</div>
														<div class="modal-footer">
															<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
															<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
														</div>
													</form>
												</div>
											</div>
										</td>
									</tr>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane fade p-3" id="Sample-Points" role="tabpanel" aria-labelledby="one-tab">
						<div class="p-2 row">
							<div class="col-sm-8">
								<h5><i class="mdi mdi-format-list-bulleted"></i> <?php echo e(trim($customer->sample_point_configurable_name)!="" ? $customer->sample_point_configurable_name : 'Sample Points'); ?>

									<small class="btn btn-transparent text-info" data-column='sample_point_configurable_name' data-target="#change-label-name" data-toggle="modal" data-current="<?php echo e($customer->sample_point_configurable_name); ?>">
										<i class="mdi mdi-pencil"></i>
									</small>
								</h5>
							</div>
							<div class="col-sm-4 align-content-center">
								<span class="btn btn-primary float-right btn-sm" data-target="#add-company-sample-point" data-toggle="modal">
									<i class="mdi mdi-plus"></i> Add
								</span>
							</div>
						</div>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th nowrap>Name</th>
										<th nowrap><?php echo e(trim($customer->unit_configurable_name)!="" ? $customer->unit_configurable_name : 'Unit'); ?></th>
										<th nowrap>Active</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $customer->units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cunit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<?php $__currentLoopData = $cunit->sample_points; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<tr>
										<td><?php echo e($loop->iteration); ?></td>
										<td><?php echo e($unit->name); ?></td>
										<td><?php echo e($unit->unit->name); ?></td>
										<td class="text-small"><?php echo $unit->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
										<td nowrap>
											<button class="btn btn-primary btn-sm" data-target="#edit-sample-point-<?php echo e($loop->iteration); ?>" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
											<div id="edit-sample-point-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog modal-lg">
													<!-- Modal content-->
													<form class="modal-content" method="POST" action="<?php echo e(route('edit-sample-point', ['id'=>$unit->id, 'cust_id'=>$customer->id])); ?>" enctype="multipart/form-data">
														<?php echo csrf_field(); ?>
														<div class="modal-header">
															<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit <?php echo e(trim($customer->sample_point_configurable_name)!="" ? $customer->sample_point_configurable_name : 'Sample Points'); ?></h4>
														</div>
														<div class="modal-body">
															<div class="form-group">
																<label class="control-label">Name</label>
																<input type="text" class="form-control" name="name" value="<?php echo e($unit->name); ?>" placeholder="Name..." required />
															</div>
															<div class="form-group">
																<label><?php echo e(trim($customer->unit_configurable_name)!="" ? $customer->unit_configurable_name : 'Unit'); ?></label>
																<select class="form-control" name="unit" placeholder="Select..." required>
																	<option></option>
																	<?php $__currentLoopData = $customer->units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																	<option value="<?php echo e($item->id); ?>" <?php echo e($unit->crm_company_unit_id == $item->id ? 'selected' : ''); ?>><?php echo e($item->name); ?></option>
																	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
																</select>
															</div>
															<div class="form-group">
																<label class="control-label"><input type="checkbox" value="1" name="active" <?php echo e($unit->active == 1 ? 'checked' : ''); ?> /> Is Active?</label>
															</div>
														</div>
														<div class="modal-footer">
															<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
															<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
														</div>
													</form>
												</div>
											</div>
										</td>
									</tr>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
				
					<div class="tab-pane fade p-3" id="Contacts" role="tabpanel" aria-labelledby="one-tab">
						<div class="p-2 row">
							<div class="col-sm-8">
								<h5><i class="mdi mdi-account-multiple"></i> Company Contacts</h5>
							</div>
							<div class="col-sm-4 align-content-center">
								<span class="btn btn-primary float-right btn-sm" data-target="#add-company-contacts" data-toggle="modal">
									<i class="mdi mdi-plus"></i> Add
								</span>
							</div>
						</div>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										
										<th style="min-width: 70px !important;"></th>
										<th nowrap>First Name</th>
										<th nowrap>Middle Name</th>
										<th nowrap>Last Name</th>
										<th nowrap>Job Title</th>
										<th nowrap>Unit Name(s)</th>
										<th nowrap>Email</th>
										<th nowrap>Telephone</th>
										<th nowrap>Mobile</th>
										<th nowrap>Receives Price List?</th>
										<th nowrap>Receives Invoice?</th>
										<th nowrap>Receives Report?</th>
										<th nowrap>Active?</th>
										<th nowrap>Can Login?</th>
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $customer->contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<tr>
										<td nowrap>
											<button class="btn btn-primary btn-sm" data-target="#edit-company-contact-<?php echo e($loop->iteration); ?>" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
											<div id="edit-company-contact-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog modal-lg">
													<!-- Modal content-->
													<form class="modal-content" method="POST" action="<?php echo e(route('edit-company-contact', ['id'=>$contact->id, 'cust_id'=>$customer->id])); ?>" enctype="multipart/form-data">
														<?php echo csrf_field(); ?>
														<div class="modal-header">
															<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Company Contact</h4>
														</div>
														<div class="modal-body">
															<div class="row">
																<div class="col-sm-4">
																	<div class="form-group">
																		<label class="control-label">Title <span class="text-danger">*</span></label>
																		<select name="title" class="form-control" placeholder="Title..." required>
																			<option></option>
																			<?php $__currentLoopData = getModulePreconfig("Designation", "Personnel-Management"); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																			<option value="<?php echo e($item->id); ?>" <?php echo e($contact->title_id == $item->id ? 'selected' :''); ?>><?php echo e($item->name); ?></option>
																			<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
																		</select>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label class="control-label">First Name <span class="text-danger">*</span></label>
																		<input type="text" class="form-control" name="first_name" value="<?php echo e($contact->first_name); ?>" placeholder="First Name..." required />
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label class="control-label">Middle Name</label>
																		<input type="text" class="form-control" name="second_name" value="<?php echo e($contact->middle_name); ?>" placeholder="Name..."/>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label class="control-label">Last Name</label>
																		<input type="text" class="form-control" name="third_name" value="<?php echo e($contact->last_name); ?>" placeholder="Last Name..." />
																	</div>
																</div>
															</div>
															<div class="row">
																<div class="col-sm-4">
																	<div class="form-group">
																		<label class="control-label">Occupation</label>
																		<input type="text" class="form-control" name="job_occupation" value="<?php echo e($contact->job_occupation); ?>" placeholder="Occupation..."  />
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label class="control-label"><?php echo e(trim($customer->unit_configurable_name)!="" ? $customer->unit_configurable_name : 'Unit'); ?> <span class="text-danger">*</span></label>
																		<select class="form-control" name="unit_name[]" multiple placeholder="Select...">
																			<option></option>
																			<?php $__currentLoopData = $customer->units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																			<option value="<?php echo e($unit->name); ?>" <?php echo e(hasUnitName($unit->name, $contact->unit_name) ? 'selected' : ''); ?>><?php echo e($unit->name); ?></option>
																			<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
																		</select>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label class="control-label">Email</label>
																		<input type="email" class="form-control" name="email" value="<?php echo e($contact->email); ?>" placeholder="Email..." required />
																	</div>
																</div>
															</div>
															<div class="row">
																<div class="col-sm-4">
																	<div class="form-group">
																		<label class="control-label">Telephone <span class="text-danger">*</span></label>
																		<input type="text" class="form-control" name="telephone" value="<?php echo e($contact->telephone); ?>" placeholder="Telephone..." required />
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label class="control-label">Mobile</label>
																		<input type="text" class="form-control" name="mobile" value="<?php echo e($contact->mobile); ?>" placeholder="Mobile..." />
																	</div>
																</div>
																
															</div>
															<div class="row">
															<div class="col-sm-4">
																	<div class="form-group" style="padding-top: 40px">
																		<label class="control-label"><input type="checkbox" value="1" name="receive_report" <?php echo e($contact->receive_report == 1 ? 'checked' : ''); ?> /> Receives Report?</label>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group" style="padding-top: 40px">
																		<label class="control-label"><input type="checkbox" value="1" name="receive_price_list" <?php echo e($contact->receive_price_list == 1 ? 'checked' : ''); ?> /> Receives Pricelist?</label>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group" style="padding-top: 40px">
																		<label class="control-label"><input type="checkbox" value="1" name="receive_invoice" <?php echo e($contact->receive_invoice == 1 ? 'checked' : ''); ?> /> Receives Invoice?</label>
																	</div>
																</div>
																
															</div>
															<div class="row">
																<div class="col-sm-6">
																	<div class="form-group">
																		<label class="control-label">
																			<input type="checkbox" name="has_credentials" value="1" <?php echo e($contact->can_login == 1 ? 'checked':''); ?> /> Create/Update User Passwords
																		</label>
																	</div>
																</div>
																<div class="col-sm-6">
																	<div class="form-group" style="padding-top: 40px">
																		<label class="control-label"><input type="checkbox" value="1" name="active" <?php echo e($contact->active == 1 ? 'checked' : ''); ?> /> Is Active?</label>
																	</div>
																</div>
															</div>
															<div class="row hidden" id="passwords-holder">
																<div class="col-sm-4">
																	<div class="form-group">
																		<label>Password</label>
																		<input autocomplete="off" type="password" value="" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control pass" name="main_password" placeholder="Password..." />
																		<div class="has-success"></div>
																	</div>
																</div>
																<div class="col-sm-4">
																	<div class="form-group">
																		<label>Confirm Password</label>
																		<input type="password" value="" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control pass" name="confirm_password" placeholder="Confirm Password..." />
																		<div class="has-error"></div>
																	</div>
																</div>
															</div>
														</div>
														<div class="modal-footer">
															<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
															<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
														</div>
													</form>
												</div>
											</div>
										</td>
										<td><?php echo e($contact->first_name); ?></td>
										<td><?php echo e($contact->middle_name); ?></td>
										<td><?php echo e($contact->last_name); ?></td>
										<td><?php echo e($contact->job_occupation); ?></td>
										<td><?php echo e($contact->unit_name); ?></td>
										<td><?php echo e($contact->email); ?></td>
										<td><?php echo e($contact->telephone); ?></td>
										<td><?php echo e($contact->mobile); ?></td>
										<td class="text-small"><?php echo $contact->receive_price_list == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
										<td class="text-small"><?php echo $contact->receive_invoice == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
										<td class="text-small"><?php echo $contact->receive_report == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
										<td class="text-small"><?php echo $contact->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
										<td class="text-small"><?php echo $contact->can_login== '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
										
									</tr>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane fade p-3" id="Customer-Details" role="tabpanel" aria-labelledby="one-tab">
						<form method="POST" action="<?php echo e(route('edit-customer', ['id'=>$customer->id])); ?>" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>
							<div class="p-2 row">
								<div class="col-sm-8">
									<h5><i class="mdi mdi-pencil"></i> Customer Details</h5>
								</div>
								<div class="col-sm-4 align-content-center">
									<button type="submit" class="btn btn-primary float-right btn-sm"><i class="mdi mdi-content-save-edit"></i> Save</button>
								</div>
							</div>
							<br>
							<div class="p-2">
								<div class="row">
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label">Name <span class="text-danger">*</span></label>
											<input type="text" class="form-control" name="name" value="<?php echo e($customer->name); ?>" placeholder="Name..." required />
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label">Physical Address <span class="text-danger">*</span></label>
											<input type="text" class="form-control" name="physical_address" value="<?php echo e($customer->physical_address); ?>" placeholder="Location..." required />
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label">Postal Address <span class="text-danger">*</span></label>
											<textarea class="form-control" name="postal_address" placeholder="Postal Address..." required><?php echo e($customer->postal_address); ?></textarea>
										</div>
									</div>
								</div>
								<hr>
								<div class="row">
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label">Website</label>
											<input type="text" class="form-control" name="website" value="<?php echo e($customer->website); ?>" placeholder="Website..."  />
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label">Country <span class="text-danger">*</span></label>
											<select class="form-control" name="country_id" data-placeholder>
												<?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
												<option value="<?php echo e($c->id); ?>" <?php echo e($c->id == $customer->country_id ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
												<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
											</select>
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label">Fax</label>
											<input type="fax" class="form-control" name="fax" value="<?php echo e($customer->fax); ?>" placeholder="Fax..." />
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label">Email <span class="text-danger">*</span></label>
											<input type="email" class="form-control" name="email" value="<?php echo e($customer->email); ?>" placeholder="Email..." required />
										</div>
									</div>
								</div>
								<hr>
								<div class="row">
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label">Phone 1 <span class="text-danger">*</span></label>
											<input type="tel" class="form-control" name="phone1" value="<?php echo e($customer->telephone1); ?>" placeholder="Phone 1..." required />
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label">Phone 2</label>
											<input type="tel" class="form-control" name="phone2" value="<?php echo e($customer->telephone2); ?>" placeholder="Phone 2..." />
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label">Credit days</label>
											<input type="number" name="credit_day" class="form-control" />
										</div>
									</div>
									<div class="col-sm-3">
										<div class="form-group">
											<label class="control-label">Account Setting</label>
											<select class="form-control" name="account_id" data-placeholder>
												<?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
												<option value="<?php echo e($account->id); ?>" <?php echo e($account->id == $customer->account_status ? 'selected': ''); ?>><?php echo e($account->key); ?></option>
												<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
											</select>
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group" style="padding-top:40px">
											<label class="control-label"><input type="checkbox" value="1" name="active" <?php echo e($customer->active == 1 ? 'checked' : ''); ?> /> Is Customer Active?</label>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div id="add-certificate" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<form action="<?php echo e(route('add-customer-certification',['id'=>$customer->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add <?php echo e($customer->name); ?> Attachment </h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" name="name" placeholder="Attachment Name" class="form-control">

				</div>
				<div class="form-group">
					<label class="control-label">Attachment </label>
					<input type="file" name="certificate" class="form-control" placeholder="Choose File..." value="" required>
				</div>
				<div class="form-group">
					<label class="control-label">Start date</label>
					<input type="date" name="certification_date" value="" class="form-control" placeholder="Certificate Date..." required>
				</div>

				<div class="form-group">
					<label class="control-label">End Date</label>
					<input type="date" name="expired_date" class="form-control" placeholder="Expire Date..." value="" required>
				</div>
				<div class="form-group">
					<label class="control-label">Certification Body</label>
					<input type="text" name="certification_body" class="form-control" placeholder="Certification Body..." value="" required>
				</div>


			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="add-feedback" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="post" action="<?php echo e(route('customer-add-feedback')); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Feedback</h4>
			</div>
			<div class="modal-body">

				<div class="form-group">
					<label class="control-label">Status</label>
					<select name="priority" class="form-control" placeholder="Operator...">
						<option value=0>Active</option>
						<option value=1>Archived</option>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Date</label>
					<input type="date" name="date" placeholder="Complaint Date..." value="" class="form-control" required>
				</div>
				<div class="form-group">
					<label class="control-label">Feedback</label>
					<textarea class="form-control" rows="4" name="feedback" value="" placeholder="Feedback Description..." required></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="add-company-sample-point" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('add-sample-point')); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add <?php echo e(trim($customer->sample_point_configurable_name)!="" ? $customer->sample_point_configurable_name : 'Sample Point'); ?></h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label><?php echo e(trim($customer->unit_configurable_name)!="" ? $customer->unit_configurable_name : 'Unit'); ?></label>
					<select class="form-control" name="unit" placeholder="Select..." required>
						<option></option>
						<?php $__currentLoopData = $customer->units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($item->id); ?>"><?php echo e($item->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group">
					<div id="location-map" style="width: 100%;height:350px"></div>
				</div>
				<div class="form-group hidden">
					<label class="control-label">Latitude</label>
					<input type="text" class="form-control" name="lat" id="latitude" value="" placeholder="Name..." />
				</div>
				<div class="form-group hidden">
					<label class="control-label">Longitude</label>
					<input type="text" id="longitude" class="form-control" name="long" value="" placeholder="Name..." />
				</div>
				<div class="form-group">
					<label class="control-label"><input type="checkbox" value="1" name="active" checked /> Is Active?</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="add-company-unit" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('add-company-units', ['cust_id'=>$customer->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Company Unit</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label"><input type="checkbox" value="1" name="active" checked /> Is Active?</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="change-label-name" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('change-client-label-name', ['id'=>$customer->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Change Label Name</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" id="section-label-name" class="form-control" name="name" placeholder="Name..." required />
				</div>
				<input type="hidden" name="column" id="section-label-column" />
				<div class="form-group">
					<label class="control-label"><input type="checkbox" value="1" name="active" checked /> Is Active?</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="add-company-contacts" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('add-company-contacts', ['cust_id'=>$customer->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Company Contact</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Title *</label>
							<select name="title" class="form-control" placeholder="Title..." required>
								<option></option>
								<?php $__currentLoopData = getModulePreconfig("Designation", "Personnel-Management"); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($item->id); ?>"><?php echo e($item->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">First Name <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="first_name" value="" placeholder="First Name..." required />
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Middle Name</label>
							<input type="text" class="form-control" name="second_name" value="" placeholder="Middle Name..." />
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Surname</label>
							<input type="text" class="form-control" name="third_name" value="" placeholder="Surame..." />
						</div>
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Job Occupation</label>
							<input type="text" class="form-control" name="job_occupation" value="" placeholder="Job Title..." />
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Company Units <span class="text-danger">*</span> </label>
							<select required class="form-control" name="unit_name[]" multiple>
								<option value="">Select Company Unit...</option>
								<?php $__currentLoopData = $customer->units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($unit->name); ?>"><?php echo e($unit->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Email <span class="text-danger">*</span></label>
							<input type="email" class="form-control" name="email" value="" placeholder="Email..." required />
						</div>
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Telephone <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="telephone" value="" placeholder="Telephone..." required />
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label">Mobile</label>
							<input type="text" class="form-control" name="mobile" value="" placeholder="Mobile..." />
						</div>
					</div>

				</div>
				<hr>
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group" style="padding-top: 40px">
							<label class="control-label"><input type="checkbox" value="1" name="receive_price_list" /> Receives Pricelist?</label>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group" style="padding-top: 40px">
							<label class="control-label"><input type="checkbox" value="1" name="receive_report" /> Receives Report?</label>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group" style="padding-top: 40px">
							<label class="control-label"><input type="checkbox" value="1" name="receive_invoice" /> Receives Invoice?</label>
						</div>
					</div>

				</div>
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">
								<input type="checkbox" name="has_credential" value="1" /> Create/Update User Passwords
							</label>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group" style="padding-top: 40px">
							<label class="control-label"><input type="checkbox" value="1" name="active" /> Is Active?</label>
						</div>
					</div>
				</div>
				<div class="row hidden" id="password-holder">
					<div class="col-sm-4">
						<div class="form-group">
							<label>Password</label>
							<input autocomplete="off" type="password" value="" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control passed" name="main_passwords" placeholder="Password..." />
							<div class="has-success"></div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label>Confirm Password</label>
							<input type="password" value="" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control passed" name="confirm_passwords" placeholder="Confirm Password..." />
							<div class="has-error"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBqS4AEZ-gVeXjG794Rh0eTd6yvdfMKTjg&sensor=false" type="text/javascript"></script>
<script>
	$(function() {

		var map;
		$('#add-company-sample-point').on('show.bs.modal', function(e) {
			var mapProp = {
				center: new google.maps.LatLng(-1.247125519439578, 36.742261815816164),
				zoom: 5,
			};
			map = new google.maps.Map(document.getElementById("location-map"), mapProp);

			var marker = new google.maps.Marker({
				position: mapProp.center,
				// icon:'pinkball.png'
				draggable: true,
			});

			marker.setMap(map);
			marker.addListener('drag', function(event) {
				console.log('start')
				document.getElementById('latitude').value = event.latLng.lat();
				console.log(event.latLng.lat())
				document.getElementById('longitude').value = event.latLng.lng()
			});
			marker.addListener('dragend', function(event) {
				console.log('start2')
				document.getElementById('latitude').value = event.latLng.lat();
				console.log(event.latLng.lat())
				document.getElementById('longitude').value = event.latLng.lng()
				console.log(event.latLng.lng())
			});

		});
		$('#change-label-name').on('show.bs.modal', function(e) {
			//get data-id attribute of the clicked element
			var name = $(e.relatedTarget).data('current');
			var column = $(e.relatedTarget).data('column');

			$('#section-label-column').val(column);
			$('#section-label-name').val(name);
		});

		$('.pass').val('');
		$('[name="has_credentials"]').on('change', function() {
			if ($(this).is(':checked')) {
				$("#passwords-holder").removeClass("hidden");
				$(".pass").attr('required', true);
			} else {
				$('.pass').val('');
				$("#passwords-holder").addClass("hidden");
				$(".pass").removeAttr('required');
			}
		});

		$('[name="confirm_password"]').on('keyup', function() {
			var pass1 = $('[name="main_password"]').val();
			var pass2 = $(this).val();

			if (pass1 != pass2) {
				$(this).siblings('.has-success').html('').addClass('text-success');
				$(this).siblings('.has-error').html(`<i class="mdi mdi-cancel"></i> Passwords did not match.`).addClass('text-danger');
			} else {
				$(this).siblings('.has-error').html('')
				$(this).siblings('.has-success').html('<i class="mdi mdi-check-circle"></i> Passwords Match!.')
			}
		});
		// --------------------------
		$('.passed').val('');
		$('[name="has_credential"]').on('change', function() {
			if ($(this).is(':checked')) {
				$("#password-holder").removeClass("hidden");
				$(".passed").attr('required', true);
			} else {
				$('.passed').val('');
				$("#password-holder").addClass("hidden");
				$(".passed").removeAttr('required');
			}
		});

		$('[name="confirm_passwords"]').on('keyup', function() {
			var pass1 = $('[name="main_passwords"]').val();
			var pass2 = $(this).val();

			if (pass1 != pass2) {
				$(this).siblings('.has-success').html('').addClass('text-success');
				$(this).siblings('.has-error').html(`<i class="mdi mdi-cancel"></i> Passwords did not match.`).addClass('text-danger');
			} else {
				$(this).siblings('.has-error').html('')
				$(this).siblings('.has-success').html('<i class="mdi mdi-check-circle"></i> Passwords Match!.')
			}
		});
	});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/crm/show.blade.php ENDPATH**/ ?>