<?php $__env->startSection('title2'); ?>
<title>Customer List | CRM</title>
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
		<i class="mdi mdi-account-group"></i> Customer List
		<?php if(isset($account_settings->id)): ?>
		<button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-customer"><i class="mdi mdi-plus"></i> Add</button>
		<?php else: ?>
		<a href="<?php echo e(route('add-config-customer')); ?>" class="btn btn-sm btn-outline-primary float-right"><i class="mdi mdi-plus"></i> Add</a>
		<?php endif; ?>
	</h2>
	<br>
	<div class="table-responsive bg-light p-4">
		<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
			<thead class="bg-light p-2">
				<tr>
					<th style="min-width: 70px !important;"></th>

					<th>Code</th>
					<th>Name</th>
					<th>Postal Address</th>
					<th>Physical Address</th>
					<th>Website</th>
					<th>Fax</th>
					<th>KRA Pin</th>
					<th>Email</th>
					<th>Phone 1</th>
					<th>Phone 2</th>
					<th>Country</th>
					<th>Active?</th>
				</tr>
			</thead>
			<tbody>
				<?php if(count($customers) > 0): ?>
				<?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				<tr>
					<td nowrap>
						<span class="btn btn-outline-primary btn-sm" data-customer="<?php echo e(json_encode($customer)); ?>" data-target="#edit-customer" data-toggle="modal" data-toggle="tooltip" title="Edit"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </span>
						<a class="btn btn-outline-success btn-sm" href="<?php echo e(route('show-customer', ['id'=>$customer->id])); ?>" data-toggle="tooltip" title="View"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
						<span class="btn btn-outline-danger btn-sm <?php echo e(auth()->user()->is_support_staff == 0 ? 'hidden' : ''); ?>" data-toggle="modal" data-customer="<?php echo e(json_encode($customer)); ?>" data-target="#delete-customer" data-toggle="tooltip" title="Delete"><i class="mdi mdi-delete-empty"></i></span>
						
					</td>
					<td>
						<a href="<?php echo e(route('show-customer', ['id'=>$customer->id])); ?>"><?php echo e($customer->code); ?> </a>
					</td>
					<td><?php echo e($customer->name); ?></td>
					<td><?php echo e($customer->postal_address); ?></td>
					<td><?php echo e($customer->physical_address); ?></td>
					<td><?php echo e($customer->website); ?></td>
					<td><?php echo e($customer->fax ?? '-'); ?></td>
					<td><?php echo e($customer->vat_no); ?></td>
					<td><?php echo e($customer->email); ?></td>
					<td><?php echo e($customer->telephone1 ?? '-'); ?></td>
					<td><?php echo e($customer->telephone2 ?? '-'); ?></td>
					<td><?php echo e($customer->country->name); ?></td>
					<td class="text-small"><?php echo $customer->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>

				</tr>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php if(count($customers) == 0): ?>
		<div class="alert alert-info">
			<i class="mdi mdi-alert"></i> No Customers added yet.
		</div>
		<?php endif; ?>
	</div>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
<div id="edit-customer" data-account="<?php echo e(json_encode($accounts)); ?>" data-country="<?php echo e(json_encode($countries)); ?>" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body row">
				
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="delete-customer" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="<?php echo e(route('delete_customer')); ?>" method="post">
				<?php echo csrf_field(); ?>
				<div class="modal-body">

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-content-save"></i> Delete</button>
					<button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="name-check-modal" style="z-index:10000" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div class="alert alert-primary p-2 d-flex">
					<i class="mdi mdi-alert-decagram mdi-36px"></i>
					<p class="p-2">
						Below are customers with the same name as the name provided on the name input field!
					</p>
				</div>
				<div class="data">
					<table class="table table-sm table-stripped table-bordered" id="name-check-tbody">
						<thead>
							<tr>
								<th>#</th>
								<th>Name</th>
							</tr>
							
						</thead>
						<tbody>

						</tbody>
					</table>

				</div>
			</div>
			<div class="modal-footer">
				<span class="btn btn-sm btn-outline-primary" data-dismiss="modal">Close</span>
			</div>
		</div>
	</div>
