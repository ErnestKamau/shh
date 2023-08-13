<?php $__env->startSection('title2'); ?>
  <title>Sample Types</title>
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
          'link' => route('sample-analysis-stages'),
          'name' => 'Sample Tracking Stages',
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
      <i class="mdi mdi-sitemap"></i> Lab Section
     
    </h2>
    <div class="card tab-card">
      <div class="card-header tab-card-header">
        <ul class="nav nav-tabs card-header-tabs" id="asset-tabs" role="tablist">
          <li class="nav-item">
            <a href="#lab-sections-tab" class="nav-link active" id="lab-sec-tab" data-toggle="tab" role="tab" aria-controls="lab-sec-tab" aria-selected="true"> <i class="mdi mdi-sitemap" style=" color: black; font-size:15px"></i> Lab Sections</a>
          </li>
          <li class="nav-item">
            <a href="#lab-sections-tab" class="nav-link" id="lab-sec-tab" data-toggle="tab" role="tab" aria-controls="lab-sec-tab" aria-selected="true"> <i class="mdi mdi-account-check" style=" color: black; font-size:15px"></i> Approver Configuration</a>
          </li>
          
        </ul>
      </div>
      <div class="tab-content" id="sample-type-tabs-content">
        <!-- all sample types  -->
        <div class="tab-pane fade show active p-3" id="sample-type-tab" role="tabpanel" aria-labelledby="one-tab">
          <h5 class="card-title">Lab Sections
            <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-sample-analysis-stage"><i class="mdi mdi-plus"></i> Add</button>
          </h5>
          <div class="table-responsive bg-light mt-3 p-4">
            <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
              <thead class="bg-light p-2">
                <tr>
                  <th>No</th>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Workflow</th>
                  <th>Level</th>
                  <th>Section Head</th>
                  <th>Lab</th>
                  <th>Active?</th>
      
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if(count($sampleAnalysisStage) > 0): ?>
                  <?php $__currentLoopData = $sampleAnalysisStage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                      <td valign="center"><?php echo e($loop->iteration); ?></td>
                      <td><?php echo e($stage->name); ?></td>
                      <td><?php echo e($stage->code); ?></td>
                      <td><?php echo e($stage->sample_workflow); ?></td>
                      <td><?php echo e($stage->level); ?></td>
                      <td><?php echo e($stage->getSectionHead()->name ?? '-'); ?></td>
                      <td><?php echo e($stage->getLabDetails()->name ?? '-'); ?></td>
                      <td class="text-small"><?php echo $stage->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                      <td nowrap>
                        <button class="btn btn-primary btn-sm" data-target="#edit-stage-<?php echo e($loop->iteration); ?>" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                        <div id="edit-stage-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
                          <div class="modal-dialog">
                            <!-- Modal content-->
                            <form class="modal-content" method="POST" action="<?php echo e(route('update-sample_analysis_stage', ['id'=>$stage->id])); ?>" enctype="multipart/form-data">
                              <?php echo csrf_field(); ?>
                              <div class="modal-header">
                                <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Sample Analysis Stage</h4>
                              </div>
                              <div class="modal-body">
                                <div class="form-group">
                                  <label class="control-label">Name</label>
                                  <input type="text" class="form-control" name="name" value="<?php echo e($stage->name); ?>" placeholder="Sample Type Name..." required />
                                </div>
                                <div class="form-group">
                                  <label class="control-label">Code</label>
                                  <input type="text" class="form-control" name="code" value="<?php echo e($stage->code); ?>" placeholder="Lab Section Name..." required />
                                </div>
                                <div class="form-group">
                                  <label for="" class="control-label">Section Head</label>
                                  <select name="section_head_id" id="" class="form-control">
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>" <?php echo e($user->id == $stage->section_head_id ? 'selected' : ''); ?>><?php echo e($user->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                  </select>
      
                                </div>
                                <div class="form-group">
                                  <label for="" class="control-label">Labs</label>
                                  <select name="lab_id" id="" class="form-control">
                                    <option value="">Choose Lab...</option>
                                    <?php $__currentLoopData = $labs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($lab->id); ?>" <?php echo e($lab->id == $stage->lab_id ? 'selected' : ''); ?>><?php echo e($lab->code); ?> - <?php echo e($lab->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                  </select>
                                </div>
                                <div class="row">
                                  <div class="col-sm-8">
                                    <label class="control-label">Sample Worflow</label>
                                    <select class="form-control" name="sample_workflow" placeholder="Select Sample Workflow..." required>
                                      <option></option>
                                      <?php $__currentLoopData = getSampleWorflowStages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($g); ?>" <?php echo e($stage->sample_workflow == $g ? 'selected' : ''); ?>><?php echo e($g); ?></option>
                                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                  </div>
                                  <div class="col-sm-4">
                                    <label class="control-label">Stage Level</label>
                                    <input type="number" min="1" class="form-control" name="level" value="<?php echo e($stage->level); ?>" placeholder="Stage Level..." required />
                                  </div>
                                  
                                </div>
                                
                                <div class="form-group mt-2">
                                  <label class="control-label"><input type="checkbox" name="active" value="1" <?php echo e($stage->active == 1 ? 'checked' : ''); ?> /> Active</label>
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
                <?php endif; ?>
              </tbody>
            </table>
            <?php if(count($sampleAnalysisStage) == 0): ?>
              <div class="alert alert-info">
                <i class="mdi mdi-alert"></i> No Sample Analysis Stage added yet.
              </div>
            <?php endif; ?>
          </div>
        </div>
        <!-- end all sample types  -->
  
        <!-- standard tab  -->
        <div class="tab-pane fade p-3" id="lab-sections-tab" role="tabpanel" aria-labelledby="one-tab">
          <h5 class="card-title">Approver Configurations
            <button class="btn btn-outline-primary btn-sm float-right" data-action="add" data-toggle="modal" data-target="#add-approver"><i class="mdi mdi-plus"></i> Add</button>
          </h5>
          <div class="table-responsive p-2">
            <table class="table table-sm table-condensed table-bordered table-stripped">
              <thead>
                <tr>
                  <th></th>
                  <th>Name</th>
                  <th>Title</th>
                  <th>Sections</th>
                </tr>
              </thead>
              <tbody>
                 <?php $__currentLoopData = $approvers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>       
                 <tr>
                    <td>
                      <span class="btn btn-default btn-sm text-primary" data-record="<?php echo e(json_encode($approver)); ?>" data-action="edit" data-target="#add-approver" data-toggle="modal"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                      <span class="btn btn-default btn-sm text-danger" data-record="<?php echo e(json_encode($approver)); ?>" data-target="#delete-approver" data-toggle="modal"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></span>

                    </td>
                    <td><?php echo e($approver->approvername); ?></td>
                    <td><?php echo e($approver->title); ?></td>
                    <td><?php echo e($approver->sectionname); ?></td>
                 </tr>
                 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </tbody>
            </table>
          </div>
         
        </div>
      </div>
    </div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="add-approver" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo e(route('addSectionApproval')); ?>" method="post">
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
<div class="modal fade" id="delete-approver" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo e(route('deleteSectionApproval')); ?>" method="post">
        <?php echo csrf_field(); ?>  
        <div class="modal-body">
         
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-outline-danger btn-sm"><i class="mdi mdi-outline-danger"></i> Delete</button>
          <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
        </div>
      </form>
    </div>
  </div>
