<?php $__env->startSection('title2'); ?>
  <title>Sample Products</title>
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
          'link' => route('sample-type-category-index'),
          'name' => 'Sample Type Category',
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
      <i class="mdi mdi-cogs"></i> Sample Type Category
      <span class="btn-btn-outline-primary btn-sm" data-toggle="modal" data-target="#add-sample-type-category" data-action="add"><i class="mdi mdi-plus"></i> Add</span>
     
    </h2>
   <div class="card table-responsive">
    <div class="card-body">
        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
            <thead class="bg-light p-2">
                <tr>
                    <th>#</th>
                    <th nowrap>Name</th>
                    <th>Created At</th>
                    <th nowrap>Active</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <tr>
                    <td>
                        <span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#add-sample-type-category" data-record="<?php echo e(json_encode($category)); ?>" data-action="edit"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                    </td>
                    <td><?php echo e($category->sample_type_category); ?></td>
                    <td><?php echo e($category->created_at); ?></td>
                    <td class="text-small"><?php echo $category->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                    
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
            </tbody>
        </table>
    </div>
   </div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="add-sample-type-category" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo e(route('sample-type-category-add')); ?>" method="post">
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
      var editSampleConditionBody = (data=false)=>{
        if(data){
            var body = $(`
                <div class="alert alert-primary p-2">
                    <i class="mdi mdi-pencil" style="font-size:30px"></i>
                    <span class="p-2">Edit ${data.sample_type_category} Sample Type Category by updating the information below</span>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Name</label>
                    <input type="text" value="${data.sample_type_category}"  name="name" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">
                        <input type="checkbox" value="1" name="active" ${data.active == 1 ? 'checked' : ''} id=""> Is Active ?
                    </label>
                </div>
                <input type="hidden" name="category_id" value="${data.id}">
            `).clone();
        }else{
            var body = $(`
                <div class="alert alert-primary p-2">
                    <i class="mdi mdi-plus" style="font-size:30px"></i>
                    <span class="p-2">Add Sample Type Category by giving the information below</span>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Name</label>
                    <input type="text" value=""  name="name" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">
                        <input type="checkbox" value="1" name="active" checked id=""> Is Active ?
                    </label>
                </div>
                <input type="hidden" name="category_id" value="0">
            `).clone();
        }
        return body;
      }
      $('#add-sample-type-category').on('show.bs.modal',(e)=>{
        var mode  = $(e.relatedTarget).data('action');
        var data = mode == 'edit' ?  $(e.relatedTarget).data('record') : false;
        var body = editSampleConditionBody(data);
        $('#add-sample-type-category').find('.modal-body').empty();
        $('#add-sample-type-category').find('.modal-body').append(body);
      });
      
    });
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/sample-type-category/index.blade.php ENDPATH**/ ?>