</div>
<div id="add-customer" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('add-customers')); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title">
					<i class="mdi mdi-plus"></i> Add Customer
				</h4>
				<span class="btn btn-sm btn-default text-primary bg-light float-right hidden name-check-listener" data-toggle="modal" data-target="#name-check-modal" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;"> <i class="mdi mdi-account-search"></i> Name Check</span>
			</div>
			<div class="modal-body row">
				<div class="col-sm-6">
					<div class="form-group">
						<label class="control-label">Name <span class="text-danger">*</span></label>
						<input type="text" class="form-control name-check-trigger" name="name" placeholder="Name..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Postal Address <span class="text-danger">*</span></label>
						<textarea class="form-control" name="postal_address" placeholder="Postal Address..."></textarea>
					</div>
					<div class="form-group">
						<label class="control-label">Physical Address <span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="physical_address" placeholder="Location..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Website </label>
						<input type="text" class="form-control" name="website" placeholder="Website..." />
					</div>
					<div class="form-group">
						<label class="control-label">Country</label>
						<select class="form-control" name="country_id" data-placeholder>
							<?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						</select>
					</div>
					<div class="form-group">
						<label for="" class="control-label">KRA PIN </label>
						<input type="text" name="vat_no" class="form-control">
					</div>
					<div class="row">
						<div class="col-sm-6">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="lpos_required" value="1" />
								<label class="form-check-label">
									Lpo Required?
								</label>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" value="1" checked name="active" />
								<label class="form-check-label"> Is Active?</label>
							</div>
						</div>
					</div>


				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label class="control-label">Fax</label>
						<input type="text" class="form-control" name="fax" placeholder="Fax..." />
					</div>
					<div class="form-group">
						<label class="control-label">Email <span class="text-danger">*</span></label>
						<input type="email" class="form-control" name="email" placeholder="Email..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Phone 1 <span class="text-danger">*</span></label>
						<input type="tel" class="form-control" name="phone1" value="" placeholder="Phone 1..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Phone 2 </label>
						<input type="tel" class="form-control" name="phone2" value="" placeholder="Phone 2..." />
					</div>
					<div class="form-group">
						<label class="control-label">Credit days</label>
						<input type="number" name="credit_day" class="form-control" />
					</div>
					<div class="form-group">
						<label class="control-label">Account Setting <span class="text-danger">*</span></label>
						<select class="form-control" name="account_id" required data-placeholder>
							<option value="">Choose Account Settings</option>
							<?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<option value="<?php echo e($account->id); ?>"><?php echo e($account->key); ?></option>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						</select>
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



