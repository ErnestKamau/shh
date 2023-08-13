

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
          'link' => route('sample-product-index'),
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
      <i class="mdi mdi-cogs"></i> Sample Products
      <span class="btn float-right btn-outline-primary btn-sm" data-toggle="modal" data-target="#add-sample-product"><i class="mdi mdi-plus"></i> Add Product</span>
     
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
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <tr>
                    <td>
                        <span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#edit-sample-product" data-record="<?php echo e(json_encode($product)); ?>"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                    </td>
                    <td><?php echo e($product->name); ?></td>
                    <td><?php echo e($product->created_at); ?></td>
                    <td class="text-small"><?php echo $product->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                    
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
            </tbody>
        </table>
    </div>
   </div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="add-sample-product" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo e(route('add-customer-product')); ?>" method="post">
        <?php echo csrf_field(); ?>  
        <div class="modal-body">
            <div class="alert alert-primary p-2">
                <i class="mdi mdi-plus" style="font-size:30px"></i>
                <span class="p-2">Add Sample Product by providing the information below</span>
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
<div class="modal fade" id="edit-sample-product" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="<?php echo e(route('edit-customer-product')); ?>" method="post">
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
                    <input type="checkbox" name="active" value="1" ${data.active == 1 ? 'checked' : ''} id=""> Is Active ?
                </label>
            </div>
            <input type="hidden" name="product_id" value="${data.id}">
        `).clone();
        return body;
      }
      $('#edit-sample-product').on('show.bs.modal',(e)=>{
        var data =  $(e.relatedTarget).data('record');
        var body = editSampleConditionBody(data);
        $('#edit-sample-product').find('.modal-body').empty();
        $('#edit-sample-product').find('.modal-body').append(body);
      });
      
    });
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/lab/sample-products/index.blade.php ENDPATH**/ ?>