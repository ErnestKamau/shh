{{-- Quick-add modals for Batch details Client / Contact / Company Section (+) --}}
<div class="modal fade" id="add-customer-contact" role="dialog" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form action="{{ route('customer-contact-add-ajax') }}" method="POST">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Company Contact</h4>
					<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="row border-bottom">
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label">Customer</label>
								<select name="customer_id" class="form-control no-select2 crm_customer_id">
									<option value="">Select Customer...</option>
									@foreach($clients as $client)
										<option value="{{ $client->id }}">{{ $client->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label">Title *</label>
								<select name="title" class="form-control no-select2" required>
									<option value=""></option>
									@foreach (getModulePreconfig('Designation', 'Personnel-Management') as $item)
										<option value="{{ $item->id }}">{{ $item->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label">First Name <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="first_name" placeholder="First Name..." required>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label">Middle Name</label>
								<input type="text" class="form-control" name="second_name" placeholder="Middle Name...">
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label">Surname</label>
								<input type="text" class="form-control" name="third_name" placeholder="Surname...">
							</div>
						</div>
					</div>

					<div class="row border-bottom">
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label">Job Occupation</label>
								<input type="text" class="form-control" name="job_occupation" placeholder="Job Title...">
							</div>
							<input type="hidden" name="not_ajax" value="1">
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label">Company Units <span class="text-danger">*</span></label>
								<select required class="form-control no-select2 crm_unit_id" id="add-contact-company-units"
									name="unit_name[]" multiple="multiple" style="width: 100%;"
									data-placeholder="Select Company Unit...">
								</select>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label">Email <span class="text-danger">*</span></label>
								<input type="email" class="form-control" name="email" placeholder="Email..." required>
							</div>
						</div>
					</div>

					<div class="row border-bottom">
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label">Telephone <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="telephone" placeholder="Telephone..." required>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label class="control-label">Mobile</label>
								<input type="text" class="form-control" name="mobile" placeholder="Mobile...">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-4">
							<div class="form-group" style="padding-top: 24px">
								<label class="control-label"><input type="checkbox" checked value="1" name="receive_price_list"> Receives Pricelist?</label>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group" style="padding-top: 24px">
								<label class="control-label"><input type="checkbox" checked value="1" name="receive_report"> Receives Report?</label>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group" style="padding-top: 24px">
								<label class="control-label"><input type="checkbox" checked value="1" name="receive_invoice"> Receives Invoice?</label>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label class="control-label"><input type="checkbox" value="1" checked name="active"> Is Active?</label>
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
</div>

<div id="add-company-unit" class="modal fade" role="dialog" tabindex="-1">
	<div class="modal-dialog">
		<form class="modal-content" id="batch-add-company-unit-form" method="POST" action="" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Company Section</h4>
				<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
			</div>
			<div class="modal-body">
				<div id="batch-add-unit-client-alert" class="alert alert-danger p-2 d-none" role="alert">
					Kindly select the client first.
				</div>
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" placeholder="Name..." required>
				</div>
				<div class="form-group">
					<label class="control-label"><input type="checkbox" value="1" name="active" checked> Is Active?</label>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" id="save-unit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div id="add-customer" class="modal fade" role="dialog" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<form class="modal-content" method="POST" action="{{ route('add-customers') }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Customer</h4>
				<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
			</div>
			<div class="modal-body row">
				<div class="col-sm-6">
					<div class="form-group">
						<label class="control-label">Name <span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="name" placeholder="Name..." required>
					</div>
					<div class="form-group">
						<label class="control-label">Postal Address <span class="text-danger">*</span></label>
						<textarea class="form-control" name="postal_address" placeholder="Postal Address..."></textarea>
					</div>
					<div class="form-group">
						<label class="control-label">Physical Address <span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="physical_address" placeholder="Location..." required>
					</div>
					<div class="form-group">
						<label class="control-label">Website</label>
						<input type="text" class="form-control" name="website" placeholder="Website...">
					</div>
					<div class="form-group">
						<label class="control-label">KRA PIN</label>
						<input type="text" name="vat_no" class="form-control" placeholder="KRA PIN....">
					</div>
					<div class="form-group">
						<label class="control-label">Country</label>
						<select class="form-control" name="country_id">
							@foreach ($countries as $c)
								<option value="{{ $c->id }}">{{ $c->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="row">
						<div class="col-sm-6">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" name="lpos_required" value="1">
								<label class="form-check-label">Lpo Required?</label>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-check">
								<input type="checkbox" class="form-check-input" checked value="1" name="active">
								<label class="form-check-label">Is Active?</label>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<label class="control-label">Fax</label>
						<input type="text" class="form-control" name="fax" placeholder="Fax...">
					</div>
					<div class="form-group">
						<label class="control-label">Email <span class="text-danger">*</span></label>
						<input type="email" class="form-control" name="email" placeholder="Email..." required>
					</div>
					<div class="form-group">
						<label class="control-label">Phone 1 <span class="text-danger">*</span></label>
						<input type="tel" class="form-control" name="phone1" placeholder="Phone 1..." required>
					</div>
					<div class="form-group">
						<label class="control-label">Phone 2</label>
						<input type="tel" class="form-control" name="phone2" placeholder="Phone 2...">
					</div>
					<div class="form-group">
						<label class="control-label">Credit days</label>
						<input type="number" name="credit_day" class="form-control">
					</div>
					<div class="form-group">
						<label class="control-label">Account Setting <span class="text-danger">*</span></label>
						<select class="form-control" name="account_id" required>
							<option value="">Choose Account Settings</option>
							@foreach ($accounts as $account)
								<option value="{{ $account->id }}">{{ $account->key }}</option>
							@endforeach
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
