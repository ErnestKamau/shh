<?php $__env->startSection('title2'); ?>
<style>
    .card {
        box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;
        text-decoration: none !important;
        color: black !important;
    }

    .card:hover {
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }

    a {
        text-decoration: none !important;
        /* color: black !important; */
    }

    .header-area{
        text-decoration: underline;
    }

    .text-bold {
        font-weight: 550;
    }
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
    <?php
    $items = array(
        array(
            'link' => '',
            'name' => 'PRP',
            'icon' => null,
        ),
        array(
            'link' => '',
            'name' => 'Species',
            'icon' => null,
        )
    )
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
    <h5 class="p-2">
        <i class="mdi mdi-test-tube"></i> Species
        <span class="float-right ml-3">
            <form action="<?php echo e(route('getVarietyByZvam')); ?>" method="post">
               <?php echo csrf_field(); ?>  
               <div class="form-group float-right">
                   <input type="text" name="variety_id" placeholder="Variety Number" class="form-control">
               </div>
               <button type="submit" class="btn btn-sm btn-info float-right"><i class="mdi mdi-account-search"></i></button>
           </form>

        </span>
        <span class="btn btn-sm btn-default text-primary btn-default-h float-right" data-toggle="modal" data-target="#add-species" data-action="add"> <i class="mdi mdi-plus"></i> Add Species</span>
        <span class="btn btn-sm btn-default btn-default-h text-success float-right" data-toggle="modal" data-target="#upload-species"><i class="mdi mdi-cloud-upload-outline"></i> Upload Species/varieties</span>
        
</h5>
    
    <div class="card mt-4" style="clear:both">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed table-bordered table-sm table-stripped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Speed Factor</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $species; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $spc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>

                            <td>
                                <span class="btn btn-sm btn-default" data-action="edit" data-species="<?php echo e(json_encode($spc)); ?>" data-toggle="modal" data-target="#add-species"><i class="mdi mdi-pencil text-primary" ></i></span>
                                <a href="<?php echo e(route('species-show',['id'=>$spc->id])); ?>" class="btn btn-sm btn-deafult text-success"><i class="mdi mdi-eye"></i></a>
    
                                <span class="btn-sm btn btn-default text-danger" data-toggle="modal" data-target="#delete-species" data-species="<?php echo e(json_encode($spc)); ?>"><i class="mdi mdi-delete-empty"></i></span>
                               
                            </td>
                            <td><?php echo e($spc->name); ?></td>
                            <td><?php echo e(getPrpNewSystemConfigurationDataByID($spc->speed_factor_id)->name ?? ''); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>

<div class="modal fade" id="add-species" data-speedfactors="<?php echo e(json_encode($speed_factors)); ?>" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('species-add')); ?>" method="post">
                <?php echo csrf_field(); ?> 
                <div class="modal-body">
                   <div class="alert alert-primary p-2 text-center">
                       <i class="mdi mdi-plus"></i> Add new Species
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
<div class="modal fade" id="delete-species" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('species-delete')); ?>" method="POST">
                <?php echo csrf_field(); ?> 
                <div class="modal-body">
                  
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-default text-danger" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="upload-species" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('bulkImportVarietyAndSpecies')); ?>" enctype="multipart/form-data" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-success p-2 d-flex">
                        <i class="mdi mdi-cloud-upload-outline mdi-36px"></i>
                        <span class="p-2 mt-2">Bulk upload species and varieties below !</span>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Choose File <small class="text-danger">(Excel only!)</small></label>
                        <input type="file" name="file" class="form-control" id="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-success"> <i class="mdi mdi-cloud-upload-outline"></i> Upload</button>
                    <span class="btn btn-default text-danger" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(()=>{
        let  addEditSpeciesBody = (data=false)=>{
            if(data){
                var body = $(`
                    <div class="alert alert-primary p-2 text-center">
                       <i class="mdi mdi-pencil"></i> Edit ${data.name} Species
                    </div>
                    <div class="form-group">
                        <label class="control-label">Name</label>
                        <input type="text" name="name" placeholder="Name..." value="${data.name}" class="form-control">
                        <input type="hidden" name="species_id" value="${data.id}">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Speed Factor</label>
                        <select name="speed_factor_id" class="form-control" id="speed_factor">
                            
                        </select>
                    </div>
                    <div class="form-group">
                       <label for="" class="control-label"><input type="checkbox" name="is_active" ${data.is_active == 1 ? 'checked' : ``} value="1" > Active</label>
                   </div>
                `).clone();
            }else{
                var body = $(`
                    <div class="alert alert-primary p-2 text-center">
                       <i class="mdi mdi-plus"></i> Add new Species
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Name</label>
                        <input type="text" name="name" placeholder="Name..." class="form-control">
                        <input type="hidden" name="species_id" value="0">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Speed Factor</label>
                        <select name="speed_factor_id" class="form-control" id="speed_factor">
                            
                        </select>
                    </div>
                    <div class="form-group">
                       <label for="" class="control-label"><input type="checkbox" name="is_active" checked  id=""> Active</label>
                   </div>
                `).clone()

            }
            return body;
        }
        $('#add-species').on('show.bs.modal',(e)=>{
            let action = $(e.relatedTarget).data('action');
            let speed_factor =  $('#add-species').data('speedfactors');
            if(action == 'add'){
                let body = addEditSpeciesBody();
                $.each(speed_factor,(i,obj)=>{
                    console.log(obj);
                    $(body).find('#speed_factor').append(`<option value="${obj.id}">${obj.name}</option>`);
                });
                $('#add-species').find('.modal-body').empty();
                $('#add-species').find('.modal-body').append(body);
            }else{  
                let data  = $(e.relatedTarget).data('species');
                console.log(data)
                let body = addEditSpeciesBody(data)
                $(body).find('#speed_factor').empty();
                $.each(speed_factor,(i,obj)=>{
                    $(body).find('#speed_factor').append(`<option value="${obj.id}" ${obj.id == data.speed_factor_id ? `selected`: ``} >${obj.name}</option>`);
                });
                
                $('#add-species').find('.modal-body').empty();
                $('#add-species').find('.modal-body').append(body);
            }
            $('#add-species').find('#speed_factor').select2();
           

        });
        let deleteSpeciesBody = (data)=>{
            var body = $(`
                <div class="alert alert-primary d-flex p-2">
                    <i class="mdi mdi-alert-decagram mdi-36px"></i> 
                    <span class="mt-3 p-2">Confirm you want to delete ${data.name} species. <br> This action will cascade down to all varieties associated with the above Species</span>
                </div>
                <input type="hidden" name="species_id" value="${data.id}">
            `).clone();
            return body;
        }
        $('#delete-species').on('show.bs.modal',(e)=>{
            let data = $(e.relatedTarget).data('species');
            var body = deleteSpeciesBody(data);
            $('#delete-species').find('.modal-body').empty();
            $('#delete-species').find('.modal-body').append(body);
        });
    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/species/index.blade.php ENDPATH**/ ?>