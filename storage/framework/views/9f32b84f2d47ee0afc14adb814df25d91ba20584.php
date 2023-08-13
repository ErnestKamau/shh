

<?php $__env->startSection('title2'); ?>
  <title>Sample Conditions</title>
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
          'link' => route('sample_condition_index'),
          'name' => 'Sample Conditions',
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
      <i class="mdi mdi-cogs"></i> Sample Conditions
     
    </h2>
   <div class="card table-responsive">
    <div class="card-body">
        <table class="table table-sm table-condensed table-bordered table-hover table-stripped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Is Active</th>
                    <th>Created At</th>

                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $conditions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $condition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#edit-sample-condition" data-record="<?php echo e(json_encode($condition)); ?>"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                    </td>
                    <td><?php echo e($condition->name); ?></td>
                    <td><?php echo $condition->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                    <td><?php echo e($condition->created_at); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
   </div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="add-sample-condition" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo e(route('add-sample-conditions')); ?>" method="post">
        <?php echo csrf_field(); ?>  
        <div class="modal-body">
            <div class="alert alert-primary p-2">
                <i class="mdi mdi-plus" style="font-size:30px"></i>
                <span class="p-2">Add Sample Condition by providing the information below</span>
            </div>
            <div class="form-group">
                <label for="" class="control-label">Name</label>
                <input type="text"  name="name" class="form-control">
            </div>
            <div class="form-group">
                <label for="" class="control-label">
                    <input type="checkbox" name="active" value="1" checked id=""> Is Active ?
                </label>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="edit-sample-condition" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="<?php echo e(route('edit-sample-condition')); ?>" method="post">
          <?php echo csrf_field(); ?>  
          <div class="modal-body">
              
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
            <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    $(()=>{
      var editSampleConditionBody = (data)=>{
        var body = $(`
            <div class="alert alert-primary p-2">
                <i class="mdi mdi-pencil" style="font-size:30px"></i>
                <span class="p-2">Edit ${data.name} Sample Condition by updating the information below</span>
            </div>
            <div class="form-group">
                <label for="" class="control-label">Name</label>
                <input type="text" value="${data.name}"  name="name" class="form-control">
            </div>
            <div class="form-group">
                <label for="" class="control-label">
                    <input type="checkbox" value="1" name="active" ${data.active == 1 ? 'checked' : ''} id=""> Is Active ?
                </label>
            </div>
            <input type="hidden" name="condition_id" value="${data.id}">
        `).clone();
        return body;
      }
      $('#edit-sample-condition').on('show.bs.modal',(e)=>{
        var data =  $(e.relatedTarget).data('record');
        var body = editSampleConditionBody(data);
        $('#edit-sample-condition').find('.modal-body').empty();
        $('#edit-sample-condition').find('.modal-body').append(body);
      });
      
    });
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/lab/sample-condition/index.blade.php ENDPATH**/ ?>