

<?php $__env->startSection('title2'); ?>
<title> <?php echo e($standard->code); ?> | Standard</title>

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
      'link' => route('sample-types'),
      'name' => 'Sample Types',
      'icon' => null
    ),
    array(
      'link' => '#',
      'name' => 'Standard-' . $standard->code,
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
    <i class="mdi mdi-microscope"></i> <?php echo e($standard->code); ?> <small class="text-muted"> | Standard</small>
    <span class="btn btn-sm btn-outline-primary float-right" data-toggle="modal" data-target="#add-analyte"><i class="mdi mdi-plus"></i> Add</span>
  </h2>
  <div class="table-responsive bg-light p-4">
    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
      <thead class="bg-light p-2">
        <tr>
          <th>No</th>
          <th>Code</th>
          <th nowrap>Analyte</th>
          <th>Standard</th>
          <th>Standard Value</th>
          <th>Standard Value Type</th>
          <th>Low</th>
          <th>High</th>
          <th>Value</th>
          <th>Comment</th>
          <th>Recommendation</th>
          <th></th>`
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $standard_analyte; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analyte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($loop->iteration); ?></td>
          <td><?php echo e($analyte->analyte_code); ?></td>
          <td><?php echo e($analyte->analyte_name); ?></td>
          <td><?php echo e($standard->code); ?></td>
          <td class="text-center"><?php echo e($analyte->standard_value_id == '' ? '-' : $analyte->standard_value_name); ?></td>
          <td><?php echo e($analyte->standard_value_type); ?></td>
          <td class="text-center"><?php echo $analyte->low == '' ? '-':$analyte->low; ?></td>
          <td class="text-center"><?php echo $analyte->high == '' ? '-': $analyte->high; ?></td>
          <td class="text-center"><?php echo $analyte->standard_is_value == '' ? '-' : $analyte->standard_is_value; ?></td>
          <td><?php echo e($analyte->comments); ?></td>
          <td><?php echo e($analyte->recommendations); ?></td>
          <td>
            <span class="btn-sm btn-outline-default" data-target="#edit-guide-<?php echo e($loop->iteration); ?>" data-toggle="modal" data-toggle="tooltip" title="Edit Analyte Standard"><i class="mdi mdi-pencil"></i></span>
            <span class="btn-sm btn-default text-warning" data-toggle="modal" data-target="#clone-guide-<?php echo e($loop->iteration); ?>" data-toggle="tooltip" title="Duplicate/Clone Analyte Standard"><i class="mdi mdi-content-duplicate"></i></span>
            <span class="btn-sm btn-default text-danger" data-toggle="modal" data-target="#delete-guide-<?php echo e($loop->iteration); ?>" data-toggle="tooltip" title="Delete Analyte Standard"><i class="mdi mdi-delete-empty"></i></span>

            <div class="modal fade" id="delete-guide-<?php echo e($loop->iteration); ?>">
              <div class="modal-dialog">
                <form action="<?php echo e(route('delete_analysis_guide')); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                  <?php echo csrf_field(); ?>
                  <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-delete-empty text-danger"></i> Delete <?php echo e($analyte->analyte_name); ?> Standard.</h4>
                  </div>
                  <div class="modal-body" style="background-color: turquoise;">
                    <input type="hidden" name="guide_id" value="<?php echo e($analyte->id); ?>">
                    Confirm you want to delete <?php echo e($analyte->analyte_name); ?> analyite standard ?
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>


            <div class="modal fade" id="clone-guide-<?php echo e($loop->iteration); ?>">
              <div class="modal-dialog">
                <form action="<?php echo e(route('clone_analysis_guide')); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                  <?php echo csrf_field(); ?>
                  <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-content-duplicate text-warning"></i> Clone <?php echo e($analyte->analyte_name); ?> Standard.</h4>
                  </div>
                  <div class="modal-body" style="background-color: turquoise;">
                    <input type="hidden" name="guide_id" value="<?php echo e($analyte->id); ?>">

                    Confirm You Want to Duplicate <?php echo e($analyte->analyte_name); ?> Analyte Standard ?
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>

            <div id="edit-guide-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
              <div class="modal-dialog">
                <!-- Modal content-->
                <form class="modal-content" method="POST" action="<?php echo e(route('add-analyte-guide')); ?>" enctype="multipart/form-data">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="standard_id" value="<?php echo e($standard->id); ?>" />
                  <input type="hidden" name="guide_id" value="<?php echo e($analyte->id); ?>">

                  <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-pencil text-primary"></i> Edit <?php echo e($analyte->analyte_name); ?> Standards</h4>
                  </div>
                  <div class="modal-body">
                    <div class="form-group">
                      <label class="control-label">Analyte</label>
                      <select class="form-control" name="analyte_id" required placeholder="Select Analyte...">
                        <option></option>
                        <?php $__currentLoopData = $analytes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($a->id); ?>" <?php echo e($analyte->analyte_id == $a->id ? 'selected':''); ?> data-step="<?php echo e($a->decimal_places); ?>"><?php echo e($a->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      </select>
                    </div>

                    <div class="form-group">
                      <label class="control-label">Standard</label>
                      <input type="text" readonly value="<?php echo e($standard->code); ?>" class="form-control">
                    </div>

                    <div class="form-group">
                      <div class="row no-gutters">
                        <div class="col-lg-6 col-sm-6">
                          <div class="form-check">

                            <input class="form-check-input" type="radio" id="is-range-<?php echo e($analyte->id); ?>" class="form-control" name="standard_value_type" value="is_range" data-id="<?php echo e($analyte->id); ?>" onclick="inhoused(this)" <?php echo e($analyte->standard_value_type == 'is_range' ? 'checked="checked"' : ''); ?> />
                            <label class="form-check-label" for="is-range">
                              Use range
                            </label>
                          </div>
                        </div>
                        <div class="col-lg-6 col-sm-6">
                          <div class="form-check">

                            <input class="form-check-input" type="radio" id="is-standard-value-<?php echo e($analyte->id); ?>" class="form-control" name="standard_value_type" data-id="<?php echo e($analyte->id); ?>" value="is_standard_value" onclick="externaled(this)" <?php echo e($analyte->standard_value_type == 'is_standard_value' ? 'checked="checked"' :''); ?> />
                            <label class="form-check-label" for="is-standard-value">
                              Use Value
                            </label>



                          </div>
                        </div>
                      </div>
                    </div>
                    <?php if($analyte->standard_value_type == 'is_range'): ?>
                    <div class="form-group" id="range-<?php echo e($analyte->id); ?>">
                      <label class="control-label">Standard Range</label>
                      <div class="row">
                        <div class="col-lg-6 col-sm-6">
                          <label class="control-label">Low <span class="text-danger">*</span></label>
                          <input type="text" name="low_range" id="Low-Range-<?php echo e($analyte->id); ?>" value="<?php echo e($analyte->low); ?>" class="form-control">
                        </div>
                        <div class="col-lg-6 col-sm-6">
                          <label class="control-label">High <span class="text-danger">*</span></label>
                          <input type="text" name="high_range" id="High-Range-<?php echo e($analyte->id); ?>" value="<?php echo e($analyte->high); ?>" class="form-control">
                        </div>
                      </div>
                    </div>
                    <?php else: ?>
                    <div class="form-group" id="range-<?php echo e($analyte->id); ?>" style="display: none;">
                      <label class="control-label">Standard Range</label>
                      <div class="row">
                        <div class="col-lg-6 col-sm-6">
                          <label class="control-label">Low <span class="text-danger">*</span></label>
                          <input type="text" name="low_range" id="Low-Range-<?php echo e($analyte->id); ?>" value="<?php echo e($analyte->low); ?>" class="form-control">
                        </div>
                        <div class="col-lg-6 col-sm-6">
                          <label class="control-label">High <span class="text-danger">*</span></label>
                          <input type="text" name="high_range" id="High-Range-<?php echo e($analyte->id); ?>" value="<?php echo e($analyte->high); ?>" class="form-control">
                        </div>
                      </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group" id="standard-values-<?php echo e($analyte->id); ?>">
                      <label class="control-label">Standard Values <span class="text-danger">*</span></label>
                      <select name="standard_value" class="form-control select-standard-value" id="standard-selected-<?php echo e($analyte->id); ?>" data-analyte='<?php echo e(json_encode($analyte)); ?>' placeholder="Employee...">
                        <option value="">Select Standard Value</option>
                        <?php $__currentLoopData = $standard_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value->code); ?>" <?php echo e($analyte->standard_value_id == $value->id ? 'selected' : ''); ?>><?php echo e($value->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      </select>
                    </div>


                    <div class="form-group" id="is-value-<?php echo e($analyte->id); ?>">
                      <label class="control-label">Value <span class="text-danger">*</span></label>
                      <input type="text" name="is_value" value="<?php echo e($analyte->standard_is_value); ?>" id="Is-Value-<?php echo e($analyte->id); ?>" placeholder="Enter Value..." class="form-control">
                    </div>

                    <div class="form-group">
                      <label class="control-label">Comments</label>
                      <textarea name="comments" class="form-control" placeholder="Guide Comments..."><?php echo e($analyte->comments); ?></textarea>
                    </div>
                    <div class="form-group">
                      <label class="control-label">Recommendations</label>
                      <textarea name="recommendations" class="form-control" placeholder="Guide Recommendations..."><?php echo e($analyte->recommendations); ?></textarea>
                    </div>

                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="mdi mdi-content-save"></i> Save</button>
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
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






</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div id="add-analyte" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <form class="modal-content" method="POST" action="<?php echo e(route('add-analyte-guide')); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="standard_id" value="<?php echo e($standard->id); ?>" />
      <input type="hidden" name="guide_id" value="0">

      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Analyte</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Analyte</label>
          <select class="form-control" name="analyte_id" required placeholder="Select Analyte...">
            <option></option>
            <?php $__currentLoopData = $analytes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($a->id); ?>" data-step="<?php echo e($a->decimal_places); ?>"><?php echo e($a->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>


        <div class="form-group">
          <label class="control-label">Standard</label>
          <input type="text" readonly value="<?php echo e($standard->code); ?>" class="form-control">
        </div>

        <div class="form-group">
          <div class="row no-gutters">
            <div class="col-lg-6 col-sm-6">
              <div class="form-check">
                <input class="form-check-input" type="radio" id="is-range" class="form-control" name="standard_value_type" value="is_range" onclick="inhouse()" />
                <label class="form-check-label" for="is-range">
                  Use range
                </label>
              </div>
            </div>
            <div class="col-lg-6 col-sm-6">
              <div class="form-check">
                <input class="form-check-input" type="radio" id="is-standard-value" class="form-control" name="standard_value_type" value="is_standard_value" onclick="external()" />
                <label class="form-check-label" for="is-standard-value">
                  Use Value
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group" id="range" style="display:none">
          <label class="control-label">Standard Range</label>
          <div class="row">
            <div class="col-lg-6 col-sm-6">
              <label class="control-label">Low <span class="text-danger">*</span></label>
              <input type="text" name="low_range" id="Low-Range" value="" class="form-control">
            </div>
            <div class="col-lg-6 col-sm-6">
              <label class="control-label">High <span class="text-danger">*</span></label>
              <input type="text" name="high_range" id="High-Range" value="" class="form-control">
            </div>
          </div>
        </div>
        <div class="form-group" id="standard-values" style="display:none">
          <label class="control-label">Standard Values <span class="text-danger">*</span></label>
          <select name="standard_value" class="form-control" id="Standard-Value-primary" placeholder="Employee...">
            <?php $__currentLoopData = $standard_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($value->code); ?>"><?php echo e($value->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group hidden" id="is-value-primary">
          <label class="control-label">Value <span class="text-danger">*</span></label>
          <input type="text" name="is_value" id="Is-Value" placeholder="Enter Value..." class="form-control">
        </div>
        <div class="form-group">
          <label class="control-label">Comments</label>
          <textarea name="comments" class="form-control" placeholder="Guide Comments..."></textarea>
        </div>
        <div class="form-group">
          <label class="control-label">Recommendations</label>
          <textarea name="recommendations" class="form-control" placeholder="Guide Recommendations..."></textarea>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" class="form-control" name="main_standard" value="1" />
          <label class="form-check-label">
            Main Standard
          </label>
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
  console.log('test2')
   function inhouse() {
    var checkbox = document.getElementById("is-range");
    var text = document.getElementById("range");
    var text2 = document.getElementById('is-value');
    if (checkbox.checked == true) {
      text.style.display = "block";
      text2.style.display = "none";
      $('#Low-Range').attr('required', true);
      $('#High-Range').attr('required', true);
      $('#Standard-Value').removeAttr('required', false);

      external();
    } else {
      text.style.display = "none";
      text2.style.display = "none";
      $('#Low-Range').removeAttr('required', false);
      $('#High-Range').removeAttr('required', false);
    }

  }

  function external() {
    var checkbox = document.getElementById("is-standard-value");
    var text = document.getElementById("standard-values");
    if (checkbox.checked == true) {

      text.style.display = "block";
      $('#Standard-Value').attr('required', true);

      inhouse();
    } else {
      text.style.display = "none";
      $('#Standard-Value').removeAttr('required', false);
      $('#Is-Value').removeAttr('required', false);
    }
  }

  function checkvalue(item) {
    var checkexist = item.value;
    var text = document.getElementById('is-value');
    var checkbox = document.getElementById("is-standard-value");

    if ((checkexist === 'IsValue') && (checkbox.checked === true)) {
      $('#Is-Value').attr('required', true);

      text.style.display = "block";
    } else {
      text.style.display = "none";
      $('#Is-Value').removeAttr('required', false);
    }
  }

  function checkvalued(item) {
    console.log('test');
    var index = item.getAttribute('data-id');
    var count = item.getAttribute('data-count');
    var i = count - 1;
    var is_value = 'is-value-' + index;
    var standard = 'standard_value-' + index;
    var is_standard = 'is-standard-value-' + index;
    var checkexist = item.value;

    console.log(item.value);
    var text = document.getElementById(is_value);
    var checkbox = document.getElementById(is_standard);
    if (checkexist == 'IsValue' && checkbox.checked == true) {

      $('#Is-Value').attr('required', true);

      text.style.display = "block";
    } else {
      text.style.display = "none";
      $('#Is-Value').removeAttr('required', false);
    }
  }

  function inhoused(item) {

    var index = item.getAttribute('data-id');
    var is_range = 'is-range-' + index;
    var range = 'range-' + index;
    var is_value = 'is-value-' + index;
    var low = '#Low-Range-' + index;
    var high = '#High-Range' + index;
    var standard = '#Standard-Value-' + index;

    var checkbox = document.getElementById(is_range);
    var text = document.getElementById(range);
    var text2 = document.getElementById(is_value);
    if (checkbox.checked == true) {
      text.style.display = "block";
      text2.style.display = "none";
      $(low).attr('required', true);
      $(high).attr('required', true);
      $(standard).removeAttr('required', false);

      externaled(item);
    } else {
      text.style.display = "none";
      text2.style.display = "block";
      $(low).removeAttr('required', false);
      $(high).removeAttr('required', false);
    }
    

  }

  function externaled(item) {

    var index = item.getAttribute('data-id');
    var is_standard = 'is-standard-value-' + index;
    var standard_value = 'standard-values-' + index;
    var is_value = '#is-value-'+index;

    var standard = '#Standard-Value-' + index;

    var checkbox = document.getElementById(is_standard);
    var text = document.getElementById(standard_value);

    if (checkbox.checked == true) {
      text.style.display = "block";
      $('#Standard-Value').attr('required', true);
      var value_id = '#standard-selected-'+index;
      $(value_id).trigger('change');
      

      inhoused(item);
    } else {
      text.style.display = "none";
      $(standard).removeAttr('required', false);
      $(is_value).removeAttr('required', false);
    }
   
   
  }
   
  $(function() {
    console.log('test')
    $('.select-standard-value').on('change', function() {
      console.log('test1');
      var analyte_id = $(this).data('analyte');
      var selected = $(this).val();
      
      var is_value = '#is-value-'+analyte_id.id;
      var t = 'is-value-'+analyte_id.id;
      if(selected === 'IsValue'){
        $(is_value).removeClass('hidden');
       
      }else{
        
        $(is_value).addClass('hidden');
      }
      

    });
    $('#Standard-Value-primary').on('change',function(){
      var selectedValue = $(this).val();
      if(selectedValue == 'IsValue'){
        console.log('tete')
        $('#is-value-primary').removeClass('hidden');
      }else{
        console.log('tete...')
        $('#is-value-primary').addClass('hidden');
      }
      
    })
  });
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/lab/sample-types/show_standard.blade.php ENDPATH**/ ?>