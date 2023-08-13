<?php $__env->startSection('title2'); ?>
  <title> <?php echo e(isset($batch->batch_code) ? $batch->batch_code." | Batch Info" : "New Batch"); ?></title>
	<style>
		.form-part-toggler{
			margin: 0px 0px 5px 0px !important;
			padding: 6px 6px 6px 6px;
			border-bottom: 1px solid rgba(0,0,0,0.09);
			cursor: pointer;
		}

		.form-part-toggler:hover{
			background-color: rgba(0,0,0,0.08);
		}

		#sample-detail-rows .form-group{
			display: none;
		}

		#sample-detail-rows tr.selected-row{
			background-color: #eef7d5;
		}
		#sample-detail-rows tr.selected-row td{
			border: none !important;
		}

		td .form-group {
			margin-bottom: unset !important;
		}

		#sample-detail-rows .text{
			display: unset;
		}

		#sample-detail-rows tr.editable .form-group{
			display: unset;
		}

		#sample-detail-rows tr.editable .text{
			display: none;
		}

		#sample-detail-rows tr{
			cursor: pointer;
		}

		.hidden{
			display: none;
		}

		.show-hoverable .complete{
			display: none;
		}
		.show-hoverable .partial{
			display: unset;
		}

		.show-hoverable:hover .partial{
			display: none;
		}
		.show-hoverable:hover .complete{
			display: unset;
		}
		.select2-selection{
			min-width: 200px !important;
		}
		.nav-item{
			margin-top: 10px;
		}

	</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
  <main>
		<?php
			$equipment_data = isset($batch->id) ? $batch->get_captured_tally() : array();

			$headerDetails = isset($batch->id) ? $batch->report_header_details() : array();

			$labStores = getStorageByType("lab_store");

			$reportingUnits = getReportingUnits();

			if($defaultClient){
				$customerDetails = App\Models\CRM\CRMCustomer::find($defaultClient);
				if($client_portal || Auth::user()->is_client == 1){
					$items = array(


						array(
							'link' => '/dashboard/crm/client-details',
							'name' => $customerDetails->name,
							'icon' => null
						),
						array(
							'link' => "#",
							'name' => "Customer Orders > ".(isset($batch->id) ? $batch->batch_code." - Order Info" : "Create New Order"),
							'icon' => null
						)
					);

				}else{

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
							'link' => route('show-customer', ['id'=>$defaultClient]),
							'name' => $customerDetails->name,
							'icon' => null
						),
						array(
							'link' => "#",
							'name' => "Customer Orders > ".(isset($batch->id) ? $batch->batch_code." - Order Info" : "Create New Order"),
							'icon' => null
						)
					);
				}
			}
			else{
				$items = array(
					array(
						'link' => route('lab-home'),
						'name' => 'Lab Management',
						'icon' => null
					),
					array(
						'link' => route('sample-workflow', ['status'=>'All Samples']),
						'name' => 'Sample Workflow',
						'icon' => null
					),
					array(
						'link' => route('sample-workflow', ['status'=>$batch->status ?? 'Samples Reception']),
						'name' => $batch->status ?? 'Samples Reception',
						'icon' => null
					),
					array(
						'link' => '#',
						'name' =>  isset($batch->batch_code) ? $batch->batch_code." - Batch Info" : "New Batch",
						'icon' => null
					)
				);
			}
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
    <h4 class="pt-4 pr-4 pl-4 pb-3">

			<i class="mdi mdi-layers-triple"></i> <span class="badge badge-pill bg-white pt-2 pb-2 pr-3 pl-3" style="font-weight: 400!important"><?php echo isset($batch->priority) && $batch->priority != "Normal" ? '<i class="mdi mdi-star text-danger"></i>' : ''; ?> <?php echo e($batch->priority ?? ''); ?></span>
			<?php echo e(isset($batch->batch_code) ? $batch->batch_code.' Batch Info' : 'New Batch'); ?> <small class="text-muted"> <?php echo isset($batch->batch_code) ? '<i class="mdi mdi-sitemap"></i> '.$batch->tracking_stage()->name : ''; ?></small>
			<?php if(isset($batch->status) && $batch->status=="Samples Request Review"): ?>
				<!-- <button class="btn btn-outline-primary btn-sm float-right"  data-target="#dispatch-to-labs-modal" data-toggle="modal" title="Approve Request"><i class="mdi mdi-clipboard-arrow-right"></i> Approve Request</button> -->
			<?php endif; ?>
			<?php if(isset($batch->status) && $batch->status=="Samples In Lab" && Auth::user()->is_client == 0): ?>
				
					<button class="btn btn-outline-danger btn-sm float-right" data-target="#send-to-verification-modal" data-toggle="modal" title="Send To Verification"><i class="mdi mdi-check-decagram"></i> Send To Verification</button>
				
			<?php endif; ?>
			<?php if(isset($batch->status) && in_array($batch->status, array("Sample Verification","Sample Approval","Reports to Email","Payments")) && Auth::user()->is_client == 0): ?>
				<?php if($batch->status == "Sample Verification"): ?>
					<?php if($batch->processed_results()->count() > 0): ?>
						<button class="btn btn-outline-primary btn-sm float-right ml-1" data-target="#send-for-approval-modal" data-toggle="modal" title="Send for Approval"><i class="mdi mdi-check-decagram"></i> Send for Approval</button>
						<?php
						$path = '/storage'.$batch->batch_report_url;
							?>
						<a href="<?php echo e($path); ?>" target="_blank" class="float-right ml-1  btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i> View Report</a>
					<?php else: ?>
						<button class="btn btn-outline-primary btn-sm float-right" disabled ><i class="mdi mdi-check-decagram"></i> Send for Approval</button>
					<?php endif; ?>
					<button class="btn btn-outline-success btn-sm float-right"  data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-file-cog-outline"></i> Process Results</button>
				<?php endif; ?>
				<?php if($batch->status == "Sample Approval"): ?>
					<?php if($batch->batch_report_url != '' && $batch->approve_user_id !== '' ): ?>)
						<button class="btn btn-primary btn-sm float-right ml-1" data-target="#send-to-email-modal" data-toggle="modal" title="Send to Email"><i class="mdi mdi-email"></i> Send to Email</button>

						<button class="btn btn-outline-primary btn-sm float-right ml-1" data-target="#send-to-payments-modal" data-toggle="modal" title="Send to Payment"><i class="mdi mdi-credit-card-outline"></i> Send to Payment</button>
						<?php
							$path = '/storage'.$batch->batch_report_url;
						?>
						<a href="<?php echo e($path); ?>" target="_blank" class="float-right ml-1 btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i> View Report</a>
					<?php else: ?>
						<button class="btn btn-primary btn-sm float-right ml-1" data-target="#prompt-report-modal" data-toggle="modal" disabled title="Send to Email"><i class="mdi mdi-email"></i> Send to Email</button>
						<button class="btn btn-outline-dark btn-sm float-right ml-1" data-target="#prompt-report-modal" data-toggle="modal" title="Send to Payment"><i class="mdi mdi-credit-card-outline"></i> Send to Payment</button>
					<?php endif; ?>
					
					<a href="<?php echo e(route('approve-batch-analysis',['id'=>$batch->id])); ?>" class="btn btn-default btn-sm float-right ml-1" style="background-color: white;"><i class="mdi mdi-check-circle"></i> Approve</a>
					<button class="btn btn-outline-success btn-sm float-right" data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-file-cog-outline"></i> Process Results</button>

					

				<?php endif; ?>
				<?php if($batch->status == 'Reports to Email' || $batch->status == 'Payments'): ?>
				<?php 
					$path = '/storage'.$batch->batch_report_url;
				?>
				<a href="<?php echo e($path); ?>" target="_blank" class="float-right ml-1 btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i> View Report</a>
				<?php endif; ?>
				<?php if($batch->status == 'Payments'): ?>
				<button class="btn btn-primary btn-sm float-right ml-1" data-target="#send-to-email-modal" data-toggle="modal" title="Send to Email"><i class="mdi mdi-email"></i> Send to Email</button>
				<?php endif; ?>
				
				
				<!-- <button class="btn btn-outline-success btn-sm float-right" data-target="#process-results-modal" data-toggle="modal" title="Process Results"><i class="mdi mdi-file-cog-outline"></i>  $batch->status == "Sample Verification" ? "Process Results" : "" }}</button> -->
				<!-- <a href="<?php echo e(route('certificate-analysis',['id'=>$batch->id])); ?>" class="btn btn-outline-warning btn-sm mr-1 float-right"> Certificate of Analysis</a> -->
				<?php endif; ?>


			<?php if(isset($batch->id) && !$defaultClient): ?>
				<div class="btn-group mt-2">
					<button class="btn btn-transparent btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="mdi mdi-swap-vertical"></i> Move To Workflow
					</button>
					<div class="dropdown-menu" id="status-selector">
						<?php $__currentLoopData = getSampleWorflowStages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<form class="dropdown-item" method="POST" style="cursor: pointer" action="<?php echo e(route('move-to-workflow', ['status'=>$item, 'batch_id'=>$batch->id])); ?>">
								<?php echo csrf_field(); ?>
								<small class="text-muted"><i class="mdi mdi-subdirectory-arrow-right"></i></small> <?php echo e($item); ?>

							</form>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</div>
				</div>
				<div class="btn-group mt-2">
					<button class="btn btn-transparent btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="mdi mdi-swap-vertical"></i> Move To Stage
					</button>
					<div class="dropdown-menu" id="stage-selector">
						<?php $__currentLoopData = getWorkflowStage_Stages($batch->status); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<form class="dropdown-item" method="POST" style="cursor: pointer" action="<?php echo e(route('move-to-stage', ['stage'=>$item->id, 'batch_id'=>$batch->id])); ?>">
								<?php echo csrf_field(); ?>
								<small class="text-muted"><i class="mdi mdi-subdirectory-arrow-right"></i></small> <?php echo e($item->name); ?>

							</form>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</div>
				</div>
			<?php endif; ?>

		</h4>
		<div class="pl-2 pr-2 pb-3 row">
			<?php if($batch->specialist_analyst ?? ''): ?>
				<div class="col-sm-4" style="font-size: 16px">
					<span class="badge bg-white badge-pill p-2" style="margin-right: 5px">
						<i class="mdi mdi-account"></i> SPECIALIST ANALYST
					</span> <?php echo e($batch->specialist_analyst->name); ?>

				</div>
			<?php endif; ?>
			<?php if(isset($batch->id) && $batch->get_request_types()->count() > 0): ?>
				<?php
					$types = $batch->get_request_types();

					$arrT = array();

					foreach ($types as $type) {
						$arrT[] = $type->name;
					}
				?>
				<div class="col-sm-8" style="font-size: 14px">
					<span class="badge bg-white badge-pill p-2" style="font-size: 13px; margin-right: 5px">
						<i class="mdi mdi-beaker-question"></i> REQUEST TYPE
					</span> <?php echo e(implode(',', $arrT)); ?>

				</div>
			<?php endif; ?>
		</div>
    <div class="row no-gutters">
      <div class="col-sm-4 p-2">
        <div class="card">
          <div class="card-body">
						<h5 class="card-title">
							<i class="mdi mdi-pencil-outline"></i> Batch Info
							<?php if(isset($batch->report_file_path) && trim($batch->report_file_path) != ""): ?>
								<a class="float-right btn btn-outline-danger btn-sm rounded-pill pl-3 pr-3" href="<?php echo e($batch->report_file_path); ?>">
									<i class="mdi mdi-download"></i> View Report
								</a>
							<?php endif; ?>
						</h5>
						<hr>
						<form action="<?php echo e(route('add-batch-info', ['batch'=>$batchID])); ?>" method="POST">
							<?php $maxDate = getTodayDate(); ?>
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<label class="control-label">Lab Receiption Date <span class="text-danger">*</span> </label>
								<input type="date" max="<?php echo e($maxDate); ?>" placeholder="Lab Receiption Date" value="<?php echo e($batch->receipt_date ?? ''); ?>" class="form-control " name="receipt_date" <?php echo e($defaultClient === false ? 'required' : ''); ?> autocomplete="off">
								
							</div>
							<div class="form-group">
								<label class="control-label">Date Collected <span class="text-danger">*</span></label>
								<input type="date" max="<?php echo e($maxDate); ?>" placeholder="Lab Receiption Date" value="<?php echo e($batch->date_collected ?? ''); ?>" class="form-control " name="date_collected" required>
								
							</div>

							<div class="form-group <?php echo e(isset($batch->id) ? ( $batch->status == 'Samples In Lab' ? 'hidden' : '') : ''); ?>">
														
								<label  class="control-label">Client <span class="text-danger">*</span></label>
								<select class="form-control <?php echo e($defaultClient === false ? '' :'no-select2'); ?> <?php echo e(isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'no-select2' : ''); ?>" <?php echo e(isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'readonly' : ''); ?> name="crm_customer_id" id="client-select" required onchange="detectChange(this)" <?php echo e($defaultClient === false ? '' :'readonly'); ?>>
									<option value="">Select Client...</option>
									<?php $__currentLoopData = getClients(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										 <?php if($defaultClient === false): ?> 
											<option value="<?php echo e($client->id); ?>"  <?php echo e(isset($batch->crm_customer_id) && $batch->crm_customer_id == $client->id ? 'selected' : ''); ?>  <?php echo e($defaultClient == $client->id ? 'selected' : ''); ?> 
												data-units="<?php echo e(json_encode($client->units)); ?>"
												data-quote="<?php echo e(json_encode($client->quotes)); ?>"
												data-unit_name='<?php echo e(trim($client->unit_configurable_name) == '' ? 'Site Location' : $client->unit_configurable_name); ?>'
												data-sample_point_name='<?php echo e(trim($client->sample_point_configurable_name)  == '' ? 'Sample Point' : $client->sample_point_configurable_name); ?>'
												data-product_name='<?php echo e(trim($client->product_configurable_name) == '' ? 'Product' : $client->product_configurable_name); ?>'><?php echo e($client->name); ?></option>
										<?php endif; ?>

										<?php if($defaultClient !== false && $client->id == $defaultClient): ?>
											<option value="<?php echo e($client->id); ?>"  <?php echo e(isset($batch->crm_customer_id) && $batch->crm_customer_id == $client->id ? 'selected' : ''); ?>  <?php echo e($defaultClient == $client->id ? 'selected' : ''); ?> 
												data-units="<?php echo e(json_encode($client->units)); ?>"
												data-unit_name='<?php echo e(trim($client->unit_configurable_name) == '' ? 'Unit' : $client->unit_configurable_name); ?>'
												data-sample_point_name='<?php echo e(trim($client->sample_point_configurable_name)  == '' ? 'Sample Point' : $client->sample_point_configurable_name); ?>'
												data-product_name='<?php echo e(trim($client->product_configurable_name) == '' ? 'Product' : $client->product_configurable_name); ?>'><?php echo e($client->name); ?></option>
										<?php endif; ?>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select>
								<?php if($defaultClient !== false): ?>
									<input type="hidden" name="is_client_order" value="1" />
								<?php endif; ?>
								
							</div>
							<div class="form-group">
								<label class="control-label"><span class='client-prefered-unit-name'>Site Location</span> <span class="text-danger">*</span></label>
								<select class="form-control <?php echo e(isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'no-select2' : ''); ?>" <?php echo e(isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'readonly' : ''); ?> name="crm_unit_name" data-selected='<?php echo e($batch->crm_unit_name ?? ''); ?>' id="client-unit-select" required>
									<option value="">Select Client Unit...</option>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label text-sm">Sample Type <span class="text-danger">*</span></label>
								<select class="form-control <?php echo e(isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'no-select2' : ''); ?>" <?php echo e(isset($batch->status) && !in_array($batch->status, array("Samples Reception", "Samples En-Route")) ? 'readonly' : ''); ?> name="sample_type_id" required id="batch-info-sample-type">
									<option value="">Select Sample Type...</option>
									<?php $__currentLoopData = getSampleTypes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sample): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<option value="<?php echo e($sample->id); ?>"  <?php echo e(isset($batch->sample_type_id) && $batch->sample_type_id == $sample->id ? 'selected' : ''); ?> data-conditions="<?php echo e(json_encode($sample->sample_condition)); ?>"><?php echo e($sample->name); ?></option>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select>
							</div>
							<div class="form-group btn-group-sm">
								<label class="control-label">RFT Form No <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="reference_number" value="<?php echo e($batch->reference_number ?? ''); ?>" placeholder="Reference Number..." required />
							</div>
							<!-- <div class="form-group btn-group-sm">
								<label class="control-label">Ammendment Number</label>
								<input type="text" class="form-control" name="document_number" value="<?php echo e($batch->document_number ?? ''); ?>" placeholder="Ammendment Number..." />
							</div> -->
							<?php if(isset($batch->id)): ?>
							<div class="form-group clients-data">
								<label class="control-label">Quotation</label>

								<select name="quote_id" id="select-quotation" class="form-control">
									<option value="">Select Quote</option>
									<?php $quotes = getCustomerQuote($batch->crm_customer_id)?>
									<?php $__currentLoopData = $quotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($quote->id); ?>" <?php echo e($quote->id == $batch->quote_id ? 'selected':''); ?>><?php echo e($quote->quote_number); ?></option>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select>

							</div>
							<?php endif; ?>
							<div class="form-group btn-group-sm">
								<label class="control-label">Goods Description</label>
								<textarea class="form-control" name="description" placeholder="Description..."><?php echo e($batch->description ?? ''); ?></textarea>
							</div>

							<div class="form-group btn-group-sm">
									<label class="control-label">Sampled By</label>
									<input type="text" name="sample_by" value="<?php echo e($batch->sampling_officer_name ?? ''); ?>" id="" placeholder="Sampled By..." class="form-control">
									<!-- <select name="sampling_officer" class="form-control" placeholder="Select Sampling Officer...">
										<option></option>
										<?php $__currentLoopData = getUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($item->id); ?>" <?php echo e(isset($item->id) && $item->id == ($batch->sampling_officer ?? 0) ? 'selected' : ''); ?>><?php echo e($item->name); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select> -->
								</div>
								<div class="form-group btn-group-sm">
								<label class="control-label">Submitted By</label>
								<input type="text" class="form-control" value="<?php echo e($batch->submit_by ?? ''); ?>" name="submit_by" value="<?php echo e($batch->submit_by ?? ''); ?>" placeholder="Submitted By..." />
							</div>
							<div class="form-group btn-group-sm">
								<label class="control-label">Received By</label>
								<input type="text" name="receive_by" class="form-control" value="<?php echo e($batch->receiving_officer_name ?? ''); ?>" placeholder="Received By..." id="" class="form-control">
								<!-- <select name="receiving_officer" class="form-control" placeholder="Select Receiving Officer...">
									<option></option>
									<?php $__currentLoopData = getUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<option value="<?php echo e($item->id); ?>" <?php echo e(isset($item->id) && $item->id == ($batch->receiving_officer ?? 0) ? 'selected' : ''); ?>><?php echo e($item->name); ?></option>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select> -->
							</div>


							
							<div class="form-group btn-group-sm">
								<label class="control-label"><?php $active_company = getActiveCompany()?>
									<input type="checkbox" name="agreement" value="1" <?php echo e(isset($batch->user_agreement) && $batch->user_agreement == 1 ? 'checked' : ''); ?>> I agree to <?php echo e($active_company->name); ?> terms and conditions?
								</label>
							</div>
							<div class="form-group btn-group-sm">
								<label class="control-label"><?php $active_company = getActiveCompany()?>
									<input type="checkbox" name="sampled_by_company_personnel" value="1" <?php echo e(isset($batch->sampled_by_company_personnel) && $batch->sampled_by_company_personnel == 1 ? 'checked' : ''); ?>> Sampled by <?php echo e($active_company->name); ?> personnel?
								</label>
							</div>
							
								<!-- <div class="form-group btn-group-sm">
								<label class="control-label">
									<input type="checkbox" name="is_ammendment" value="1" <?php echo e(isset($batch->is_ammendment) && $batch->is_ammendment > 0 ? 'checked' : ''); ?>> Is Ammendment?
								</label>
							</div> -->
							<div class="btn btn-default btn-sm text-primary btn-block toggle-more-fields mb-1">
								<i class="mdi mdi-chevron-double-down"></i> More Fields
							</div>
							<div id="more-fields" class="hidden">
							<div class="form-group btn-group-sm">
								<label class="control-label">
									<input type="checkbox" name="is_routine" value="1" <?php echo e(isset($batch->is_routine) && $batch->is_routine == 1 ? 'checked' : ''); ?>> Is Routine?
								</label>
							</div>
							<div class="form-group btn-group-sm <?php echo e(isset($batch->is_routine) && $batch->is_routine == 1 ? '' : 'hidden'); ?>" id="routine_frequency">
									<label class="control-label">Routine Frequency <small class="text-danger">*required for routine sample</small></label>
									<select name="routine_frequency" class="form-control">
										<option value="">Select Frequency</option>
										<?php $__currentLoopData = getRoutineFrequency(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($k); ?>" <?php echo e(isset($batch->routine_frequency) && $batch->routine_frequency == $k ? 'selected' : ''); ?>><?php echo e($item); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
							<div class="form-group">
								<label class="control-label">Date Expected</label>
								<input type="date" placeholder="Date Expected" value="<?php echo e($batch->date_expected ?? ''); ?>" class="form-control " name="date_expected">
							</div>
							<div class="form-group btn-group-sm">
								<label class="control-label">Customer Address</label>
								<textarea class="form-control" name="importer_address" placeholder="Customer Address..."><?php echo e($batch->importer_address ?? ''); ?></textarea>
							</div>
							<div class="form-group btn-group-sm">
								<label class="control-label">Radioactive Level</label>
								<input type="text" class="form-control" name="radio_active_levels" value="<?php echo e($batch->radio_active_levels ?? ''); ?>" placeholder="Radioactive Level..." />
							</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Office REF</label>
									<input type="text" class="form-control" name="kra_office_ref" value="<?php echo e($batch->kra_office_ref ?? ''); ?>" placeholder="Office REF..." />
								</div>

								<div class="form-group btn-group-sm">
									<label class="control-label">Office/Station</label>
									<input type="text" class="form-control" name="kra_office_station" value="<?php echo e($batch->kra_office_station ?? ''); ?>" placeholder="Office/Station..." />
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">How Sample was Obtained</label>
									<textarea class="form-control" name="how_sample_was_obtained" placeholder="How Sample was Obtained..."><?php echo e($batch->how_sample_was_obtained ?? ''); ?></textarea>
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Where Sample was Obtained</label>
									<input type="text" class="form-control" name="where_sample_was_obtained" value="<?php echo e($batch->where_sample_was_obtained ?? ''); ?>" placeholder="Where Sample was Obtained..." />
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Declared Commodity Code</label>
									<input type="text" class="form-control" name="declared_commodity_code" value="<?php echo e($batch->declared_commodity_code ?? ''); ?>" placeholder="Declared Commodity Code..." />
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Declared Amount</label>
									<input type="text" class="form-control" name="declared_amount" value="<?php echo e($batch->declared_amount ?? ''); ?>" placeholder="Declared Amount..." />
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Net Quantity and Unit of Quantity</label>
									<input type="text" class="form-control" name="net_quantity_and_unit_of_quantity" value="<?php echo e($batch->net_quantity_and_unit_of_quantity ?? ''); ?>" placeholder="Net Quantity and Unit of Quantity..." />
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Use of Goods</label>
									<textarea class="form-control" name="use_of_goods" placeholder="Use of Goods..."><?php echo e($batch->use_of_goods ?? ''); ?></textarea>
								</div>
								<div class="form-group btn-group-sm">
									<label class="control-label">Sample Appearance Description</label>
									<textarea class="form-control" name="sample_appearance_description" placeholder="Sample Appearance Description..."><?php echo e($batch->sample_appearance_description ?? ''); ?></textarea>
								</div>

							</div>
							<div class="form-group">
								<?php if(Auth::user()->is_client == 1 && isset($batch->status) && $batch->status != 'Samples En-Route'): ?>
								<?php else: ?>
									<?php if(isset($batch->id) && $batch->status == 'Samples In Lab'): ?>
									<?php else: ?>
										<button class="btn btn-outline-primary btn-lg btn-block">
											<i class="mdi mdi-content-save"></i> Save
										</button>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</form>
          </div>
        </div>
      </div>
      <div class="col-sm-8 p-2">
				<?php if(isset($batch->id)): ?>
				<?php if(isset($batch->status) && $batch->status == 'Samples In Lab'): ?>
				<span class="btn btn-outline-primary btn-sm float-right mb-2" data-toggle="modal" data-target="#import-results"><i class="mdi mdi-file-import-outline"></i> Import Results</span>
				<?php endif; ?> 
				<div class="card mb-2" style="clear: both;">
					<div class="card-header">
						<header style="font-size: large"><i class="mdi mdi-calendar-month"></i> Batch Dates</header>
					</div>
					<div class="card-body">
						<div class="row no-gutters">
							<?php $__currentLoopData = getSampleDateTypes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<?php if($date == 'Login Date' || $date == 'Target Date' || $date == 'Processing Date'): ?>
								<div class="col-sm-4 p-1">
									<b style="color: rgb(68, 68, 68)"><i class="mdi mdi-calendar-outline"></i> <?php echo e($date); ?></b>
									<span class="float-right mr-2"
										style="padding: 3px 9px; font-size:12px; border-radius: 15px; background-color: #f0f0f0; border: 1px solid #eeeeee; color:rgb(68, 68, 68)"><?php echo e($batch->get_date($date) ? date('Y-m-d', strtotime($batch->get_date($date)['date'])) : '-'); ?></span>
								</div>
								<?php endif; ?>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						</div>
					</div>
				</div>
				<?php endif; ?>
        <div class="card tab-card">
          <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="analyte-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="samples-tab" data-toggle="tab" href="#samples" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-snowflake"></i> Samples</a>
							</li>
							<?php if(isset($batch->id)): ?>
								
								<?php if(Auth::user()->is_client == 0): ?>
								<?php if(in_array($batch->status, array("Sample Approval","Sample Verification", "Payments", "Reports"))): ?>
								<!-- <li class="nav-item">
									<a class="nav-link" id="processed-results-tab" data-toggle="tab" href="#processed-results" role="tab" aria-controls="Processed-Results" aria-selected="true"><i class="mdi mdi-clipboard-text"></i> Processed Results</a>
								</li> -->
								<?php endif; ?>
								<li class="nav-item">
									<a class="nav-link" id="notes-tab" data-toggle="tab" href="#notes-reminders" role="tab" aria-controls="Notes" aria-selected="true"><i class="mdi mdi-android-messages"></i> Notes <span class="badge badge-pill badge-primary"><?php echo e(isset($batch->comments) ? count($batch->comments) : 0); ?></span></a>
								</li>
								<li class="nav-item">
									<a class="nav-link" id="chain-of-custody-tab" data-toggle="tab" href="#chain-of-custody" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-sitemap"></i> Chain of Custody</a>
								</li>
								
								<li class="nav-item">
									<a class="nav-link" id="ammendment-tab" data-toggle="tab" href="#ammendment" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-file-document-edit"></i> Amendment</a>
								</li>
								
								
								<?php if(in_array($batch->status, array("Sample Approval","Samples In Lab", "Sample Verification"))): ?>
								<li class="nav-item">
									<a class="nav-link" id="data-from-equipment-results-tab" data-toggle="tab" href="#data-from-equipment-results" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-file-cog"></i> Equipment Data</a>
								</li>
								<?php endif; ?>
								
								<?php endif; ?>
								<li class="nav-item">
									<a class="nav-link" id="attachment-tab" data-toggle="tab" href="#Attachment" role="tab" aria-controls="Custody" aria-selected="true"><i class="mdi mdi-attachment"></i> Attachments</a>
								</li>
							<?php endif; ?>
              
            </ul>
          </div>
          <div class="tab-content" id="analyte-tabs-content">
						<?php if(isset($batch->id)): ?>
							<?php if(in_array($batch->status, array("Sample Approval", "Samples In Lab", "Sample Verification"))): ?>
							<div class="tab-pane fade p-3" id="data-from-equipment-results" role="tabpanel" aria-labelledby="one-tab">
									<h5 class="p-2">
										Equipment Data
										
									</h5>
									<div class="card tab-card">
										<div class="card-header tab-card-header">
											<ul class="nav nav-tabs card-header-tabs" id="equipment-data-results-header" role="tablist">
												<li class="nav-item">
												<a href="#not-processed-tab"  data-toggle="tab" role="tab" aria-selected="true" class="nav-link active" id="not-processed"><i class="mdi mdi-content-save-cog"></i> Equipment Data</a>
												</li>
												<li class="nav-item">
													<a href="#duplicate-tab"  data-toggle="tab" role="tab" aria-selected="true" class="nav-link" id="duplicate-tab-content"><i class="mdi mdi-content-duplicate"></i> Raw Data</a>
												</li>
												<li class="nav-item">
													<a href="#processed-tab"  data-toggle="tab" role="tab" aria-selected="true" class="nav-link" id="not-processed"><i class="mdi mdi-file-import-outline"></i> Imported Excels</a>
												</li>
											</ul>
										</div>
										<div class="tab-content" id="equipment_data">
											<div class="tab-pane fade show active p-3" id="not-processed-tab" role="tabpanel" aria-labelledby="one-tab">
												<h5 class="p-2">
													<i class="mdi mdi-content-save-cog"></i> Equipment Data
													
												</h5>
												<div class="table-responsive">
													<table class="table table-condensed table-hover table-bordered table-stripped table-sm" style="width:160% !important">
														<thead class="bg-light p-2">
															<tr>
																<th></th>
																<th>Analyte Name</th>
																<th>Well ID</th>
																<th>Sample No</th>
																<th>Well</th>
																<th>Results</th>
																<th>Mean</th>
																<th>Std Dev</th>
																<th>CV%</th>
																<th>Status</th>	
																<th>Remark</th>
																<th>Date of Reading</th>
																<th>Testing Week</th>
																<th>Analyte Code</th>

															</tr>
														</thead>
														<tbody>
															<?php $__currentLoopData = $selected_results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
															<tr>
																<td>
																	<?php echo e($loop->iteration); ?>

																</td>
																<td style="min-width: 150px;"><?php echo e($sr->analyte_name); ?></td>
																<td><?php echo e($sr->well_id); ?></td>
																<td><?php echo e($sr->sample_no); ?></td>
																<td><?php echo e($sr->well); ?></td>
																<td><?php echo e($sr->result); ?></td>
																<td><?php echo e($sr->mean); ?></td>
																<td><?php echo e($sr->std_dev); ?></td>
																<td><?php echo e($sr->cv); ?></td>
																<td><?php echo e($sr->remark); ?></td>
																<td><span class="badge badge-pill badge-primary  <?php echo e($sr->remark_data_class); ?> text-dark"><?php echo e($sr->remark_data); ?></span></td>
																<td><?php echo e($sr->date_of_reading); ?></td>
																<td><?php echo e($sr->test_week); ?></td>
																<td><?php echo e($sr->analyte_code); ?></td>
															</tr>
															<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
														</tbody>
													</table>
												</div>
											</div>
											<div class="tab-pane fade p-3" id="duplicate-tab" role="tab-panel" aria-labelledby="one-tab">
												<h5 class="p-2">
												<i class="mdi mdi-content-duplicate"></i> Raw Data
												<span class="float-right btn btn-outline-warning btn-sm" id="process-data" data-target="#proccess-lab-results" data-toggle="modal"><i class="mdi mdi-file-refresh"></i> Process Data</span>
												</h5>
												<form action="" method="post">
													<div class="table-responsive">
														<table class="table table-condensed table-hover table-bordered table-stripped table-sm" style="width:160% !important">
															<thead class="bg-light p-2">
																<tr>
																	<th></th>
																	<th>Analyte Name</th>
																	<th>Well ID</th>
																	<th>Sample No</th>
																	<th>Well</th>
																	<th>Results</th>
																	<th>Mean</th>
																	<th>Std Dev</th>
																	<th>CV%</th>
																	<th>Status</th>	
																	<th>Remark</th>
																	<th>Date of Reading</th>
																	<th>Testing Week</th>
																	<th>Analyte Code</th>
																	
																</tr>
															</thead>
															<tbody>
																<?php $__currentLoopData = $duplicate_results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																<tr>
																	<td>
																	<div class="form-group">
																		<label class="control-label"><input type="checkbox" id="is-selected" name="is_selected[]" value="<?php echo e($dr->id); ?>" <?php echo e($dr->is_selected == 1 ? 'checked' : ''); ?>  /></label>
																	</div>
																	</td>
																	<td style="min-width: 150px;"><?php echo e($dr->analyte_name); ?></td>
																	<td><?php echo e($dr->well_id); ?></td>
																	<td><?php echo e($dr->sample_no); ?></td>
																	<td><?php echo e($dr->well); ?></td>
																	<td><?php echo e($dr->result); ?></td>
																	<td><?php echo e($dr->mean); ?></td>
																	<td><?php echo e($dr->std_dev); ?></td>
																	<td><?php echo e($dr->cv); ?></td>
																	<td><?php echo e($dr->remark); ?></td>
																	<td><span class="badge badge-pill badge-primary  <?php echo e($dr->remark_data_class); ?> text-dark"><?php echo e($dr->remark_data); ?></span></td>
																	<td><?php echo e($dr->date_of_reading); ?></td>
																	<td><?php echo e($dr->test_week); ?></td>
																	<td><?php echo e($dr->analyte_code); ?></td>
																</tr>
																<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
															</tbody>
														</table>
													</div>

												</form>
											</div>
											<div class="tab-pane fade p-3" id="processed-tab" role="tabpanel" aria-labelledby="one-tab">
												<h5 class="p-2">
												   <i class="mdi mdi-file-import-outline"></i> Imported Excels
												</h5>
												<div class="table-responsive">
													<table class="table table-condensed table-hover table-bordered table-stripped table-sm">
														<thead class="bg-light p-2">
															<tr>
																<th></th>
																<th>Date of Reading</th>			
																<th>Testing Week</th>
																<th>Import User</th>
																<th>No of Strips</th>
																<th>Analyte/Virus</th>
																<th>File</th>
																
															</tr>
														</thead>
														<tbody>
															<?php $__currentLoopData = $excels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $excel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																<tr>
																	<td><?php echo e($loop->iteration); ?></td>
																	<td><?php echo e($excel->date_of_reading); ?></td>
																	<td><?php echo e($excel->test_week); ?></td>
																	<td><?php echo e($excel->user_name); ?></td>
																	<td><?php echo e($excel->strip_name); ?></td>
																	<td style="min-width: 150px;" ><?php echo e($excel->analyte_name ?? '-'); ?></td>
																	<td class="text-center"><a  href="<?php echo e($excel->excel_url); ?>" target="_blank"><i class="mdi mdi-download"></i> Download</a></td>
																</tr>
															<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php endif; ?>
							<?php if(in_array($batch->status, array("Sample Approval","Sample Verification", "Payments", "Reports"))): ?>
								<div class="tab-pane fade p-3" id="processed-results" role="tabpanel" aria-labelledby="one-tab">
									<div class="p-2 row">
										<div class="col-sm-8">
											<h5><i class="mdi mdi-clipboard-text"></i> Processed Results</h5>
										</div>
									</div>
									<div class="table-responsive">
										<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
											<thead class="bg-light p-2">
												<tr>
													<th>Analyte</th>
													<th>Sample Code</th>
													<th nowrap>Result</th>
													<th nowrap>Reporting Unit</th>
													<th>Analyst</th>
													<th>Equipment</th>
												</tr>
											</thead>
											<tbody>
												<?php $__currentLoopData = $batch->processed_results(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
													<tr>
														<td><?php echo e($res->analyte_code); ?></td>
														<td><?php echo e($res->sample_detail_code); ?></td>
														<td><?php echo e($res->reporting_symbol."".$res->result); ?></td>
														<td><?php echo e($res->unit_code); ?></td>
														<td><?php echo e($res->operator); ?></td>
														<td><?php echo e($res->equipment); ?></td>
													</tr>
												<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
											</tbody>
										</table>
									</div>
								</div>
							<?php endif; ?>
							<div class="tab-pane fade p-3" id="chain-of-custody" role="tabpanel" aria-labelledby="one-tab">
								<div class="p-2 row">
									<div class="col-sm-8">
										<h5><i class="mdi mdi-sitemap"></i> Chain of Custody</h5>
									</div>
								</div>
								<div class="table-responsive">
									<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
										<thead class="bg-light p-2">
											<tr>
												<th>No</th>
												<th>Workflow</th>
												<th nowrap>Tracking Stage</th>
												<th nowrap>Started By</th>
												<th nowrap>Start Date</th>
												<th nowrap>Completed By</th>
												<th nowrap>Complete Date</th>
												<th nowrap>Comments</th>
											</tr>
										</thead>
										<tbody>
											<?php $__currentLoopData = $batch->custody; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
												<tr>
													<td nowrap><?php echo e($loop->iteration); ?></td>
													<td nowrap><?php echo e($c->workflow_stage); ?></td>
													<td nowrap><?php echo e($c->tracking_stage->name ?? '-'); ?></td>
													<td nowrap><?php echo e($c->started_by->name ?? '-'); ?></td>
													<td nowrap><?php echo e($c->created_at ?? '-'); ?></td>
													<td nowrap><?php echo $c->completed_by->name ?? '<i class="mdi mdi-timer-sand text-warning"  style="font-size: 16px!important"></i>'; ?></td>
													<td nowrap><?php echo $c->moved_out_date ?? '<i class="mdi mdi-timer-sand text-warning" style="font-size: 16px!important"></i>'; ?></td>
													<td><?php echo e($c->comments != '' ? $c->comments : '-'); ?></td>
												</tr>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										</tbody>
									</table>
								</div>
							</div>
							<div class="tab-pane fade p-3" id="notes-reminders" role="tabpanel" aria-labelledby="one-tab">
								<div class="p-2 row">
									<div class="col-sm-8">
										<h5><i class="mdi mdi-android-messages"></i> Notes & Reminders</h5>
									</div>
									<div class="col-sm-4 align-content-center">
										<?php if(Auth::user()->is_client == 0): ?>
										<span class="btn btn-primary float-right btn-sm" data-target="#add-sample-notes" data-toggle="modal">
											<i class="mdi mdi-message-plus-outline"></i> Add
										</span>
										<?php endif; ?>
									</div>
								</div>
								<div class="table-responsive">
									<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
										<thead class="bg-light p-2">
											<tr>
												<th>No</th>
												<th nowrap>Sender</th>
												<th nowrap>Receiver</th>
												<th nowrap>Type</th>
												<th nowrap>Other Users</th>
												<th nowrap>Status</th>
												<th nowrap>Comment</th>
												<th></th>
											</tr>
										</thead>
										<tbody>
											<?php $__currentLoopData = $batch->comments ?? array(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
												<tr>
													<td><?php echo e($loop->iteration); ?></td>
													<td><?php echo e($item->creator->name ?? '-'); ?></td>
													<td><?php echo e($item->reminder_for()->name ?? '-'); ?></td>
													<td><?php echo e($item->comment_type); ?></td>
													<td>
														<?php $__currentLoopData = $item->people_to_cc()['names']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
															<small class="mr-1"><i class="mdi mdi-account"></i> <?php echo e($p); ?></small>
														<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
													</td>
													<td><?php echo $item->completed_at == "" ? '<i class="text-warning mdi mdi-timer-sand"></i> Pending' : '<i class="text-success mdi mdi-check-circle"></i> Completed'.$item->completed_at; ?></td>
													<td class="show-hoverable">
														<span class="partial"><?php echo e(substr($item->comments, 0, 75)); ?><?php echo e(strlen($item->comments) > 75 ? '...' : ''); ?></span>
														<span class="complete"><?php echo e($item->comments); ?></span>
													</td>
													<td>
														<?php if($item->completed_at == ""): ?>
															<span class="btn btn-sm btn-outline-info" data-target="#edit-sample-notes-<?php echo e($loop->iteration); ?>" data-toggle="modal">
																<i class="mdi mdi-pencil"></i>
															</span>
															<div id="edit-sample-notes-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
																<div class="modal-dialog">
																	<!-- Modal content-->
																	<form class="modal-content" id="print-labels-form" method="POST" action="<?php echo e(route('edit-batch-comment', ['id'=>$item->id])); ?>" enctype="multipart/form-data">
																		<?php echo csrf_field(); ?>
																		<div class="modal-header">
																			<h4 class="modal-title"><i class="mdi mdi-message-plus"></i> Edit Note </h4>
																		</div>
																		<div class="modal-body">
																			<div class="form-group">
																				<label class="control-label">User To Notify</label>
																				<select class="form-control" name="user_id" required placeholder="Select User...">
																					<option></option>
																					<?php $__currentLoopData = getNotifiableUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																						<option value="<?php echo e($g->id); ?>" <?php echo e($item->created_by == $g->id ? 'selected' : ''); ?>><?php echo e($g->name); ?></option>
																					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
																				</select>
																			</div>
																			<div class="form-group">
																				<label class="control-label">Also Notify <small class="text-muted">*Optional</small></label>
																				<select class="form-control" name="followers[]" multiple placeholder="Other Notifiable Users...">
																					<option></option>
																					<?php $__currentLoopData = getNotifiableUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																						<option value="<?php echo e($g->id); ?>" <?php echo e(in_array($g->id, $item->people_to_cc()['ids']) ? 'selected' : ''); ?>><?php echo e($g->name); ?></option>
																					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
																				</select>
																			</div>
																			<input name="batch_id" type="hidden" value="<?php echo e($batch->id); ?>" />
																			<div class="form-group">
																				<label class="control-label">Type</label>
																				<select class="form-control" name="type" required placeholder="Message Type...">
																					<option></option>
																					<?php $__currentLoopData = getNotesReminderTypes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																						<option value="<?php echo e($g); ?>" <?php echo e($item->comment_type == $g ? 'selected' : ''); ?>><?php echo e($g); ?></option>
																					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
																				</select>
																			</div>
																			<div class="form-group">
																				<label class="control-label">Message</label>
																				<textarea class="form-control" name="message" placeholder="Message..." required><?php echo e($item->comments); ?></textarea>
																			</div>
																			<div class="form-group">
																				<label class="control-label"><input type="checkbox" value="yes" name="complete" /> Mark as Complete </label>
																			</div>
																		</div>
																		<div class="modal-footer">
																			<button type="submit" class="btn btn-info btn-sm print-label-btn"><i class="mdi mdi-content-save"></i> Save</button>
																			<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
																		</div>
																	</form>
																</div>
															</div>
														<?php else: ?>
															-
														<?php endif; ?>
													</td>
												</tr>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										</tbody>
									</table>
								</div>
							</div>
							<div class="tab-pane fade p-3" id="ammendment" role="tabpanel" aria-labelledby="one-tab">
								<h5 class="card-title">
									<i class="mdi mdi-file-document-edit"></i> Amendments
									
								</h5>

								<div class="table-responsive">
									<table class="table table-condensed table-sm my-small-text table-hover table-stripped">
										<thead class="bg-light">
											<th>Version No</th>
											<th nowrap>Samples</th>
											<th nowrap>Amended By</th>
											<th>Date</th>
											<th>Reason</th>
											<th nowrap>Report</th>

										</thead>
										<tbody>
											<?php $ammendments = getBatchAmmendmentsById($batch->id)?>
											<?php $__currentLoopData = $ammendments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<tr>
												<td>V <?php echo e($a->version_number); ?></td>
												<td>
													<?php echo e($a->sample_name); ?>

												</td>
												<td>
													<?php $user = getUserById($a->created_by_id)?>
													<?php echo e($user->name); ?>

												</td>
												<td><?php echo e($a->created_at); ?></td>
												<td><span class="btn-sm btn-outline-dark mdi mdi-comment-text" data-toggle="modal" data-target="#reason-<?php echo e($a->id); ?>" data-toggle="tooltip" title="Amendment Reason" ></span>
												<div class="modal fade" id="reason-<?php echo e($a->id); ?>" role="dialog">
													<div class="modal-dialog">
														<div class="modal-content">
															<div class="modal-header bg-light">
																<h4 class="modal-title"><i class="mdi mdi-comment-text"></i> Amendment <?php echo e($a->version); ?> Reason</h4>
															</div>
															<div class="modal-body">
																<?php echo e($a->reason); ?>

															</div>
															<div class="modal-footer">
															<button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Close</button>
															</div>
														</div>
													</div>
												</div>
											</td>
											<td nowrap><a href="<?php echo $a->report_url == '' ? '' : '/storage'.$a->report_url; ?>"><i class="mdi mdi-download"></i> Download Report</a></td>
											</tr>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										</tbody>
									</table>
								</div>
							</div>
							<div class="tab-pane fade p-3" id="Attachment" role="tabpanel" aria-labelledby="one-tab">
								<h5 class="card-tile mb-2">
									<i class="mdi mdi-attachment"></i> Attachments
									<span class="btn btn-outline-info btn-sm float-right" data-target="#add-attachment-batch" style="font-size: 12px !important;" data-toggle="modal"><i class="mdi mdi-plus"></i> Add</span>
								</h5>
								<div class="table-responsive">
								<table class="table table-condensed table-sm table-hover table-stripped table-bordered">
									<thead class="bg-light p-2">
										<tr>
											<th></th>
											<th>Title</th>
											<th>Upload Date</th>
											<th>Uploaded By</th>
											<th>File</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php if(Auth::user()->is_client == 1): ?>
											<?php $__currentLoopData = $attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
												<?php if($a->is_internal == 0): ?>
													<?php $user_upload = getUserById($a->uploaded_by)?>
													<tr>
														<td><?php echo e($loop->iteration); ?></td>
														<td><?php echo e($a->title ?? 'N/a'); ?></td>
														<td><?php echo e(date('Y-m-d',strtotime($a->created_at))); ?></td>
														<td><?php echo e($user_upload->name); ?></td>
														<td class="text-center">
														<a href="<?php echo e($a->attachment_url); ?>" target="_blank" data-toggle="tooltip" data-title="View Attachment" class=" btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i></a>
														</td>
														<td>
															<span class="btn btn-sm btn-outline-danger" data-title="Delete Attachment" data-toggle="modal" data-target="#delete-attachment-<?php echo e($a->id); ?>" ><i class="mdi mdi-delete-empty"></i></span>
															<div class="modal fade" id="delete-attachment-<?php echo e($a->id); ?>" role="dialog">
																<div class="modal-dialog">
																	<div class="modal-content">
																		<form action="<?php echo e(route('delete_batch_attachmment')); ?>" method="post">
																			<?php echo csrf_field(); ?>  
																			<div class="modal-body">
																				
																					<div class="alert alert-danger p-3">
																					<i class="mdi mdi-delete-empty"></i>	Confirm you want to delete attchment <?php echo e($loop->iteration); ?>.
																					</div>
																			
																				<input type="hidden" name="attachment_id" value="<?php echo e($a->id); ?>">

																			</div>
																			
																			<div class="modal-footer">
																				<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Confirm</button>
																				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
										
																			</div>
																		</form>
																	</div>
																</div>
															</div>
														</td>
													</tr>
												<?php endif; ?>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										<?php else: ?>
											<?php $__currentLoopData = $attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<?php $user_upload = getUserById($a->uploaded_by)?>
											<tr>
												<td><?php echo e($loop->iteration); ?></td>
												<td><?php echo e($a->title ?? 'N/a'); ?></td>
												<td><?php echo e(date('Y-m-d',strtotime($a->created_at))); ?></td>
												<td><?php echo e($user_upload->name); ?></td>
												<td class="text-center">
												<a href="<?php echo e($a->attachment_url); ?>" target="_blank" data-toggle="tooltip" data-title="View Attachment" class=" btn-sm btn btn-outline-dark"><i class="mdi mdi-eye"></i></a>
												</td>
												<td>
													<span class="btn btn-sm btn-outline-danger" data-title="Delete Attachment" data-toggle="modal" data-target="#delete-attachment-<?php echo e($a->id); ?>" ><i class="mdi mdi-delete-empty"></i></span>
													<div class="modal fade" id="delete-attachment-<?php echo e($a->id); ?>" role="dialog">
														<div class="modal-dialog">
															<div class="modal-content">
																<form action="<?php echo e(route('delete_batch_attachmment')); ?>" method="post">
																	<?php echo csrf_field(); ?>  
																	<div class="modal-body">
																		
																			<div class="alert alert-danger p-3">
																			<i class="mdi mdi-delete-empty"></i>	Confirm you want to delete attchment <?php echo e($loop->iteration); ?>.
																			</div>
																	
																		<input type="hidden" name="attachment_id" value="<?php echo e($a->id); ?>">

																	</div>
																	
																	<div class="modal-footer">
																		<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Confirm</button>
																		<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
								
																	</div>
																</form>
															</div>
														</div>
													</div>
												</td>
											</tr>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										<?php endif; ?>
									</tbody>
								</table>
								</div>
							</div>
							<?php if(in_array($batch->status, array("Sample Approval", "Samples In Lab", "Sample Verification"))): ?>
								<div class="tab-pane fade p-3" id="data-from-equipment-results" role="tabpanel" aria-labelledby="one-tab">
									<h5 class="p-2">
										Equipment Data
										
									</h5>
									<div class="card tab-card">
										<div class="card-header tab-card-header">
											<ul class="nav nav-tabs card-header-tabs" id="equipment-data-results-header" role="tablist">
												<li class="nav-item">
												<a href="#not-processed-tab"  data-toggle="tab" role="tab" aria-selected="true" class="nav-link active" id="not-processed"><i class="mdi mdi-content-save-cog"></i> Equipment Data</a>
												</li>
												<li class="nav-item">
													<a href="#duplicate-tab"  data-toggle="tab" role="tab" aria-selected="true" class="nav-link" id="duplicate-tab-content"><i class="mdi mdi-content-duplicate"></i> Raw Data</a>
												</li>
												<li class="nav-item">
													<a href="#processed-tab"  data-toggle="tab" role="tab" aria-selected="true" class="nav-link" id="not-processed"><i class="mdi mdi-file-import-outline"></i> Imported Excels</a>
												</li>
											</ul>
										</div>
										<div class="tab-content" id="equipment_data">
											<div class="tab-pane fade show active p-3" id="not-processed-tab" role="tabpanel" aria-labelledby="one-tab">
												<h5 class="p-2">
													<i class="mdi mdi-content-save-cog"></i> Equipment Data
													
												</h5>
												<div class="table-responsive">
													<table class="table table-condensed table-hover table-bordered table-stripped table-sm" style="width:160% !important">
														<thead class="bg-light p-2">
															<tr>
																<th></th>
																<th>Analyte Name</th>
																<th>Well ID</th>
																<th>Sample No</th>
																<th>Well</th>
																<th>Results</th>
																<th>Mean</th>
																<th>Std Dev</th>
																<th>CV%</th>
																<th>Status</th>	
																<th>Remark</th>
																<th>Date of Reading</th>
																<th>Testing Week</th>
																<th>Analyte Code</th>

															</tr>
														</thead>
														<tbody>
															<?php $__currentLoopData = $selected_results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
															<tr>
																<td>
																	<?php echo e($loop->iteration); ?>

																</td>
																<td style="min-width: 150px;"><?php echo e($sr->analyte_name); ?></td>
																<td><?php echo e($sr->well_id); ?></td>
																<td><?php echo e($sr->sample_no); ?></td>
																<td><?php echo e($sr->well); ?></td>
																<td><?php echo e($sr->result); ?></td>
																<td><?php echo e($sr->mean); ?></td>
																<td><?php echo e($sr->std_dev); ?></td>
																<td><?php echo e($sr->cv); ?></td>
																<td><?php echo e($sr->remark); ?></td>
																<td><span class="badge badge-pill badge-primary  <?php echo e($sr->remark_data_class); ?> text-dark"><?php echo e($sr->remark_data); ?></span></td>
																<td><?php echo e($sr->date_of_reading); ?></td>
																<td><?php echo e($sr->test_week); ?></td>
																<td><?php echo e($sr->analyte_code); ?></td>
															</tr>
															<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
														</tbody>
													</table>
												</div>
											</div>
											<div class="tab-pane fade p-3" id="duplicate-tab" role="tab-panel" aria-labelledby="one-tab">
												<h5 class="p-2">
												<i class="mdi mdi-content-duplicate"></i> Raw Data
												<span class="float-right btn btn-outline-warning btn-sm" id="process-data" data-target="#proccess-lab-results" data-toggle="modal"><i class="mdi mdi-file-refresh"></i> Process Data</span>
												</h5>
												<form action="" method="post">
													<div class="table-responsive">
														<table class="table table-condensed table-hover table-bordered table-stripped table-sm" style="width:160% !important">
															<thead class="bg-light p-2">
																<tr>
																	<th></th>
																	<th>Analyte Name</th>
																	<th>Well ID</th>
																	<th>Sample No</th>
																	<th>Well</th>
																	<th>Results</th>
																	<th>Mean</th>
																	<th>Std Dev</th>
																	<th>CV%</th>
																	<th>Status</th>	
																	<th>Remark</th>
																	<th>Date of Reading</th>
																	<th>Testing Week</th>
																	<th>Analyte Code</th>
																	
																</tr>
															</thead>
															<tbody>
																<?php $__currentLoopData = $duplicate_results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																<tr>
																	<td>
																	<div class="form-group">
																		<label class="control-label"><input type="checkbox" id="is-selected" name="is_selected[]" value="<?php echo e($dr->id); ?>" <?php echo e($dr->is_selected == 1 ? 'checked' : ''); ?>  /></label>
																	</div>
																	</td>
																	<td style="min-width: 150px;"><?php echo e($dr->analyte_name); ?></td>
																	<td><?php echo e($dr->well_id); ?></td>
																	<td><?php echo e($dr->sample_no); ?></td>
																	<td><?php echo e($dr->well); ?></td>
																	<td><?php echo e($dr->result); ?></td>
																	<td><?php echo e($dr->mean); ?></td>
																	<td><?php echo e($dr->std_dev); ?></td>
																	<td><?php echo e($dr->cv); ?></td>
																	<td><?php echo e($dr->remark); ?></td>
																	<td><span class="badge badge-pill badge-primary  <?php echo e($dr->remark_data_class); ?> text-dark"><?php echo e($dr->remark_data); ?></span></td>
																	<td><?php echo e($dr->date_of_reading); ?></td>
																	<td><?php echo e($dr->test_week); ?></td>
																	<td><?php echo e($dr->analyte_code); ?></td>
																</tr>
																<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
															</tbody>
														</table>
													</div>

												</form>
											</div>
											<div class="tab-pane fade p-3" id="processed-tab" role="tabpanel" aria-labelledby="one-tab">
												<h5 class="p-2">
												   <i class="mdi mdi-file-import-outline"></i> Imported Excels
												</h5>
												<div class="table-responsive">
													<table class="table table-condensed table-hover table-bordered table-stripped table-sm">
														<thead class="bg-light p-2">
															<tr>
																<th></th>
																<th>Date of Reading</th>			
																<th>Testing Week</th>
																<th>Import User</th>
																<th>No of Strips</th>
																<th>Analyte/Virus</th>
																<th>File</th>
																
															</tr>
														</thead>
														<tbody>
															<?php $__currentLoopData = $excels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $excel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																<tr>
																	<td><?php echo e($loop->iteration); ?></td>
																	<td><?php echo e($excel->date_of_reading); ?></td>
																	<td><?php echo e($excel->test_week); ?></td>
																	<td><?php echo e($excel->user_name); ?></td>
																	<td><?php echo e($excel->strip_name); ?></td>
																	<td style="min-width: 150px;" ><?php echo e($excel->analyte_name ?? '-'); ?></td>
																	<td class="text-center"><a  href="<?php echo e($excel->excel_url); ?>" target="_blank"><i class="mdi mdi-download"></i> Download</a></td>
																</tr>
															<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>
            <form method="POST" action="<?php echo e(route('add-batch-samples', ['batch'=>$batchID])); ?>" class="tab-pane fade show active p-3" id="samples" role="tabpanel" aria-labelledby="one-tab">
              <h5 class="card-title">
								<span class="btn btn-transparent">Samples Configuration</span>
								<?php if(isset($batch->id) || (Auth::user()->is_client == 1 && isset($batch->status) && $batch->status =='Samples En-Route')): ?>
									<?php if(in_array($batch->status, array("Sample Verification","Sample Approval","Payments","Reports","Samples In Lab"))): ?>
										<?php if(sizeof($not_captured) > 0): ?>
										<button type="button" class="btn btn-outline-danger btn-sm ml-2" data-toggle="modal" data-target="#missing-parameters-modal">
											<i class="mdi mdi-content-save"></i> Missing Analytes
										</button>
										<?php endif; ?>
									<?php endif; ?>
									<?php if(isset($batch->status) && ($batch->status == "Sample Verification" || $batch->status == "Sample Approval") && Auth::user()->is_client == 0): ?>
										
											<button type="button" class="btn btn-danger btn-sm text-white float-right" data-target="#send-back-for-rechcek-modal" data-toggle="modal">
												<i class="mdi mdi-page-previous"></i> Recheck
											</button> &nbsp; &nbsp;
										
									<?php endif; ?>
									<?php if(isset($batch->status) && in_array($batch->status, array("Samples Reception","Samples En-Route"))): ?>
										<?php if(Auth::user()->is_client == 1 && $batch->status == 'Samples Reception'): ?>
										<?php else: ?>
										<input type="hidden" name="batch" value=<?php echo e($batch->id); ?>>
										<button type="button" class="btn btn-danger btn-sm text-white ml-2 save-samples"><i class="mdi mdi-content-save"></i> Save</button> &nbsp; &nbsp;
										<span class="btn btn-success btn-sm create-new-sample-row float-right"><i class="mdi mdi-plus"></i> Add</span> &nbsp; &nbsp;
										<span class="btn btn-primary btn-sm duplicate-sample-row float-right mr-1"><i class="mdi mdi-content-duplicate"></i> Duplicate</span>
										<span data-toggle="modal" data-target="#batch-edit-modal"
											class="btn btn-transparent text-info btn-sm batch-edit-row float-right mr-1">
											<i class="mdi mdi-pencil-box-multiple"></i> Batch Edit
										</span>
										<?php endif; ?>
									<?php endif; ?>
								<?php endif; ?>
							</h5>
							<?php echo csrf_field(); ?>
              <div class="table-responsive">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table">
					<thead>
						<tr>
							<th></th>
							<th></th>
							<th>SampleNo</th>
							<th>Code</th>
							<th>Analysis<sup class="text-danger">*</sup></th>
							<th nowrap>Condition<sup class="text-danger">*</sup></th>
							<th nowrap><span class="client-preferred-sample_point-name"></span><sup class="text-danger">*</sup></th>
							<th><span class="client-preferred-product-name"></span><sup class="text-danger">*</sup></th>
							<th>Main Standard <sup class="text-danger">*</sup></th>
							<th>Secondary Standard <sup class="text-danger">*</sup></th>
							<th>Lab Submission No</th>
							<th>Comments</th>
							<th>Storage</th>
							<th>Slot</th>
							<th>Quantity</th>
							<th>UoM</th>
							<th>Barcode</th>
							
						</tr>
					</thead>
					<?php
						$sample_analysis_type = array();
						$analaytesHolder = array();
						$analysisBySample = array();
						if(isset($batch->id)){

							foreach ($batch->captured_results ?? array() as $item){
								if(!isset($analaytesHolder[$item->sample_detail_code])){
									$analaytesHolder[$item->sample_detail_code] = array();
								}
								$operators = $item->operators();
								if(sizeof($operators)> 0){
	
									$item->ops = $item->operators();
								}else{
									$item->ops = getOperators();
								}
								$item->equip_name = $item->equipment()->name ?? '-';
								$item->method = $item->method()->name ?? '-';
								$item->def_operator = $item->defacto_analyst();
								$item->analysis_type = $item->analysis_type;
								$analaytesHolder[$item->sample_detail_code][] = $item;
								$item->remarks = $remark_configs;
								
							}
	
							foreach ($batch->samples ?? array() as $sample) {
								if(!isset($analysisBySample[$sample->sample_code])){
									$analysisBySample[$sample->sample_code] = array();
								}
								$analysisBySample[$sample->sample_code] = array_merge(explode(",",$sample->analysis_type_id), $analysisBySample[$sample->sample_code]);
							}
	
							// echo json_encode($batch->all_samples());
							
							$sample_analysis_type = $selectedSampleType->analysis_types;
						}
					?>
					<tbody id="sample-detail-rows"
						data-sample_analysis_ids = '<?php echo e(json_encode($analysisBySample)); ?>'
						data-parameters='<?php echo e(json_encode($analaytesHolder)); ?>'
						data-conditions='<?php echo e(json_encode($selectedSampleType->sample_condition ?? array())); ?>'
						data-stores='<?php echo e(json_encode($labStores)); ?>'
						data-analysis_types='<?php echo e(json_encode($selectedSampleType->analysis_types ?? array())); ?>'
						data-samples="<?php echo e(json_encode(isset($batch->id) ? $batch->all_samples() : array())); ?>"
						data-ammendments = "<?php echo e(json_encode($ammendable)); ?>"
						data-client = "<?php echo e(json_encode(Auth::user())); ?>"
						data-batch = "<?php echo e(json_encode($batch ?? array())); ?>"
						data-standards = "<?php echo e(json_encode($standards ?? array())); ?>"
						>

					</tbody>
                </table>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>

	<?php if(isset($batch->id)): ?>
		<?php if($batch->status == 'Samples In Lab'): ?>
		<div class="modal fade" id="import-results" role="dialog">
					<div class="modal-dialog">
						<form action="<?php echo e(route('import_lab_results')); ?>" method="post" class="modal-content" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>
							<div class="modal-header">
								<h5 class="modal-title"><i class="mdi mdi-file-import-outline"></i> Import Results </h5>
							</div>
							<div class="modal-body">
								<input type="hidden" name="batch_id" value="<?php echo e($batch->id); ?>">
								<div class="form-group">
									<label class="control-label">Date of Reading <span class="text-danger">*</span></label>
									<input type="date" placeholder="Select Date of reading..." name="date_reading" value="" required class="form-control">
								</div>
								<div class="form-group">
									<label class="control-label">Testing Week</label>
									<select name="test_week" required id="" class="form-control">
										<option value="">Select Testing Week</option>
										<?php $__currentLoopData = getweeks(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($w); ?>"><?php echo e($w); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
								<div class="form-group">
									<label class="control-label">Choose Analyst <span class="text-danger">*</span></label>
									<select name="analyst_id" id="" class="form-control" required>
										<option value="">Select Analyst</option>
										<?php $__currentLoopData = $analysts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analyst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<option value="<?php echo e($analyst->id); ?>" <?php echo e($analyst->id == auth()->user()->id ? 'selected' : ''); ?>><?php echo e($analyst->name); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
								<div class="form-group">
									<label class="control-label">Choose Virus/Analyte</label>
									<select name="analyte_id"  class="form-control">
										<option value="">Choose Analyte...</option>
										<?php $__currentLoopData = $analytes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
								<div class="form-group">
									<label class="control-label">No of Strips <span class="text-danger">*</span></label>
									<select name="no_of_strips" required aria-placeholder="Select Strips..." class="form-control">
										<option value="">Select Strip</option>
										<?php $__currentLoopData = $strips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $strip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($strip->id); ?>"><?php echo e($strip->key); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
								<div class="form-group">
									<label class="control-label">Choose File <small class="text-danger">(.xlsx or .xls file *)</small></label>
									<input type="file" value="" name="file" required class="form-control" placeholder="Choose Excel File...">
								</div>

							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Import results</button>
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
		<?php endif; ?>
		<div class="modal fade" id="add-attachment-batch" role="dialog">
			<div class="modal-dialog">
				<form action="<?php echo e(route('add_batch_attachment')); ?>" method="post" enctype="multipart/form-data" class="modal-content">
					<?php echo csrf_field(); ?> 
					<div class="modal-header">
						<h5 class="modal-title">Add Attachment For <?php echo e($batch->batch_code); ?></h5>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label class="control-label">Title</label>
							<input type="text" name="title" id="" class="form-control" required>
						</div>
						<div class="form-group">
							<label class="control-label">Choose File</label>
							<input type="file" name="attachment" required class="form-control">
							<input type="hidden" name="batch_id" value="<?php echo e($batch->id); ?>">
						</div>
						<div class="form-group">
							<label class="control-label">
								<input type="checkbox" name="is_internal" id=""> For Internal Use
							</label>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Save</button>
						<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>
		<?php $requestTypes = getRequestTypes(); ?>
		<?php if(in_array($batch->status, array("Sample Verification","Sample Approval","Payments","Reports","Samples In Lab"))): ?>
			<?php if($batch->processed_results()->count() > 0): ?>
				<div id="send-for-approval-modal" class="modal fade" role="dialog">
					<div class="modal-dialog">
						<!-- Modal content-->
						<form class="modal-content" method="POST" action="<?php echo e(route('move-to-workflow', ['status'=>"Sample Approval", 'batch_id'=>$batch->id])); ?>" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>
							<div class="modal-header">
								<h4 class="modal-title"><i class="mdi mdi-check-decagram"></i> Send to Sample Approval </h4>
							</div>
							<div class="modal-body">
								<div class="form-group">
									<label class="control-label">Approval Notes/Comments</label>
									<textarea class="form-control" name="comments" placeholder="Comments..."></textarea>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
									<label class="form-check-label">
										Send Email Notification
									</label>
								</div>
								<br>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
									<label class="form-check-label">
										Send Message
									</label>
								</div>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Yes Proceed</button>
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
			<?php endif; ?>
			<?php if(isset($batch->status) && ($batch->status=="Sample Verification" || $batch->status=="Sample Approval")): ?>
				<div class="modal fadeprompt-report-modal" role="dialog">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-body">
								<div class="alert alert-danger">
									Ensure that batch <?php echo e($batch->batch_code); ?> has results, The results has been proccessed and Approved by clicking the Approve button.
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				<div id="process-results-modal" class="modal fade" role="dialog">
					<div class="modal-dialog">
						<!-- Modal content-->
						<form class="modal-content" method="GET" action="<?php echo e(route('process-raw-results', ['batch_id'=>$batch->id])); ?>" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>
							<div class="modal-header">
								<h4 class="modal-title"><i class="mdi mdi-file-cog-outline"></i> Process Results </h4>
							</div>
							<div class="modal-body">
								<div class="alert alert-info">
									<h4><i class="mdi mdi-information pull-left"></i> Proceed with processing of the results?</h4>
								</div>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Yes Process</button>
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
				<div id="send-back-for-rechcek-modal" class="modal fade" role="dialog">
					<div class="modal-dialog">
						<!-- Modal content-->
						<form class="modal-content" method="POST" action="<?php echo e(route('move-to-workflow', ['status'=>"Samples In Lab", 'batch_id'=>$batch->id])); ?>" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>
							<div class="modal-header">
								<h4 class="modal-title"><i class="mdi mdi-page-previous"></i> Recheck Batch Samples</h4>
							</div>
							<div class="modal-body">
								<div class="form-group">
									<label class="control-label">User To Notify</label>
									<select class="form-control" name="user_id" required placeholder="Select User...">
										<option></option>
										<?php $__currentLoopData = getNotifiableUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($item->id); ?>"><?php echo e($item->name); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
								<div class="form-group">
									<label class="control-label">Also Notify <small class="text-muted">*Optional</small></label>
									<select class="form-control" name="followers[]" multiple placeholder="Other Notifiable Users...">
										<option></option>
										<?php $__currentLoopData = getNotifiableUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($item->id); ?>"><?php echo e($item->name); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
								<input name="batch_id" type="hidden" value="<?php echo e($batch->id); ?>" />
								<div class="form-group">
									<label class="control-label">Comments</label>
									<textarea class="form-control" name="comments" required placeholder="Comments..."></textarea>
								</div>
								<div class="form-group hidden">
									<label class="control-label">Request Type</label>
									<input type="text" name="type" value="Recheck" class="form-control">
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
									<label class="form-check-label">
										Send Email Notification
									</label>
								</div>
								<br>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
									<label class="form-check-label">
										Send Message
									</label>
								</div>


							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-danger btn-sm"><i class="mdi mdi-keyboard-return"></i> Recheck</button>
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
			<?php endif; ?>
			<div id="missing-parameters-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<div class="modal-content">
						<?php echo csrf_field(); ?>
						<div class="modal-header">
							<h4 class="modal-title"><i class="mdi mdi-beaker-question-outline"></i> Missing Analytes </h4>
						</div>
						<div class="modal-body">
							
							
							<div class="table-responsive">
								<table class="table table-condensed table-striped my-small-text table-sm table-bordered">
									<thead>
										<tr>
											<th>Sample Code</th>
											<th>Missing Parameters</th>
										</tr>
									</thead>
									<tbody>
										<?php $__currentLoopData = $not_captured; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $samC=>$vals): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<?php $anals = array_values($vals); ?>
											<tr>
												<td><?php echo e($samC); ?></td>
												<td><?php echo e(implode(',', $anals)); ?></td>
											</tr>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php if(isset($batch->status) && in_array($batch->status, array("Sample Approval","Samples In Lab","Sample Verification","Payments"))): ?>
			
				<div id="provide-interpretations" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<!-- Modal content-->
						<form class="modal-content" method="POST" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>
							<div class="modal-header">
								<h4 class="modal-title"><i class="mdi mdi-file-document-edit"></i> Comments & Interpretations </h4>
							</div>
							<div class="modal-body" id="sample-interpretations-holder"></div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-info btn-sm" onclick="tinyMCE.triggerSave()"><i class="mdi mdi-content-save"></i> Save</button>
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
				<div id="process-results-modals" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<!-- Modal content-->
						<form class="modal-content" method="POST" action="<?php echo e(route('report-interpretations', ['batch_id'=>$batch->id])); ?>" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>
							<div class="modal-header">
								<h4 class="modal-title"><i class="mdi mdi-file-document-edit"></i> Provide Interpretations </h4>
							</div>
							<div class="modal-body">
								<ul class="nav nav-tabs" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" id="report-interpretations-tab" data-toggle="tab" href="#report-interpretations" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-typewriter"></i> Email Body</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" id="report-header-tab" data-toggle="tab" href="#report-header-details" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-page-layout-header"></i> Report Header/Footer Details</a>
									</li>
								</ul>
								<div class="tab-content">
									<div class="tab-pane fade p-3" id="report-header-details" role="tabpanel" aria-labelledby="one-tab">
										<h4>Report </h4>
										<div class="form-group">
											<label>Title</label>
											<input type="text" class="form-control" value="<?php echo e(isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->title ?? $headerDetails['client_header']->title) : ''); ?>" name="report_title" placeholder="Report Title..." required />
										</div>
										<div class="form-group">
											<label>To</label>
											<input type="text" class="form-control" value="<?php echo e(isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->to ?? $headerDetails['client_header']->to) : ''); ?>" name="report_to" placeholder="Report To..." required />
										</div>
										<div class="form-group">
											<label>C.C.</label>
											<input type="text" class="form-control" value="<?php echo e(isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->cc ?? $headerDetails['client_header']->cc) : ''); ?>" name="report_cc" placeholder="Report C.C..." required />
										</div>
										<div class="form-group">
											<label>From</label>
											<input type="text" class="form-control" value="<?php echo e(isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->from ?? $headerDetails['client_header']->from) : ''); ?>" name="report_from" placeholder="Report From..." required />
										</div>
										<div class="form-group">
											<label>Date</label>
											<input type="date" class="form-control" value="<?php echo e(isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->date ?? $headerDetails['client_header']->date) : ''); ?>" name="report_date" placeholder="Report Date..." required />
										</div>
										
										<div class="form-group">
											<label>Re</label>
											<textarea class="form-control editor" name="report_re" placeholder="Report Re..." required><?php echo e(isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->re ?? $headerDetails['client_header']->re) : ''); ?></textarea>
										</div>
										<hr>
										<div class="form-group">
											<label>For</label>
											<input type="text" class="form-control" value="<?php echo e(isset($headerDetails['header']->model) || isset($headerDetails['client_header']->model) ? ($headerDetails['header']->for ?? $headerDetails['client_header']->for) : ''); ?>" name="report_for" placeholder="Report For..." required />
										</div>
										<div class="form-group">
											<label><input type="checkbox" name="update_client_headers" value="1"/> Update client report header defaults.</label>
										</div>
									</div>
									<div class="tab-pane show active p-3" id="report-interpretations" role="tabpanel" aria-labelledby="one-tab">
										<div class="form-group">
											<label>Declared Amount</label>
											<input type="text" class="form-control" value="<?php echo e($batch->declared_amount ?? ''); ?>" name="declared_amount" placeholder="Declared Amount..." />
										</div>
										<div class="form-group">
											<label>Final Declared Amount</label>
											<input type="text" class="form-control" value="<?php echo e($batch->final_declared_amount ?? ''); ?>" name="final_declared_amount" placeholder="Final Declared Amount..." />
										</div>
										<div class="form-group">
											<label>Outgoing Email Body</label>
											<textarea class="form-control editor" name="outgoing_email_body" placeholder="Outgoing Email Body..." required><?php echo e($headerDetails['header']->outgoing_email_body ?? ''); ?></textarea>
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-info btn-sm" onclick="tinyMCE.triggerSave()"><i class="mdi mdi-content-save"></i> Save</button>
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
				<?php if($batch->batch_report_url): ?>
					<div id="send-to-email-modal" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<!-- Modal content-->
							<form class="modal-content" method="POST" action="<?php echo e(route('move-to-workflow', ['status'=>"Reports to Email", 'batch_id'=>$batch->id])); ?>" enctype="multipart/form-data">
								<?php echo csrf_field(); ?>
								<div class="modal-header">
									<h4 class="modal-title"><i class="mdi mdi-file-check-outline"></i> Ready for Email Report?</h4>
								</div>
								<div class="modal-body">
									
									<?php if($batch->customer_paid == 0): ?>
									<div class="alert alert-danger">
										

											<i class="mdi mdi-information pull-left"></i> Batch <?php echo e($batch->batch_code); ?> has not been paid in full. Kindly confirm this before proceeding to email the report ! 	
									</div>
									<?php else: ?>
									<div class="alert alert-info">
										<h6><i class="mdi mdi-information pull-left"></i> Proceed with moving batch to Email report?</h6>
									</div>
									<?php endif; ?>

								</div>
								<div class="modal-footer">
									<button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Proceed</button>
									<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
								</div>
							</form>
						</div>
					</div>
					<div id="send-to-payments-modal" class="modal fade" role="dialog">
						<div class="modal-dialog">
							<!-- Modal content-->
							<form class="modal-content" method="POST" action="<?php echo e(route('move-to-workflow', ['status'=>"Payments", 'batch_id'=>$batch->id])); ?>" enctype="multipart/form-data">
								<?php echo csrf_field(); ?>
								<div class="modal-header">
									<h4 class="modal-title"><i class="mdi mdi-credit-card"></i> Send to Payment</h4>
								</div>
								<div class="modal-body">
									<div class="form-group">
										<label class="control-label">Comments</label>
										<textarea class="form-control" name="comments" placeholder="Comments..."></textarea>
									</div>
								</div>
								<div class="modal-footer">
									<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Send to Payment</button>
									<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
								</div>
							</form>
						</div>
					</div>
				<?php endif; ?>
			
		<?php endif; ?>
		<?php if(isset($batch->status) && $batch->status=="Samples In Lab"): ?>
				<div id="send-to-verification-modal" class="modal fade" role="dialog">
					<div class="modal-dialog">
						<!-- Modal content-->
						<form class="modal-content" method="POST" action="<?php echo e(route('move-to-workflow', ['status'=>"Sample Verification", 'batch_id'=>$batch->id])); ?>" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>
							<div class="modal-header">
								<h4 class="modal-title"><i class="mdi mdi-check-decagram"></i> Send to Verification </h4>
							</div>
							<div class="modal-body">
								<?php if(sizeof( $not_captured ) > 0): ?>
									<div class="form-group">
										<div class="alert alert-danger">
											<i class="mdi mdi-information pull-left" style="font-size: 24px"></i> Some analytes have not been captured. Please ignore this message, if they are to be calculated during processing.
										</div>
									</div>
								<?php endif; ?>
								<div class="form-group">
									<label class="control-label">Verification Notes/Comments</label>
									<textarea class="form-control" name="comments" placeholder="Comments..."></textarea>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" class="form-control" name="notification" />
									<label class="form-check-label">
										Send Email Notification
									</label>
								</div>
								<br>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
									<label class="form-check-label">
										Send Message
									</label>
								</div>
							</div>
							<div class="modal-footer">
								<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Send to Verification</button>
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
							</div>
						</form>
					</div>
				</div>
		<?php endif; ?>
		<?php if(isset($batch->status) && $batch->status=="Samples Request Review"): ?>
			<div id="dispatch-to-labs-modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<!-- Modal content-->
					<form class="modal-content" method="POST" action="<?php echo e(route('change-batch-workflow')); ?>" enctype="multipart/form-data">
						<?php echo csrf_field(); ?>
						<?php if($batch->sample_tracking_stage=='20007'): ?>
							<div class="modal-header">
								<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Approve Request </h4>
							</div>
							<div class="modal-body">
								<input type="hidden" name="status" value="Samples Request Review" />
								<input type="hidden" name="tracking_stage" value="20008" />
								<input type="hidden" name="bacth_id" value="<?php echo e($batch->id); ?>" />
								<div class="form-group">
									<label class="control-label">Select Request Type</label>
									<select class="form-control" name="request_type_id[]" placeholder="Request Type..." multiple required>
										<option></option>
										<?php $__currentLoopData = $requestTypes[1]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($i->id); ?>"><?php echo e($i->name); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										<option value="Other">Other Type</option>
									</select>
								</div>
								<div class="form-group other-reason hidden">
									<label class="control-label">Specify Other Request Type</label>
									<textarea class="form-control" name="other_type" placeholder="Specify Other Request Type..."></textarea>
								</div>
								<div class="form-group">
									<label class="control-label">Approval Comments</label>
									<textarea class="form-control" name="comments" placeholder="Comments..."></textarea>
								</div>
								<div class="form-group">
									<label class="control-label"><input type="checkbox" name="is_priority" value="High" /> Is High Prority</label>
								</div>
							</div>
						<?php else: ?>
							<div class="modal-header">
								<h4 class="modal-title"><i class="mdi mdi-clipboard-arrow-right"></i> Approve Request </h4>
							</div>
							<div class="modal-body">
								<input type="hidden" name="status" value="Samples In Lab" />
								<input type="hidden" name="bacth_id" value="<?php echo e($batch->id); ?>" />
								<div class="form-group">
									<label class="control-label">Select Specific Specialist</label>
									<select class="form-control" name="specialist_analyst_id" placeholder="Specific Specialist..." required>
										<option></option>
										
										<?php $__currentLoopData = analysts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($i->id); ?>"><?php echo e($i->name); ?></option>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
								<div class="form-group other-reason">
									<label class="control-label">Approval Comments</label>
									<textarea class="form-control" name="comments" placeholder="Comments..."></textarea>
								</div>
							</div>
						<?php endif; ?>
						<div class="modal-footer">
							<button type="submit" class="btn btn-info btn-sm"><i class="mdi mdi-thumb-up"></i> Yes</button>
							<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
						</div>
					</form>
				</div>
			</div>
		<?php endif; ?>

		<div id="add-analyte-modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form class="modal-content" id="print-labels-form" method="POST" action="<?php echo e(route('add-batch-comment')); ?>" enctype="multipart/form-data">
					<?php echo csrf_field(); ?>
					<div class="modal-header">
						<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add New Analyte </h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label class="control-label">Select Analysis</label>
							<select class="form-control" name="analysis_id" required placeholder="Select Analysis...">
								<option></option>
								<?php $__currentLoopData = $batch->samples; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($item->id); ?>"><?php echo e($item->sample_code); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Select Analyte</label>
							<select class="form-control" name="analysis_id" required placeholder="Select Analyte...">
								<option></option>
							</select>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-info btn-sm print-label-btn"><i class="mdi mdi-content-save"></i> Save</button>
						<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>

		<div id="add-sample-notes" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form class="modal-content" id="print-labels-form" method="POST" action="<?php echo e(route('add-batch-comment')); ?>" enctype="multipart/form-data">
					<?php echo csrf_field(); ?>
					<div class="modal-header">
						<h4 class="modal-title"><i class="mdi mdi-message-plus"></i> Add Note </h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label class="control-label">User To Notify</label>
							<select class="form-control" name="user_id" required placeholder="Select User...">
								<option></option>
								<?php $__currentLoopData = getNotifiableUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($item->id); ?>"><?php echo e($item->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Also Notify <small class="text-muted">*Optional</small></label>
							<select class="form-control" name="followers[]" multiple placeholder="Other Notifiable Users...">
								<option></option>
								<?php $__currentLoopData = getNotifiableUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($item->id); ?>"><?php echo e($item->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<input name="batch_id" type="hidden" value="<?php echo e($batch->id); ?>" />
						<div class="form-group">
							<label class="control-label">Type</label>
							<select class="form-control" name="type" required placeholder="Message Type...">
								<option></option>
								<?php if($batch->status == 'Sample Verification' || $batch->status == 'Samples In Lab'): ?>

								<option value="Recheck">Recheck</option>
								<?php endif; ?>
								<?php $__currentLoopData = getNotesReminderTypes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($item); ?>"><?php echo e($item); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Message</label>
							<textarea class="form-control" name="message" placeholder="Message..." required></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-info btn-sm print-label-btn"><i class="mdi mdi-email-send"></i> Send</button>
						<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
		</div>
	<?php endif; ?>
	<?php if(isset($batch->status) && in_array($batch->status, array("Samples Reception", "Samples En-Route"))): ?>
	<div id="batch-edit-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-pencil-box-multiple"></i> Batch Edit </h4>
				</div>
				<div class="modal-body">
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-analysis"> Select Analysis</label>
						<select name="sample_analysis" class="form-control form-control-sm sample-analysis" multiple placeholder="Select Analysis..."></select>
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-condition"> Sample Condition</label>
						<select  name="sample_condition" class="form-control form-control-sm sample-condition" placeholder="Select Sample Condition..." required></select>
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label "><input type="checkbox" class="bulk-checkbox" data-name=".sample-point"> <span class="client-preferred-sample_point-name">Sample Point</span></label>
						<select  name="sample_point" class="form-control form-control-sm sample-point" placeholder="Select..." required></select>
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-product"> <span class="client-preferred-product-name">Product</span></label>
						<select  name="sample_product" class="form-control form-control-sm sample-product" placeholder="Select..." required></select>
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-barcode"> Barcode</label>
						<input  name="sample_barcode" class="form-control form-control-sm sample-barcode" placeholder="Select..." >
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-comments"> Comments</label>
						<input  name="sample_comments" class="form-control form-control-sm sample-comments" placeholder="Select..." >
					</div>
					<div class="form-group form-group-sm">
						<label class="control-label"><input type="checkbox" class="bulk-checkbox" data-name=".sample-gps"> GPS</label>
						<input  name="sample_gps" class="form-control form-control-sm sample-gps" placeholder="Select...">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-info btn-sm make-batch-changes" data-dismiss="modal"><i class="mdi mdi-refresh"></i> Change</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
	<div id="show-sample-analysis-analytes" class="modal fade" data-backdrop="static" data-keyboard="false" role="dialog">
		<div class="modal-dialog modal-lg">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-snowflake"></i> Analysis Parameters </h4>
					<span class="btn btn-outline-danger btn-sm float-right" data-dismiss="modal">Close</span>
				</div>
				<div class="modal-body">
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="configured-analytes-tab" data-toggle="tab" href="#configured-analytes" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-snowflake"></i> Parameters</a>
						</li>
						<?php if(isset($batch->status) && $batch->status == "Samples Reception" && Auth::user()->is_client == 0): ?>
						<li class="nav-item">
							<a class="nav-link" id="add-analytes-tab" data-toggle="tab" href="#add-analytes" role="tab" aria-controls="Parameters" aria-selected="true"><i class="mdi mdi-plus"></i> Add Analyte</a>
						</li>
						<?php endif; ?>
					</ul>
					<div class="tab-content">
						<form class="tab-pane show active p-3" method="POST" action="<?php echo e(route('capture-raw-results')); ?>" id="configured-analytes" role="tabpanel" aria-labelledby="one-tab">
							<?php echo csrf_field(); ?>
							<h5 class="mb-3">
								<?php if(Auth::user()->is_client == 0): ?>
								Raw Results
								<span class="ml-4 badge badge-pill badge-light p-2 mr-4" style="font-weight: 400!important">
									<span class="bg-green analytes-with-results-count small-badge">12</span> With Results
								</span>
								<span class="badge badge-pill badge-light p-2 show-missing-results" style="font-weight: 400!important">
									<span class="bg-red analytes-without-results-count small-badge">0</span> Missing Results
								</span>
								
								<?php if(isset($batch->status) &&  $batch->status == "Samples In Lab"): ?>
									<button class="btn btn-sm btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
								<?php endif; ?>
								<?php if(isset($batch->status) && in_array($batch->status,array('Samples Reception','Samples Request Review','Samples En-Route'))): ?>
									<a href="/sample-workflow/batch/<?php echo e($batch->id); ?>/details" class="btn btn-outline-primary float-right btn-sm"><i class="mdi mdi-content-save"></i> Save</a>
								<?php endif; ?>
								<?php endif; ?>
								<br>
								
								<span class="badge badge-pill badge-light float-left p-2 " style="font-weight: 400!important">
									Standard - <span class="main-standard-name"></span>
								</span>
								<br>
							</h5>
							<div class="table-responsive" id="sph-parent">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table">
									<thead class="bg-light p-2">
										<tr>
										<th></th>
											<th>Analyte</th>
											<th>Sample Code</th>
											<th>Analysis</th>
											<?php if(Auth::user()->is_client == 0): ?>
											<th nowrap>Result</th>
											<?php endif; ?>
											
											<?php if(Auth::user()->is_client == 0): ?>
											<th>Remarks</th>
											<th>Analyst</th>
											<?php endif; ?>
											<th>Method</th>
											<?php if(Auth::user()->is_client == 0): ?>
											<th>Equipment</th>				
											<?php endif; ?>				
											<th>Sub Contracted</th>
											<th>Accredited</th>
										</tr>
									</thead>
									<tbody id="sample-parameters-holder"></tbody>
								</table>
							</div>
						</form>
						<?php if(isset($batch->status) && $batch->status == "Samples Reception"): ?>
						<form class="tab-pane fade p-3" method="POST" action="<?php echo e(route('add-analytes-to-sample-analysis')); ?>" id="add-analytes" role="tabpanel" aria-labelledby="one-tab">
							<?php echo csrf_field(); ?>
							<div class="table-responsive">
								<h5 class="mb-3">
									Available analytes for this sample
									<button class="btn btn-sm btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
								</h5>
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table">
									<thead class="bg-light p-2">
										<tr>
											<th></th>
											<th>Analyte</th>
											<th>Sample Code</th>
											<th>Analysis</th>
											<th>Analyst</th>
											<th>Equipment</th>
											<th>Contracted</th>
										</tr>
									</thead>
									<tbody id="sample-analysis-parameters-holder"></tbody>
								</table>
							</div>
						</form>
						<?php endif; ?>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
		<?php if(isset($batch->status)): ?>
			
				
				<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
			
		<?php endif; ?>
	<script>
		<?php if(isset($batch->status) && $batch->status=="Sample Approval"): ?>
			<?php if($equipment_data['captured'] > 0): ?>
				tinymce.init({
					selector: 'textarea.editor'
				});
			<?php endif; ?>
		<?php endif; ?>
		var detectChange = function(ts){
			var op = $(ts).children('option:selected');
			$('#client-unit-select').html('<option value="" selected>Select Organizational Unit...</option>');
			$('#client-unit-select').trigger('change');
			$.each(op.data('units'), function(i, e){
				$('#client-unit-select').append('<option value="'+e.name+'">'+e.name+'</option>');
			});

			$('#client-unit-select').val($('#client-unit-select').data('selected')).trigger('change');
		};

		$(function(){
			var sampleAnalysisByType = $('#sample-detail-rows').data('analysis_types')
			var sampleCondtions = $('#sample-detail-rows').data('conditions');
			var labStores = $('#sample-detail-rows').data('stores');
			var $ammendableSamples = $('#sample-detail-rows').data('ammendments');
			var $isclient = $('#sample-detail-rows').data('client');
			var $batch = $('#sample-detail-rows').data('batch');
			var standards = $('#sample-detail-rows').data('standards');
			var unitSamplePoints = [];
			var unitProducts = [];
			var clientPrefProductName;
			var clientPrefUnitName;
			var clientPrefSPName;

			var configuredSamples = $('#sample-detail-rows').data('samples');


			$('.save-samples').on('click', function(){
				var trs = $('#sample-detail-rows').find('tr').length;
				var missing = false;

				if(trs > 0){
					var missingVals = {};
					$('#sample-detail-rows').find('[required]').each(function(){
						var val = $(this).val();
						console.log($(this).attr('name'), val)
						if($.trim(val) == ""){
							var parentTD = $(this).parents('td');
							var titleTH = parentTD.parents('table').find('thead th').eq(parentTD.index());
							missingVals[titleTH.text()] = true;
							var borderStyle = $(this).css('border');
							$(this).css('border', '1px solid red').focus();
							var el = $(this);
							$(this).on('change', function(){
								el.css('border', borderStyle);
							});
						}
					});
					console.log(Object.keys(missingVals));
					if(Object.keys(missingVals).length > 0){
						alert("One or more samples is missing the following data: "+Object.keys(missingVals).join(","));
						return false;
					}
					$(this).parents('form').submit();
				}
				else{
					alert("Samples Required!")
				}
			});

			$('.raw-data-row').find('[name="result"]').on('keyup', function(){
				$(this).addClass('changed');
			});

			$('#stage-selector').find('form.dropdown-item').on('click', function(){
				$(this).submit();
			});

			$('#status-selector').find('form.dropdown-item').on('click', function(){
				$(this).submit();
			});

			$('.my-tab-headers').on('click', '.my-tab', function(){
				$('.my-tab-headers').find('.my-tab').removeClass('selected');
				$(this).addClass('selected');
				var equip = $(this).data('equipment');

				$('.equip-table').addClass('hidden');
				$('.equip-table[data-equipment="'+equip+'"]').removeClass('hidden');

			});

			$('.toggle-more-fields').on('click', function(){
				$(this).toggleClass('open');
				if($(this).hasClass('open')){
					$(this).html(`
						<i class="mdi mdi-chevron-double-up"></i> Hide Fields
					`);
					$('#more-fields').removeClass('hidden');
				}
				else{
					$(this).html(`
						<i class="mdi mdi-chevron-double-down"></i> More Fields
					`);
				$('#more-fields').addClass('hidden');
				}
			});

			$('#dispatch-to-labs-modal').find('[name="request_type_id[]"]').on('change', function(){
				var vals = $(this).val();
				var otherReasonDiv = $(this).parents('.modal-body').find('.other-reason');
				if(vals.indexOf('Other') > -1){
					otherReasonDiv.find('[name="other_type"]').attr('required', true)
					otherReasonDiv.removeClass('hidden');
				}
				else{
					otherReasonDiv.find('[name="other_type"]').val('').removeAttr('required');
					otherReasonDiv.addClass('hidden');
				}
			});

			var parametersBySampleCode = $('#sample-detail-rows').data('parameters'); //Parameters by sample code
			var analysisIDsBySampleCode = $('#sample-detail-rows').data('sample_analysis_ids');
			$('#show-sample-analysis-analytes').on('show.bs.modal', function(e){
				var sampleCode = $(e.relatedTarget).data('sample_code');
				
				$('#sample-parameters-holder').empty();
				var parameters = parametersBySampleCode[sampleCode];
				// console.log(parameters);
				var analysisIDs = analysisIDsBySampleCode[sampleCode];
				var loop = 1;
				$.each(parameters, function(p, param){
					var sampleRow = sampleCodeParameters(param,loop);
					// console.log(param);
					$('#sample-parameters-holder').append(sampleRow);
					loop = loop + 1;
				});

				$('.analytes-with-results-count').text($('#sample-parameters-holder').find('tr.has-result').length);
				$('.analytes-without-results-count').text($('#sample-parameters-holder').find('tr.no-result').length);

				$.ajax({
					url: '<?php echo e(route("missing_analysis_parameters_by_sample_code")); ?>',
					data: {
						id: JSON.stringify(analysisIDs),
						sample: sampleCode,
					},
					beforeSend: function(){
						$('#sample-analysis-parameters-holder').empty();
					},
					success: function(js){
						if(js.length == 0){
							$('#sample-analysis-parameters-holder').html(`
							<tr class="raw-data-row">
								<td colspan="7">
									<div class="alert alert-info text-center"><i class="mdi mdi-information-circle"></i> No new analytes found.</div>
								</td>
							</tr>
							`);
						}
						$.each(js, function(j,s){
							s['sample_code'] = sampleCode;
							var $row = $(`
							<tr class="raw-data-row">
								<td>
									<input type="checkbox" name="item[]" value='${JSON.stringify(s)}' />
								</td>
								<td  nowrap>${s.name+' - '+s.code}</td>
								<td  nowrap>${sampleCode}</td>
								<td  nowrap>${s.analysis_type}</td>
								<td  nowrap>${s.operator == null ? '-': s.operator}</td>
								<td  nowrap>${s.equipment == null ? '-' :s.equipment}</td>
								<td nowrap>${s.analyte_status == 1 ? '<input type="checkbox" checked disabled>' : '<input type="checkbox" disabled>'}<td>
							</tr>
							`);
							$('#sample-analysis-parameters-holder').append($row);
						});
					}
				})
			})

			$('#client-unit-select').on('change', function(){
				if($(this).val() == ''){
					return false;
				}
				$.ajax({
					url: '/fetch-unit-stuff/'+$(this).val()+'/'+$('#client-select').val(),
					beforeSend: function(){
						unitProducts = [];
						unitSamplePoints= [];
					},
					success: function(js){
						unitProducts = js.products;
						unitSamplePoints= js.sample_points;

						$('#sample-detail-rows').find('tr').find('[name="sample_details[sample_point][]"]').each(function(e){
							var rowData = $(this).parents('tr').data('sample');
							var SP = $(this);
							SP.html('<option tetet></option>');
							$.each(unitSamplePoints, function(j,s){
								SP.append(`
									<option value="${s.id}">${s.name}</option>
								`);
							});
							SP.val(rowData ? rowData.sample_point_id : '');
							SP.trigger('change');
						})

						$('#sample-detail-rows').find('tr').find('[name="sample_details[product][]"]').each(function(){
							var rowData = $(this).parents('tr').data('sample');
							var P = $(this);
							P.html('<option tetet></option>');
							$.each(unitProducts, function(j,s){
								P.append(`
									<option value="${s.id}">${s.name}</option>
								`);
							});
							P.val(rowData ? rowData.company_product_id : '');
							P.trigger('change');
						});

					}
				})
			})

			$('#client-select').on('change', function(){
				var selectedOps = $(this).children('option:selected');
				clientPrefProductName = selectedOps.data('product_name');
				clientPrefUnitName = selectedOps.data('unit_name');
				clientPrefSPName = selectedOps.data('sample_point_name');


				$('.client-prefered-unit-name').text(clientPrefUnitName)
				$('.client-preferred-sample_point-name').text(clientPrefSPName)
				$('.client-preferred-product-name').text(clientPrefProductName)
				var client_selected = $('#client-select').val();
				var client_id = 'client-'+client_selected;
				var text = document.getElementById(client_id);
				var text2 = document.getElementsByClassName('clients-data');
				
			});

			$('#client-select').trigger('change');


			$('.make-batch-changes').on('click', function(){
				if($('#sample-detail-rows').find('tr.selected-row').length == 0){
					alert("Please select the analysis rows you want to amend.");
					return false;
				}

				var modal = $(this).parents('.modal');

				if(modal.find('.bulk-checkbox:checked').length == 0){
					alert("Please select columns you want to change.");
					return false;
				}
				$('.bulk-checkbox:checked').each(function(e){
					var cls = $(this).data('name');
					var elem = modal.find(cls);

					if(elem.is("input")){
						var value = modal.find(cls).val();
						$('#sample-detail-rows').find('tr.selected-row').find(cls).each(function(e){
							$(this).val(value).trigger('change');
						});
					}
					else{
						var value =[];
						modal.find(cls).children("option:selected").each(function(e){
							value.push($(this).val());
						});

						$('#sample-detail-rows').find('tr.selected-row').find(cls).children('option').each(function(e){
							$(this).attr('selected', false).trigger('change');
						});

						$('#sample-detail-rows').find('tr.selected-row').find(cls).children('option').each(function(e){
							var sVal = $(this).val();
							if (value.indexOf(sVal) !== -1) {
								$(this).attr('selected', 'selected').trigger('change');
							}
						});
					}
				});

				modal.find('.form-control').val('').trigger('change');
				modal.find('.bulk-checkbox').removeProp('checked');
			});

			$('#provide-interpretations').on('show.bs.modal', function(e){
				var d = new Date();
				var n = d.getTime();
				var $row = $(`<div class="form-group">
					<label>Comments</label>
					<textarea class="form-control" name="header_body" placeholder="Comments..." required><?php echo e($headerDetails['header']->header_body ?? ''); ?></textarea>
				</div>
				<div class="form-group">
					<label>Recommendations / Interpretations</label>
					<textarea class="form-control" name="main_body" placeholder="Recommendations / Interpretations..." ><?php echo e($headerDetails['header']->main_body ?? ''); ?></textarea>
				</div>`).clone();

				$('#sample-interpretations-holder').html($row);

				$row.find('[name="main_body"]').attr('id', 'sample-main-body-'+n)
				$row.find('[name="header_body"]').attr('id', 'sample-header-body-'+n)

				var action = $(e.relatedTarget).data('action');
				var mainBody = $(e.relatedTarget).data('mainbody');
				var headerBody = $(e.relatedTarget).data('headerbody');
				$(this).find('form').prop('action', action);
				$(this).find('form').attr('action', action);

				$('#sample-header-body-'+n).html(headerBody)
				$('#sample-main-body-'+n).html(mainBody);
				console.log(n);
				tinymce.init({
					selector: '#sample-header-body-'+n
				});

				tinymce.init({
					selector: '#sample-main-body-'+n
				});
			});

			$('#batch-edit-modal').on('show.bs.modal', function(){
				$(this).find('.form-control').val('');
				var modal = $(this);
				modal.find('select.sample-analysis').html('');
				$.each(sampleAnalysisByType, function(s, sc){
					modal.find('select.sample-analysis').append(`<option value="${sc.id}">${sc.name}</option>`);
				});

				modal.find('select.sample-condition').html('');
				$.each(sampleCondtions, function(s, sc){
					modal.find('select.sample-condition').append(`<option value="${sc.id}">${sc.name}</option>`);
				});

				modal.find('select.sample-point').html('');
				$.each(unitSamplePoints, function(j,s){
					modal.find('select.sample-point').append(`<option value="${s.id}">${s.name}</option>`);
				});

				modal.find('select.sample-product').html('');
				$.each(unitProducts, function(j,s){
					modal.find('select.sample-product').append(`<option value="${s.id}" >${s.name}</option>`);
				});
			});

			$('#batch-info-sample-type').trigger('change');

			$('[name="is_routine"]').on('change', function(){
				if($(this).is(':checked')){
					$('#routine_frequency').removeClass('hidden');
					$('[name="routine_frequency"]').prop('required');
					$('[name="routine_frequency"]').attr('required');
				}
				else{
					$('#routine_frequency').addClass('hidden');
					$('[name="routine_frequency"]').find("option:selected").removeAttr("selected");
					$('[name="routine_frequency"]').find("option:selected").removeProp("selected");
					$('[name="routine_frequency"]').removeAttr('required');
					$('[name="routine_frequency"]').removeProp('required');
				}
			});

			$('.duplicate-sample-row').on('click', function(){
				var selectedRows = $('#sample-detail-rows').find('tr.selected-row');

				if(selectedRows.length == 0){
					alert('No row selected.');
				}
				else{
					var duplicate = prompt("Enter number of duplicates", 1);
					duplicate = duplicate || 0;

					selectedRows.each(function(i,e){
						for(var z = 0; z<duplicate; z++){
							var rowNo = $('#sample-detail-rows').find('tr').length;
							var ids = [];
							$(e).find('.sample-analysis').children('option:selected').each(function(a,b){
								ids.push($(b).val());
							})
							var data = {
								"id": "",
								"sample_code": $(e).find('.sample-code').children('option:selected').val(),
								"lab_sub_no": $(e).find('.submission-form-no').val(),
								"analysis_type_id": ids.join(','),
								"sample_condition_id": $(e).find('.sample-condition').children('option:selected').val(),
								"sample_point_id": $(e).find('.sample-point').children('option:selected').val(),
								"company_product_id": $(e).find('.sample-product').children('option:selected').val(),
								"comments": $(e).find('.sample-comments').val(),
								"barcode": $(e).find('.sample-barcode').val(),
								
								"main_standard":$(e).find('.main-standard').children('option:selected').val(),
								"secondary_standard":$(e).find('.secondary-standard').children('option:selected').val(),
								"unit_type": $(e).find('.sample-reporting-unit').children('option:selected').val(),
								"stock_in": $(e).find('.sample-quantity').val(),
								"stock_out": 0,
								
								"store_id": $(e).find('.sample-store').children('option:selected').val(),
								"slot_id": $(e).find('.sample-store-slot').children('option:selected').val(),
							};
							
							console.log(data)
							

							createRow(data);
						}
					});
				}

			});

			var createRow = function(data=false){
				var $row = $(sampleDetailsRow).clone();
				// console.log($ammeendableSamples);

				console.log(data);
				if($isclient.is_client == 1 && $batch.status != 'Samples En-Route'){
					
					$row.find('.edit-remove').empty();
					$row.find('.interpretation-remove').empty();
					$row.find('.delete-remove').empty();
				}


				$row.find('.is-required').each(function(){
					$(this).attr('required', true);
				});

				if(data){
					$row.removeClass('editable').addClass('saved-data');
					$row.data('sample', data);

					$row.find('.toggle-row-edit-mode').removeClass('text-primary').addClass('text-muted');

					$row.find('.dropdown-row').data('sample_code', data['sample_code']);

					$row.find('.provide-interpretation-row').data("action", '/sample-interpretations/'+data.id);
					$row.find('.provide-interpretation-row').data("headerbody", data.header_body);
					$row.find('.provide-interpretation-row').data("mainbody", data.main_body);
				}

				$row.find('[name="sample_details[sample_store][]"]').val(data['store_id']).trigger('change');
				$row.find('[name="sample_details[sample_store_slot][]"]').data('selected', data['slot_id']);

				

				$row.find('[name="sample_details[sample_point][]"]').html('<option></option>');

				$.each(unitSamplePoints, function(j,s){
					$row.find('[name="sample_details[sample_point][]"]').append(`
						<option value="${s.id}" ${ s.id == data['sample_point_id'] ? 'selected' : '' }>${s.name}</option>
					`);
				});

				$row.find('[name="sample_details[main_standard][]"]').html('<option></option>');
				$.each(standards,function(j,s){

					$row.find('[name="sample_details[main_standard][]"]').append(`
						<option value="${s.id}" ${ s.id === data['main_standard'] ? 'selected' : '' }>${s.code}</option>
					`);
				});
				$row.find('[name="sample_details[secondary_standard][]"]').html('<option></option>');
				$.each(standards,function(j,s){
					$row.find('[name="sample_details[secondary_standard][]"]').append(`
						<option value="${s.id}" ${ s.id === data['secondary_standard'] ? 'selected' : '' }>${s.code}</option>
					`);
				});
				$row.find('[name="sample_details[product][]"]').html('<option></option>');

				$.each(unitProducts, function(j,s){
					$row.find('[name="sample_details[product][]"]').append(`
						<option value="${s.id}" ${ s.id == data['company_product_id'] ? 'selected' : '' }>${s.name}</option>
					`);
				});

				$row.append(`<input type="hidden" value="${data.id}" name="sample_details[detail_header][]" />`);

				var rowNo = $('#sample-detail-rows').find('tr').length;

				$row.find('[name="sample_details[sample_code][]"]').val(data['sample_code']);
				$row.find('[name="sample_details[sample_no][]"]').val(data['id']);
				$row.find('[name="sample_details[submission_no][]"]').val(data['lab_sub_no']);

				$row.find('.analysis-field .form-control').empty();

				$.each(sampleAnalysisByType, function(s, sa){
					$row.find('.analysis-field .form-control').append(`<option value="${sa.id}">${sa.name}</option>`);
				});

				$row.find('.analysis-field .form-control').attr('name', 'sample_details[sample_analysis]['+rowNo+'][]');

				$row.find('.analysis-field .form-control').val(data['analysis_type_id'] ? data['analysis_type_id'].split(',') : '');

				$row.find('[name="sample_details[sample_condition][]"]').html(`<option value="">Select Condition</option>`);

				$.each(sampleCondtions, function(s, sc){
					$row.find('[name="sample_details[sample_condition][]"]').append(`<option value="${sc.id}">${sc.name}</option>`);
				});

				$row.find('select.sample-store').on('change', function(){
					var items = $(this).children("option:selected").data('items');
					$row.find('select.sample-store-slot').html(`<option></option>`).attr('placeholder', 'Select Sample Storage Slot...');

					var selected = $row.find('select.sample-store-slot').data('selected');
					$.each(items, function(i, e){
						$row.find('select.sample-store-slot').append(`<option value="${i}" ${selected == i ? 'selected' : ''}>${e}</option>`);
					});

				});

				$row.find('[name="sample_details[sample_condition][]"]').val(data['sample_condition_id']);
				$row.find('[name="sample_details[main_standard][]"]').val(data['main_standard']);
				$row.find('[name="sample_details[secondary_standard][]"]').val(data['secondary_standard']);

				$row.find('[name="sample_details[sample_quantity][]"]').val(parseFloat(data['stock_in']) - parseFloat(data['stock_out']));
				$row.find('[name="sample_details[sample_reporting_unit][]"]').val(data['unit_type']);

				$row.find('[name="sample_details[comments][]"]').val(data['comments']);
				$row.find('[name="sample_details[barcode][]"]').val(data['barcode']);
				

				$('#sample-detail-rows').append($row);

				$row.find('.toggle-row-edit-mode').on('click', function(){
					var row = $(this).parents('tr');
					row.toggleClass('editable');
					$(this).toggleClass('text-muted text-primary');
				});

				$row.find('.delete-row').on('click', function(){
					var row = $(this).parents('tr');
					var rowID = data.id;

					var rowColor = row.css('backgroundColor');

					if(confirm("Are you sure that you want to delete this row?")){
						var interval;
						var blink = true;
						if(rowID){
							$.ajax({
								url: '/delete-sample/'+rowID,
								dataType: 'json',
								method: 'POST',
								beforeSend: function(){
									interval = setInterval(function(){
										if(blink){
											row.css('background-color', '#ffd9bd')
										}
										else{
											row.css('background-color', '#ffabab')
										}
										blink=!blink;
									}, 200);
								},
								success: function(js){
									if(js.status){
										row.remove();
									}
									else{
										alert(js.message);
										clearInterval(interval);
										row.css('background-color', rowColor);
										console.log(rowColor);
									}
								},
								error: function(xhr, ajaxOptions, thrownError) {
                  console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
							})
						}
						else{
							row.remove();
						}
					}
				});

				$row.find('.select-row-check').on('change', function(){
					if($(this).is(":checked")){
						$(this).parents('tr').addClass('selected-row');
					}
					else{
						$(this).parents('tr').removeClass('selected-row');
					}

				})

				$row.find('.form-control').on('keyup', function(){
					var value = $(this).is("input") ? $(this).val() : ($(this).is("textarea") ? $(this).val() : $(this).children('option:selected').text());

					var formGroup = $(this).parents('.form-group');
					var textHolder = formGroup.siblings('span.text');

					var values = [];

					if($(this).is("select")){

						$(this).children('option:selected').each(function(i,e){
							values.push($(e).text());
						});
					}
					else{
						values.push(value);
					}

					textHolder.text(values.join(','));
				});

				$row.find('.form-control').trigger('change');
				$row.find('.form-control').trigger('keyup');

				$row.find('.form-control').on('change', function(){
					$(this).trigger('keyup');
				});


				$row.find('select').not('.no-select2').select2();
			}

			$.each(configuredSamples, function(s, sample){

				createRow(sample);
			});

			$('.create-new-sample-row').on('click', function(){
				createRow();
			});

			var fetchSampleAnalysis = function($this){
				var sampleId = $this.val();

				if(sampleId == "") return;

				sampleCondtions = $this.children('option:selected').data('conditions');

				$.ajax({
					url: '/analysis-types/'+sampleId,
					dataType: 'json',
					beforeSend: function(){
						isFetchingAnalysis = true;
						$('#preparing-details-msg').html(`
							<i class="fas fa-spinner fa-spin"></i> Loading Sample Analysis.
						`);
					},
					success: function(js){
						isFetchingAnalysis = false;
						$('#preparing-details-msg').empty();
						sampleAnalysisByType = js;

						$('#sample-detail-rows').empty();
						// $.each(samples, function(s, sample){
						// 	createRow(sample);
						// });
					}
				});
			}

			$('#batch-info-sample-type').on('change', function(){
				fetchSampleAnalysis($(this));
			});

		});

		var sampleCodeParameters = function(data,loop){
			var readonly = '';
			$('.main-standard-name').text(data.main_standard === undefined ? '' : (data.main_standard === null ? '' : data.main_standard));
			console.log(data);
			// ${readonly} ${ <?php echo e(Auth::user()->id); ?> != (data.def_operator ? data.def_operator.id : 0) ? 'readonly' : '' } //results validator by user
			<?php if(isset($batch->status) && $batch->status != "Samples In Lab"): ?>
				readonly = 'disabled';
			<?php endif; ?>

			var $oGRow = $(`
				<tr class="raw-data-row ${data.result == null ? 'no-result' : 'has-result'}" >
					<?php if(isset($batch->status) && $batch->status != 'Samples In Lab' && Auth::user()->is_client == 0): ?>
					<td class="remove-analyte-row"><span class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="bottom" title="delete"><i class="mdi mdi-trash-can-outline text-danger"></i></span></td>
					<?php else: ?>
					<td></td>
					<?php endif; ?>
					<td  nowrap><input type="hidden" name="captured_result_id[]" value="${data.id}">${data.analyte_code}</td>
					<td  nowrap>${data.sample_detail_code}</td>
					<td  nowrap>${data.analysis_type.code}</td>
					<?php if(Auth::user()->is_client == 0): ?>
					<td>
						<div class="form-group">
							<input id="${data.sample_detail_code},${data.analyte_code},${data.id}" style="min-width: 150px" type="text"
							class="form-control first-result" value="${data.result == null ? '' : data.result}" name="result[${data.id}]" placeholder="Result..." />
							<input type="hidden" name="result_confirm"  />
						</div>
					</td>
					
					<td nowrap>
					<div class="form-group" name="remarks" placeholder="Select Remark...">
					<select style="min-width: 150px" class="form-control item-remark" data-colour="${data.remark_colour ? data.remark_colour : ''}"  name="remark[${data.id}]" placeholder="Select Remark..." data-selected="${data.remark ? data.remark : ''}"></select>
						</div>
					</td>
					
					<td>
						<div class="form-group" name="operators" placeholder="Select Operator...">
							<select style="min-width: 150px" class="form-control item-operators"  name="operators[${data.id}]" placeholder="Select Operator..."  data-selected="${data.def_operator ? data.def_operator.id : 0 }"></select>
						</div>
					</td>
					<?php endif; ?>
					<td  nowrap>${data.method}</td>
					<?php if(Auth::user()->is_client == 0): ?>
					<td  nowrap>${data.equip_name}</td>
					<?php endif; ?>
					<td class="text-center" nowrap>
					<div class="form-group">
					<input class="form-check" type="checkbox" <?php echo e(Auth::user()->is_client == 1 ? 'disabled' : ''); ?>  name="subcontracted[${data.id}]" ${data.analyte_status_contracted  == 1 ? 'checked':''}/>
					</div>
					</td>
					<td class="text-small">${data.analyte_accredited == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'}</td>
				</tr>
			`);

			var $row = $oGRow.clone();
			$row.on('keypress','.first-result',function(e){
				if (e.which == 13) {
					e.preventDefault();
					var looped = loop + 1;
					var nextInput = getElementById('result-'+looped);
					if (nextInput) {
					nextInput.focus();
					}
				}
			});
			$row.on('change', '.first-result', function(){
				var result_confirm = prompt('Please confirm the result:');
				var current_result = $(this).val();

				if(result_confirm != current_result){
					alert("Result confirmation didn't match captured result!");
					$(this).removeClass('changed').removeClass('clean');
					$(this).siblings('[name="result_confirm"]').val('');
					$(this).val("");
				}
				else{
					$(this).siblings('[name="result_confirm"]').val(result_confirm);
					$(this).removeClass('clean').addClass('changed');

					var tt = this.id.split(',');
					var y = tt.splice(1,1);
					var tt_str = tt.toString();
					console.log(tt_str);

					var res = tt_str.replace(/,/g,'-');

					
				}
			});

			var selectedremark = $row.find('select.item-remark').data('selected');
			var selectedcolor = $row.find('select.item-remark').data('colour');
			$row.find('select.item-remark').empty();
			$row.find('select.item-remark').append(`<option data-colour="" class="badge" value="">Select Remark...</option>`)
			// console.log(data);
			$.each(data.remarks, function(o,p){
				$row.find('select.item-remark').append(`<option data-colour="${p.value}" class="badge ${p.value}" value="${p.id}">${p.key}</option>`)
			});
			$row.find('select.item-remark').addClass(selectedcolor);
			$row.find('select.item-remark').val(selectedremark).trigger('change');

			var previous_color = selectedcolor;
			$row.on('change','select.item-remark',function(){
				var colour = $row.find('select.item-remark').children('option:selected').data('colour');
				$row.find('select.item-remark').removeClass(previous_color);
				$row.find('select.item-remark').addClass(colour);
				previous_color = colour;
			})


			var selectedOperator = $row.find('select.item-operators').data('selected');
			$row.find('select.item-operators').empty();
			$.each(data.ops, function(o,p){
				$row.find('select.item-operators').append(`<option value="${p.id}">${p.name}</option>`)
			});
			$row.find('select.item-operators').val(selectedOperator).trigger('change');
			var $token   = $('meta[name="csrf-token"]').attr('content');
			$row.find('.remove-analyte-row').on('click', function(){
				if(confirm("Are you sure that you want to remove this analyte?")){
					$.ajax({
						url: "<?php echo e(route('remove-analyte-from-captured-result')); ?>",
						dataType: 'json',
						data: {
							_token: $token,
							id: data.id
						},
						type: "POST",
						success: function(js){
							if(js.status){
								$row.remove();
							}
							else{
								alert("Couldn't remove analyte!")
							}
						}
					})
				}
			});

			return $row;
		}

		var sampleDetailsRow = `<tr class="editable">
			<td>
			<input type="checkbox" class="select-row-check mt-1" /></td>
			<td class="block toolbar" nowrap>
				<?php if(isset($batch->status) && in_array($batch->status, array("Samples En-Route" ,"Samples Reception"))): ?>
					<span class="btn edit-remove btn-default text-primary btn-sm toggle-row-edit-mode" data-toggle="tooltip" title="Edit"><i class="mdi mdi-lead-pencil"></i></span> &nbsp;
					<span class="btn btn-default delete-remove text-danger btn-sm delete-row" data-toggle="tooltip" title="Delete"><i class="mdi mdi-trash-can"></i></span>
				<?php endif; ?>
				<?php if(isset($batch->status) && in_array($batch->status, array("Sample Approval","Samples In Lab","Sample Verification"))): ?>
					<span class="btn btn-default interpretation-remove text-success btn-sm provide-interpretation-row" data-target="#provide-interpretations" data-toggle="modal"  data-toggle="tooltip" title="Comments and Interpretation"><i class="mdi mdi-android-messages"></i></span>
				<?php endif; ?>
				<span class="btn btn-default parameter-remove text-info btn-sm dropdown-row" data-target="#show-sample-analysis-analytes" data-toggle="modal" data-toggle="tooltip" title="Parameters" ><i class="mdi mdi-snowflake"></i></span>
			</td>
			<td class="sample-code-field" nowrap>
				<div class="form-group form-group-sm">
					<input type="text" style="width: 70px" class="form-control form-control-sm sample-code" name="sample_details[sample_no][]" readonly="true" />
				</div>
				<span class="text"></span>
			</td>
			<td class="sample-code-field" nowrap>
				<div class="form-group form-group-sm">
					<input type="text" style="width: 70px" class="form-control form-control-sm sample-code" name="sample_details[sample_code][]" readonly="true" />
				</div>
				<span class="text"></span>
			</td>
			<td class="analysis-field" nowrap>
				<div class="form-group form-group-sm">
					<select class="form-control form-control-sm is-required sample-analysis" multiple style="width: 200px" placeholder="Select Analysis...">
						<?php if($selectedSampleType): ?>
							<?php $__currentLoopData = $selectedSampleType->analysis_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typ): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($typ->id); ?>"><?php echo e($typ->name); ?></option>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						<?php endif; ?>
					</select>
				</div>
				<span class="text"></span>
			</td>
			<td class="sample-condition-field">
				<div class="form-group form-group-sm">
					<select class="form-control form-control-sm is-required sample-condition" name="sample_details[sample_condition][]" style="width: 200px" placeholder="Select Sample Condition..." required>
						<?php if($selectedSampleType): ?>
							<?php $__currentLoopData = $selectedSampleType->sample_condition; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $con): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($con->id); ?>"><?php echo e($con->name); ?></option>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						<?php endif; ?>
					</select>
				</div>
				<span class="text"></span>
			</td>
			<td class="sample-sample_point-field">
				<div class="form-group form-group-sm">
					<select class="form-control form-control-sm  is-required sample-point" name="sample_details[sample_point][]" style="width: 200px" placeholder="Select..." required></select>
				</div>
				<span class="text"></span>
			</td>
			<td class="sample-product-field">
				<div class="form-group form-group-sm">
					<select class="form-control form-control-sm is-required sample-product" name="sample_details[product][]" style="width: 200px" placeholder="Select..." required></select>
				</div>
				<span class="text"></span>
			</td>
			<td class ="main-standard-field">
				<div class="form-group form-group-sm">
					<select class="form-control form-control-sm is-required main-standard" name="sample_details[main_standard][]" style"width:200px" placeholder="Select Main Standard..." required >
					<?php if($standards): ?>
						<?php $__currentLoopData = $standards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $standard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($standard->id); ?>"><?php echo e($standard->code); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					<?php endif; ?>
					</select>
				</div>
				<span class="text"></span>
			</td>
			<td class ="secondary-standard-field">
				<div class="form-group form-group-sm">
					<select class="form-control form-control-sm is-required secondary-standard" name="sample_details[secondary_standard][]" style"width:200px" placeholder="Select Sec Standard..." required>
					<?php if($standards): ?>
						<?php $__currentLoopData = $standards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $standard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($standard->id); ?>"><?php echo e($standard->code); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					<?php endif; ?>
					</select>
				</div>
				<span class="text"></span>
			</td>
			<td class="submission-form-field" nowrap>
				<div class="form-group form-group-sm">
					<input type="text" style="width: 100px" class="form-control form-control-sm submission-form-no" name="sample_details[submission_no][]"  />
				</div>
				<span class="text"></span>
			</td>
			<td class="comments-field" nowrap>
				<div class="form-group form-group-sm">
					<textarea rows="1" style="width: 200px" class="form-control form-control-sm sample-comments" name="sample_details[comments][]" placeholder="Sample Comments..."></textarea>
				</div>
				<span class="text"></span>
			</td>
			<td class="sample-store-field" nowrap>
				<div class="form-group form-group-sm">
					<select class="form-control form-control-sm sample-store" name="sample_details[sample_store][]" style="width: 200px" placeholder="Select Sample Storage...">
						<option></option>
						<?php if($labStores): ?>
							<?php $__currentLoopData = $labStores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($store['id']); ?>" data-items="<?php echo e(json_encode($store['items'])); ?>"><?php echo e($store['name']); ?></option>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						<?php endif; ?>
					</select>
				</div>
				<span class="text"></span>
			</td>
			<td class="sample-slot-field" nowrap>
				<div class="form-group form-group-sm">
					<select class="form-control form-control-sm sample-store-slot" name="sample_details[sample_store_slot][]" style="width: 200px !important" placeholder="Select a Srore First...">
						<option></option>
					</select>
				</div>
				<span class="text"></span>
			</td>
			
			<td class="sample-quantity-field">
				<div class="form-group form-group-sm">
					<input type="number" min="0" style="width: 200px !important" class="form-control form-control-sm sample-quantity"  name="sample_details[sample_quantity][]" placeholder="Sample Quantity..." />
				</div>
				<span class="text"></span>
			</td>
			<td class="sample-reporting-unit-field">
				<div class="form-group form-group-sm">
					<select class="form-control form-control-sm sample-reporting-unit"  name="sample_details[sample_reporting_unit][]" style="width: 200px !important" placeholder="Select Sample Reporting Unit...">
						<option></option>
						<?php if($reportingUnits): ?>
							<?php $__currentLoopData = $reportingUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($unit['name']); ?>"><?php echo e($unit['name']); ?></option>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						<?php endif; ?>
					</select>
				</div>
				<span class="text"></span>
			</td>
			<td class="barcode-field">
				<div class="form-group form-group-sm">
					<input type="text" style="width: 100px" class="form-control form-control-sm sample-barcode" name="sample_details[barcode][]" placeholder="BarCode..." />
				</div>
				<span class="text"></span>
			</td>
			

		</tr>`;
	</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make($defaultClient ? ($client_portal || Auth::user()->is_client == 1 ? 'layouts.crm.dashboard.layout.app':'layouts.crm.layout.app') : 'layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true, 'datePicker'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/sample-workflow/general_show.blade.php ENDPATH**/ ?>