<script>

	$(function() {
		var edit_body = function(customer){
			var body_ = $(`
				<div class="col-sm-6">
					<div class="form-group">
						<label class="control-label">Name</label>
						<input type="text" class="form-control" name="name" value="${customer.name }" placeholder="Name..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Postal Address</label>
						<textarea class="form-control" name="postal_address" placeholder="Postal Address..." required>${customer.postal_address }</textarea>
					</div>
					<div class="form-group">
						<label class="control-label">Physical Address</label>
						<input type="text" class="form-control" name="physical_address" value="${customer.physical_address }" placeholder="Location..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Website</label>
						<input type="text" class="form-control" name="website" value="${ customer.website }" placeholder="Website..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Country</label>
						<select class="form-control" name="country_id" data-placeholder>

						</select>
					</div>
					<div class="form-group">
						<label for="" class="control-label">KRA PIN </label>
						<input type="text" name="vat_no" value="${customer.vat_no}" class="form-control">
					</div>
					<div class="row">
						<div class="col-sm-6">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" class="form-control" name="lpos_required" value="1" ${customer.lpos_required==1 ? `checked`: ``} />
								<label class="form-check-label">
									Lpo Required?
								</label>
							</div>

						</div>
						<div class="col-sm-6">
							<div class="form-check">
								<input type="checkbox" value="1" class="form-check-input" name="active" ${customer.active == 1 ? `checked` : `` } />
								<label class="form-check-label"> Is Active?</label>
							</div>
						</div>
					</div>

				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label class="control-label">Fax</label>
						<input type="fax" class="form-control" name="fax" value="${customer.fax }" placeholder="Fax..." />
					</div>
					<div class="form-group">
						<label class="control-label">Email</label>
						<input type="email" class="form-control" name="email" value="${customer.email}" placeholder="Email..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Phone 1</label>
						<input type="tel" class="form-control" name="phone1" value="${customer.telephone1}" placeholder="Phone 1..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Phone 2</label>
						<input type="tel" class="form-control" name="phone2" value="${customer.telephone2}" placeholder="Phone 2..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Credit days</label>
						<input type="number" name="credit_day" value="${customer.credit_days}" class="form-control" />
					</div>
					<div class="form-group">
						<label class="control-label">Account Setting</label>
						<select class="form-control" name="account_id" data-placeholder>

						</select>
					</div>

				</div>
			`).clone()
			return body_
		}
		$('#edit-customer').on('show.bs.modal',function(e){
			$('#edit-customer').find('.modal-body').empty();
			var customer = $(e.relatedTarget).data('customer');
			var account = $(this).data('account');
			var countries = $(this).data('country');
			var editBody = edit_body(customer);
			$('#edit-customer').find('form').attr('action','/customer/'+customer.id);
			$(this).find('.modal-title').empty();
			var header_ = `<i class="mdi mdi-pencil-outline"></i> Edit Customer ${customer.name}`;
			$(this).find('.modal-title').append(header_);
			$.each(account,function(i,j){
				var option_ =  $(`<option value="${j.id}" ${j.id == customer.account_status ? `selected` : ``}>${j.value}</option>`).clone();
				$(editBody).find('select[name="account_id"]').append(option_);
			})
			$(editBody).find('select[name="account_id"]').select2();
			$.each(countries,function(i,c){
				var option_ =  $(`<option value="${c.id}" ${c.id == customer.country_id ? `selected` : ``}>${c.name}</option>`).clone();
				$(editBody).find('select[name="country_id"]').append(option_);
			});
			$(editBody).find('select[name="country_id"]').select2();
			$('#edit-customer').find('.modal-body').append(editBody);
		})
		$('#delete-customer').on('show.bs.modal', function(e) {
			var customer = $(e.relatedTarget).data('customer');
			$(this).find('.modal-body').empty();
			var text_ = `
			<div class="alert alert-danger p-3">
				<input type="hidden" name="customer_id" value="${customer.id}">
				<i class="mdi mdi-alert-decagram ml-3"></i> Confirm you want to delete <b>Customer ${customer.name}</b>
			</div>
			`;
			$(this).find('.modal-body').append(text_);
			// console.log(customer);
		});
		$('#add-customer').on('show.bs.modal',(e)=>{
			console.log('here1.....')
			$('#add-customer').find('.name-check-trigger').on('change',(e)=>{
				console.log('here2.....')
				$.each($('#add-customer').find('.form-control'),(i,obj)=>{
					$(obj).attr('readonly',true)
					$('.name-check-listener').removeClass('hidden');
				});
			})
		});
		let nameCheckTbodyData = (loop,data)=>{
			var body=$(`
				<tr>
					<td>${loop}</td>
					<td>${data.name}</td>
				</tr>
			`).clone();
			return body;
		}
		$('#name-check-modal').on('show.bs.modal',(e)=>{
			var current_name = $('#add-customer').find('.name-check-trigger').val();
			$('#name-check-modal').find('#name-check-tbody').find('tbody').empty();
			$.ajax({
				url:`/validate-Crm-Customer/Name/${current_name}/Ajax`,
				method:'GET',
				success:(data)=>{
					$loop = 1;
					if(data.length > 0){
						$('#name-check-modal').find('#name-check-tbody').find('tbody').empty();
						$.each(data,(i,obj)=>{
							var body = nameCheckTbodyData($loop,obj)
							$('#name-check-modal').find('#name-check-tbody').find('tbody').append(body);
							++$loop
						})
					}else{
						var body=$(`<tr><td colspan="2" class="text-center">No Data Available ....</td></tr>`).clone();
						$('#name-check-modal').find('#name-check-tbody').find('tbody').append(body);

					}
					$.each($('#add-customer').find('.form-control'),(i,obj)=>{
						$(obj).removeAttr('readonly')
						$('.name-check-listener').addClass('hidden');
					});
					
				},
				error:(data)=>{
					console.log(data);
				}
			});
		})
	})
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/crm/index.blade.php ENDPATH**/ ?>