</div>
  <div id="add-sample-analysis-stage" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-sample-analysis-stage')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Sample Analysis Stage</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" placeholder="Sample Analysis Stage Name..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Code</label>
            <input type="text" class="form-control" name="code" value="" placeholder="Lab Section Code..." required />
          </div>
          <div class="form-group">
            <label for="" class="control-label">Section Head</label>
            <select name="section_head_id" id="" class="form-control">
              <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

          </div>
          <div class="form-group">
            <label for="" class="control-label">Labs</label>
            <select name="lab_id" id="" class="form-control">
              <option value="">Choose Lab...</option>
              <?php $__currentLoopData = $labs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($lab->id); ?>"><?php echo e($lab->code); ?> - <?php echo e($lab->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
					<div class="row">
						<div class="col-sm-8">
							<label class="control-label">Sample Worflow</label>
							<select class="form-control" name="sample_workflow" placeholder="Select Sample Workflow..." required>
								<option></option>
								<?php $__currentLoopData = getSampleWorflowStages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($g); ?>"><?php echo e($g); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="col-sm-4">
							<label class="control-label">Stage Level</label>
							<input type="number" min="1" class="form-control" name="level" placeholder="Stage Level..." required />
						</div>
					</div>
					<div class="form-group mt-2">
          <div class="form-group">
            <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
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
    $(()=>{
      let deleteAppoverBody = (data)=>{
        var body = $(`
          <div class="alert alert-danger p-2 d-flex">
            <i class="mdi mdi-delete-empty" style="font-size: 25px"></i>
            <span class="p-2">Confirm you want to delete ${data.approvername} Lab Section Approver</span>
          </div>
          <input type="hidden" name="approver_id" value="${data.id}">
        `).clone();
        return body;
      }
      let addApproverBody = (data=false)=>{
        if(data){
          var body = $(`
          <div class="alert alert-primary p-2 d-flex">
            <i class="mdi mdi-plus" style="font-size:25px"></i>
            <div class="p-2">
              Edit ${data.approvername} Lab Sections approvers by providing the information below:
            </div>
          </div>
          <div class="form-group">
            <label for="" class="control-label">Title</label>
            <input type="text" value="${data.title}" name="title" placeholder="Title..." class="form-control">
          </div>
          <input type="hidden" name="approver_id" value="${data.id}">
          <div class="form-group">
            <label for="" class="control-label">Approver</label>
            <select name="user_id" id="user_id_field" class="form-control">
              <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="form-group">
            <label for="" class="control-label">Sections</label>
            <select name="section_ids[]" multiple id="section_ids" class="form-control">
              <?php $__currentLoopData = $sampleAnalysisStage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($stage->id); ?>"><?php echo e($stage->code); ?> - <?php echo e($stage->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          `).clone();
          $(body).find('#user_id_field').val(data.user_id);
          $(body).find('#section_ids').val(data.sectionarr);
         

        }else{
          var body = $(`
          <div class="alert alert-primary p-2 d-flex">
            <i class="mdi mdi-plus" style="font-size:25px"></i>
            <div class="p-2">
              Add Lab Sections approvers by providing the information below:
            </div>
          </div>
          <div class="form-group">
            <label for="" class="control-label">Title</label>
            <input type="text" name="title" placeholder="Title..." class="form-control">
          </div>
          <input type="hidden" name="approver_id" value="0">
          <div class="form-group">
            <label for="" class="control-label">Approver</label>
            <select name="user_id" id="user_id_field" class="form-control">
              <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="form-group">
            <label for="" class="control-label">Sections</label>
            <select name="section_ids[]" multiple id="section_ids" class="form-control">
              <?php $__currentLoopData = $sampleAnalysisStage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($stage->id); ?>"><?php echo e($stage->code); ?> - <?php echo e($stage->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          `).clone();
        }
        $(body).find('#section_ids').select2();
          $(body).find('#user_id_field').select2();
        return body;
      }
      $('#add-approver').on('show.bs.modal',(e)=>{
        var mode = $(e.relatedTarget).data('action');
        var data = mode == 'add' ? [] : $(e.relatedTarget).data('record');
        var body = mode == 'add' ? addApproverBody() : addApproverBody(data);
        $('#add-approver').find('.modal-body').empty();
        $('#add-approver').find('.modal-body').append(body);
      });
      $('#delete-approver').on('show.bs.modal',(e)=>{
        var data = $(e.relatedTarget).data('record')
        var body = deleteAppoverBody(data);
        $('#delete-approver').find('.modal-body').empty();
        $('#delete-approver').find('.modal-body').append(body);

      })
    });
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/sample-analysis-stages/index.blade.php ENDPATH**/ ?>