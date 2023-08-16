<?php $__env->startSection('title2'); ?>
  <title>Analytes</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
  <main>
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab Management',
					'icon' => null
				),
				array(
          'link' => route('analytes'),
          'name' => 'Analytes',
          'icon' => null
        ),
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
      <i class="mdi mdi-molecule"></i> Analytes
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-analyte"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Code</th>
            <th>Name</th>
            <th nowrap>Common Name</th>
            <th nowrap>Decimal Places</th>
            <th nowrap>Equivalent Weight</th>
            <th nowrap>Reporting Symbol</th>
            <th nowrap>Reporting Unit</th>
            <th>Method</th>
            <th>Equipment</th>
            <th nowrap>Non Detectable</th>
            <th nowrap>Non Accredited</th>
            <th nowrap>Show on Report</th>
            <th>Active?</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
					<?php $__currentLoopData = $analytes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analyte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<?php
							$methods = $analyte->methods();
							$equipments = $analyte->equipments();
						?>
						<tr>
							<td valign="center"><?php echo e($loop->iteration); ?> </td>
							<td><?php echo e($analyte->code); ?></td>
							<td><span class="text-primary btn" style="padding: 0px !important;font-size:13px" data-methods='<?php echo e(json_encode(array_values($methods))); ?>' data-equipments='<?php echo e(json_encode(array_values($equipments))); ?>' data-analyte='<?php echo e(json_encode($analyte)); ?>' data-target="#edit-analyte" data-toggle="modal"><?php echo e($analyte->name); ?></span> </td>
							<td><?php echo e($analyte->common_name); ?></td>
							<td><?php echo e($analyte->decimal_places); ?></td>
							<td><?php echo e(number_format($analyte->equivalent_weight, $analyte->decimal_places)); ?></td>
							<td><?php echo e($analyte->reporting_symbol); ?></td>
							<td><?php echo e($analyte->reporting_unit); ?></td>
							<td><?php echo e(implode(", ", array_keys($methods)) ?? '-'); ?></td>
							<td><?php echo e(implode(", ", array_keys($equipments)) ?? '-'); ?></td>
							<td class="text-small"><?php echo $analyte->non_detectable == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
							<td class="text-small"><?php echo $analyte->non_accredited == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
							<td class="text-small"><?php echo $analyte->show_on_report == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
							<td class="text-small"><?php echo $analyte->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
							<td nowrap>
								<button class="btn btn-primary btn-sm" data-methods='<?php echo e(json_encode(array_values($methods))); ?>' data-equipments='<?php echo e(json_encode(array_values($equipments))); ?>' data-analyte='<?php echo e(json_encode($analyte)); ?>' data-target="#edit-analyte" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
								
							</td>
						</tr>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
  <div id="add-analyte" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content modal-lg" method="POST" action="<?php echo e(route('add-analytes')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Analyte</h4>
        </div>
        <div class="modal-body row">
          <div class="col-sm-6">
            <div class="form-group">
              <label class="control-label">Analyte Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" placeholder="Analyte Name..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Common Name</label>
              <input type="text" class="form-control" name="common_name" placeholder="Common Name..." />
            </div>
            <div class="form-group">
              <label class="control-label">Analyte Code <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="code" placeholder="Analyte Code..." required />
			</div>
			<div class="form-group">
              <label class="control-label">Equivalent Weight</label>
              <input type="text" class="form-control" name="equivalent_weight" placeholder="Equivalent Weight..." />
            </div>
            <div class="form-group">
              <label class="control-label">Analyte Decimal Places <span class="text-danger">*</span></label>
              <input type="number" min="0" step="1" class="form-control" name="decimal_places" value="0" placeholder="Analyte Decimal Places..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Analyte Reporting Symbol</label>
              <input type="text" class="form-control" name="reporting_symbol" placeholder="Analyte Reporting Symbol..." />
            </div>
            <div class="form-group">
              <label class="control-label">Reporting Unit</label>
              <select class="form-control" name="reporting_unit">
                <option value="">Select Reporting Unit...</option>
                <?php $__currentLoopData = getReportingUnits(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($g['name']); ?>"><?php echo e($g['name']); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label class="control-label">Method</label>
              <select class="form-control" name="method[]" multiple placeholder="Select Method...">
                <option></option>
                <?php $__currentLoopData = getMethods(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($g['id']); ?>"><?php echo e($g['name']); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="form-group">
              <label class="control-label">Equipment </label>
              <select class="form-control" name="equipment_id[]" multiple placeholder="Select Equipment...">
                <option></option>
                <?php $__currentLoopData = getEquipment(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($g['id']); ?>"><?php echo e($g['name']); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" name="non_detectable" value="1" />  Not Detectable</label>
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" name="non_accredited" value="1" />  Accredited</label>
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" name="show_on_report" value="1" checked /> Show on Report</label>
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
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
	<div id="edit-analyte" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<!-- Modal content-->
			<form class="modal-content" method="POST" enctype="multipart/form-data">
				<?php echo csrf_field(); ?>
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Analyte</h4>
				</div>
				<div class="modal-body row"></div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<script>
		$(function(){
			var formBody = function($analyte, $methods, $equipments){
				var $fB = $(`
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Analyte Name <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="name" value="${ $analyte.name }" placeholder="Analyte Name..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Common Name <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="common_name" value="${ $analyte.common_name }" placeholder="Analyte Name..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Analyte Code <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="code" value="${ $analyte.code }" placeholder="Analyte Code..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Equivalent Weight</label>
							<input type="text" class="form-control" name="equivalent_weight" value="${$analyte.equivalent_weight == null ? '' : $analyte.equivalent_weight}" placeholder="Equivalent Weight..." />
						</div>
						<div class="form-group">
							<label class="control-label">Analyte Decimal Places <span class="text-danger">*</span></label>
							<input type="number" min="0" step="1" class="form-control" name="decimal_places" value="${ $analyte.decimal_places }" placeholder="Analyte Decimal Places..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Analyte Reporting Symbol</label>
							<input type="text" class="form-control" name="reporting_symbol" value="${ $analyte.reporting_symbol }" placeholder="Analyte Reporting Symbol..." />
						</div>
						<div class="form-group">
							<label class="control-label">Reporting Unit</label>
							<select class="form-control" name="reporting_unit">
								<option value="">Select Reporting Unit...</option>
								<?php $__currentLoopData = getReportingUnits(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($g['name']); ?>" ${ $analyte.reporting_unit == '<?php echo e($g['name']); ?>' ? 'selected' : '' }><?php echo e($g['name']); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Method</label>
							<select class="form-control" name="method[]" multiple placeholder="Select Method...">
								<option></option>
								<?php $__currentLoopData = getMethods(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($g['id']); ?>" ${ $methods.indexOf(<?php echo e($g['id']); ?>) > -1 ? 'selected' : '' }><?php echo e($g['name']); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Equipment</label>
							<select class="form-control" name="equipment_id[]" multiple placeholder="Select Equipment...">
								<option></option>
								<?php $__currentLoopData = getEquipment(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($g['id']); ?>" ${ $equipments.indexOf(<?php echo e($g['id']); ?>) > -1 ? 'selected' : '' }><?php echo e($g['name']); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label"><input type="checkbox" name="non_detectable" value="1" ${ $analyte.non_detectable == 1 ? 'checked' : '' } />  Not Detectable</label>
						</div>
						<div class="form-group">
							<label class="control-label"><input type="checkbox" name="non_accredited" value="1" ${ $analyte.non_accredited == 1 ? 'checked' : '' } /> Accredited</label>
						</div>
						<div class="form-group">
							<label class="control-label"><input type="checkbox" name="show_on_report" value="1" ${ $analyte.show_on_report == 1 ? 'checked' : '' } /> Show on Report</label>
						</div>
						<div class="form-group">
							<label class="control-label"><input type="checkbox" name="active" value="1" ${ $analyte.active == 1 ? 'checked' : '' } /> Active</label>
						</div>
					</div>
				`);

				return $fB.clone();
			}

			$('#edit-analyte').on('show.bs.modal', function (e) {
				var $analyte = $(e.relatedTarget).data('analyte');
				var $methods = $(e.relatedTarget).data('methods');
				var $equipments = $(e.relatedTarget).data('equipments');

				var $fBText = formBody($analyte, $methods, $equipments);

				$fBText.find('select').select2();

				$(this).find('form').prop('action', '/analyte/'+$analyte.id)

				$(this).find('.modal-body').html($fBText);


			})
		})
	</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/kimari/Projects/polucon/resources/views/layouts/lab/analytes/index.blade.php ENDPATH**/ ?>