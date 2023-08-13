

<?php $__env->startSection('title2'); ?>
<title> <?php echo e($analysis_type->name); ?> | Analysis Types</title>

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
      'link' => '/sample-types',
      'name' => 'Sample Types',
      'icon' => null
    ),

    array(
      'link' => '/sample-type/' . $analysis_type->sample_type_id,
      'name' => getSampleTypeByID($analysis_type->sample_type_id)->name,
      'icon' => null
    ),
    array(
      'link' => '#',
      'name' => $analysis_type->name,
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
    <i class="mdi mdi-microscope"></i> <?php echo e($analysis_type->name); ?> <small class="text-muted"> | Analysis Types</small>
  </h2>
  <div class="row no-gutters">
    <div class="col-sm-4 p-2">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title"><i class="mdi mdi-pencil-outline"></i> Edit Analysis Type</h5>
          <form method="POST" action="<?php echo e(route('edit-analysis-type', ['id'=>$analysis_type->id])); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="form-group">
              <label class="control-label">Name</label>
              <input type="text" class="form-control" name="name" value="<?php echo e($analysis_type->name); ?>" placeholder="Analysis Type Name..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Code</label>
              <input type="text" class="form-control" name="code" value="<?php echo e($analysis_type->code); ?>" placeholder="Analysis Type Code..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Description</label>
              <textarea class="form-control" name="description" placeholder="Description..." required><?php echo e($analysis_type->description); ?></textarea>
            </div>
            <div class="form-group">
              <label class="control-label">Sample Type</label>
              <select class="form-control" name="sample_type_id" data-placeholder>
                <?php $__currentLoopData = $sample_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($sam->id); ?>" <?php echo e($sam->id == $analysis_type->sample_type_id ? 'selected' : ''); ?>><?php echo e($sam->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="form-group">
              <label class="control-label">Lab</label>
              <select class="form-control" name="lab_id" data-placeholder>
                <?php $__currentLoopData = $labs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->id); ?>" <?php echo e($c->id == $analysis_type->lab_id ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="form-group">
              <label for="" class="control-label">Lab Section</label>
              <select name="lab_section_id" id="" class="form-control">
                <?php $sample_type = getSampleTypeByID($analysis_type->sample_type_id); ?>
                <?php if($sample_type->sample_analysis_stage): ?>
                  <?php $__currentLoopData = $sample_type->sample_analysis_stage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($stage->active == 1): ?>
                      <option value="<?php echo e($stage->sample_analysis_stage_id); ?>" <?php echo e($stage->sample_analysis_stage_id == $analysis_type->lab_section_id ? 'selected' : ''); ?>><?php echo e($stage->sample_analysis_stage->name); ?></option>
                    <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="control-label">Reporting Time <small class="text-muted">(in days)</small></label>
              <input type="number" min="0" class="form-control" name="reporting_time" value="<?php echo e($analysis_type->reporting_time); ?>" placeholder="Analysis Type Reporting Time..." required />
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" name="active" value="1" <?php echo e($analysis_type->active == 1 ? 'checked' : ''); ?> /> Active</label>
            </div>
            <div class="p-0">
              <button type="submit" class="btn btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-sm-8 p-2">
      <div class="card tab-card">
        <div class="card-header tab-card-header">
          <ul class="nav nav-tabs card-header-tabs" id="analyte-tabs" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="parameters-tab" data-toggle="tab" href="#parameters" role="tab" aria-controls="Parameters" aria-selected="true">Analytes</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="guides-tab" data-toggle="tab" href="#guides" role="tab" aria-controls="Notes" aria-selected="false">Guides</a>
            </li>
          </ul>
        </div>

        <div class="tab-content" id="analyte-tabs-content">
          <div class="tab-pane fade p-3" id="guides" role="tabpanel" aria-labelledby="one-tab">
            <h5 class="card-title">Guides <div class="btn btn-sm btn-info float-right" data-target="#add-analyte-guide" data-toggle="modal"><i class="mdi mdi-plus"></i> Add</div>
            </h5>
            <div class="table-responsive">
              <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                <thead class="bg-light p-2">
                  <tr>
                    <th>No</th>
                    <th nowrap>Analyte</th>

                    <th>Standard</th>
                    <th>Value Type</th>
                    <th>Low</th>
                    <th>High</th>
                    <th>Default</th>
                    <th nowrap>Value</th>
                    <th nowrap>Comments</th>
                    <th nowrap>Recommendations</th>
                    <th></th>
                    <th></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php $__currentLoopData = $analysis_type->guides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php
                  $analysis_standard = getStandardByid($item->standard_id);
                  $analysis_value = getStandardValuebyID($item->standard_value_id);
                  ?>
                  <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($item->analyte->name." - ".$item->analyte->code); ?></td>
                    <td><?php echo e($analysis_standard->name ?? ''); ?></td>
                    <td><?php echo e($item->standard_value_type ?? ''); ?></td>
                    <td><?php echo e($item->low != '' ? $item->low : '-'); ?></td>
                    <td><?php echo e($item->high != '' ? $item->high : '-'); ?></td>
                    <td><?php echo e(isset($analysis_value->id) ? $analysis_value->name : '-'); ?></td>
                    <td><?php echo e($item->standard_is_value != '' ? $item->standard_is_value:'-'); ?></td>


                    <td><?php echo e($item->comments); ?></td>
                    <td><?php echo e($item->recommendations); ?></td>
                    <td>
                      <span class="btn-sm btn-outline-default" data-target="#edit-guide-<?php echo e($loop->iteration); ?>" data-toggle="modal"><i class="mdi mdi-pencil"></i></span>

                      <div id="edit-guide-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                          <!-- Modal content-->
                          <form class="modal-content" method="POST" action="<?php echo e(route('add-analyte-guide')); ?>" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="analysis_type_id" value="<?php echo e($analysis_type->id); ?>" />
                            <input type="hidden" name="guide_id" value="<?php echo e($item->id); ?>" />
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="mdi mdi-pencil text-primary"></i> Edit <?php echo e($item->analyte->name); ?> Guide</h4>
                            </div>
                            <div class="modal-body">
                              <div class="form-group">
                                <label class="control-label">Analyte</label>
                                <select class="form-control" name="analyte_id" required placeholder="Select Analyte...">
                                  <option></option>
                                  <?php $__currentLoopData = $analysis_type->analysis_elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                  <option value="<?php echo e($a->analyte->id); ?>" <?php echo e($item->analyte->id == $a->analyte->id ? 'selected':''); ?> data-step="<?php echo e($a->analyte->decimal_places); ?>"><?php echo e($a->analyte->name); ?></option>
                                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                              </div>

                              <?php
                              $standards = getStandards();
                              $standard_values = getStandardValues();
                              ?>
                              <div class="form-group">
                                <label class="control-label">Standard</label>
                                <select class="form-control" name="standard" required>
                                  <?php $__currentLoopData = $standards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $standard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                  <?php if($standard->status == 1): ?>
                                  <option value="<?php echo e($standard->id); ?>" <?php echo e($standard->id == $item->standard_id ? 'selected':''); ?>><?php echo e($standard->code); ?></option>
                                  <?php endif; ?>
                                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                              </div>

                              <div class="form-group">
                                <div class="row no-gutters">
                                  <div class="col-lg-6 col-sm-6">
                                    <div class="form-check">

                                      <input class="form-check-input" type="radio" id="is-range-<?php echo e($item->id); ?>" class="form-control" name="standard_value_type" value="is_range" data-id="<?php echo e($item->id); ?>" onclick="inhoused(this)" <?php echo e($item->standard_value_type == 'is_range' ? 'checked="checked"' : ''); ?> />
                                      <label class="form-check-label" for="is-range">
                                        Use range
                                      </label>
                                    </div>
                                  </div>
                                  <div class="col-lg-6 col-sm-6">
                                    <div class="form-check">

                                      <input class="form-check-input" type="radio" id="is-standard-value-<?php echo e($item->id); ?>" class="form-control" name="standard_value_type" data-id="<?php echo e($item->id); ?>" value="is_standard_value" onclick="externaled(this)" <?php echo e($item->standard_value_type == 'is_standard_value' ? 'checked="checked"' :''); ?> />
                                      <label class="form-check-label" for="is-standard-value">
                                        Use Value
                                      </label>



                                    </div>
                                  </div>
                                </div>
                              </div>
                              <?php if($item->standard_value_type == 'is_range'): ?>
                              <div class="form-group" id="range-<?php echo e($item->id); ?>">
                                <label class="control-label">Standard Range</label>
                                <div class="row">
                                  <div class="col-lg-6 col-sm-6">
                                    <label class="control-label">Low <span class="text-danger">*</span></label>
                                    <input type="text" name="low_range" id="Low-Range-<?php echo e($item->id); ?>" value="<?php echo e($item->low); ?>" class="form-control">
                                  </div>
                                  <div class="col-lg-6 col-sm-6">
                                    <label class="control-label">High <span class="text-danger">*</span></label>
                                    <input type="text" name="high_range" id="High-Range-<?php echo e($item->id); ?>" value="<?php echo e($item->high); ?>" class="form-control">
                                  </div>
                                </div>
                              </div>
                              <?php else: ?>
                              <div class="form-group" id="range-<?php echo e($item->id); ?>" style="display: none;">
                                <label class="control-label">Standard Range</label>
                                <div class="row">
                                  <div class="col-lg-6 col-sm-6">
                                    <label class="control-label">Low <span class="text-danger">*</span></label>
                                    <input type="text" name="low_range" id="Low-Range-<?php echo e($item->id); ?>" value="<?php echo e($item->low); ?>" class="form-control">
                                  </div>
                                  <div class="col-lg-6 col-sm-6">
                                    <label class="control-label">High <span class="text-danger">*</span></label>
                                    <input type="text" name="high_range" id="High-Range-<?php echo e($item->id); ?>" value="<?php echo e($item->high); ?>" class="form-control">
                                  </div>
                                </div>
                              </div>
                              <?php endif; ?>
                              <?php if($item->standard_value_type == 'is_standard_value'): ?>
                              <div class="form-group" id="standard-values-<?php echo e($item->id); ?>">
                                <label class="control-label">Standard Values <span class="text-danger">*</span></label>
                                <select name="standard_value" class="form-control" data-id="<?php echo e($item->id); ?>" data-count="<?php echo e($loop->iteration); ?> id=" Standard-Value-<?php echo e($item->id); ?>" onclick="checkvalued(this)" placeholder="Employee...">
                                  <?php $__currentLoopData = $standard_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                  <option value="<?php echo e($value->code); ?>" <?php echo e($item->standard_value_id == $value->id ? 'selected' : ''); ?>><?php echo e($value->name); ?></option>
                                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                              </div>
                              <?php else: ?>
                              <div class="form-group" id="standard-values-<?php echo e($item->id); ?>" style="display: none;">
                                <label class="control-label">Standard Values <span class="text-danger">*</span></label>
                                <select name="standard_value[]" class="form-control" data-id="<?php echo e($item->id); ?>" data-count="<?php echo e($loop->iteration); ?>" onclick="checkvalued(this)" placeholder="Employee...">
                                  <?php $__currentLoopData = $standard_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                  <option value="<?php echo e($value->code); ?>" <?php echo e($item->standard_value_id == $value->id ? 'selected' : ''); ?>><?php echo e($value->name); ?></option>
                                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                              </div>
                              <?php endif; ?>
                              <?php
                              $standard = getStandardValuebyID($item->standard_value_id);
                              ?>
                              <?php if(isset($standard->id) && $standard->code == 'IsValue'): ?>
                              <div class="form-group" id="is-value-<?php echo e($item->id); ?>">
                                <label class="control-label">Value <span class="text-danger">*</span></label>
                                <input type="text" name="is_value" value="<?php echo e($item->standard_is_value); ?>" id="Is-Value-<?php echo e($item->id); ?>" placeholder="Enter Value..." class="form-control">
                              </div>
                              <?php else: ?>
                              <div class="form-group" id="is-value-<?php echo e($item->id); ?>" style="display: none;">
                                <label class="control-label">Value <span class="text-danger">*</span></label>
                                <input type="text" name="is_value" value="<?php echo e($item->standard_is_value); ?>" id="Is-Value-<?php echo e($item->id); ?>" placeholder="Enter Value..." class="form-control">
                              </div>
                              <?php endif; ?>
                              <div class="form-group">
                                <label class="control-label">Comments</label>
                                <textarea name="comments" class="form-control" placeholder="Guide Comments..."><?php echo e($item->comments); ?></textarea>
                              </div>
                              <div class="form-group">
                                <label class="control-label">Recommendations</label>
                                <textarea name="recommendations" class="form-control" placeholder="Guide Recommendations..."><?php echo e($item->recommendations); ?></textarea>
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
                    <td>
                      <span class="btn-sm btn-outline-warning" data-toggle="modal" data-target="#clone-guide-<?php echo e($loop->iteration); ?>"><i class="mdi mdi-content-duplicate"></i></span>


                      <div class="modal fade" id="clone-guide-<?php echo e($loop->iteration); ?>">
                        <div class="modal-dialog">
                          <form action="<?php echo e(route('clone_analysis_guide')); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="mdi mdi-content-duplicate text-warning"></i> Clone <?php echo e($item->analyte->name); ?> Guide.</h4>
                            </div>
                            <div class="modal-body" style="background-color: turquoise;">
                              <input type="hidden" name="guide_id" value="<?php echo e($item->id); ?>">
                              Confirm you want to duplicate <?php echo e($item->analyte->name); ?> analysis guide ?
                            </div>
                            <div class="modal-footer">
                              <button type="submit" class="btn btn-outline-success"><i class="mdi mdi-content-save"></i> Yes</button>
                              <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="btn-sm btn-outline-danger" data-toggle="modal" data-target="#delete-guide-<?php echo e($loop->iteration); ?>"><i class="mdi mdi-delete-empty"></i></span>

                      <div class="modal fade" id="delete-guide-<?php echo e($loop->iteration); ?>">
                        <div class="modal-dialog">
                          <form action="<?php echo e(route('delete_analysis_guide')); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="mdi mdi-delete-empty text-danger"></i> Delete <?php echo e($item->analyte->name); ?> Guide.</h4>
                            </div>
                            <div class="modal-body" style="background-color: turquoise;">
                              <input type="hidden" name="guide_id" value="<?php echo e($item->id); ?>">
                              Confirm you want to delete <?php echo e($item->analyte->name); ?> analysis guide ?
                            </div>
                            <div class="modal-footer">
                              <button type="submit" class="btn btn-outline-success"><i class="mdi mdi-content-save"></i> Yes</button>
                              <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
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
          </div>
          <div class="tab-pane fade show active p-3" id="parameters" role="tabpanel" aria-labelledby="one-tab">
            <h5 class="card-title">Analytes <div class="btn btn-sm btn-info float-right" data-target="#add-analyte" data-toggle="modal"><i class="mdi mdi-plus"></i> Add</div>
            </h5>
            <div class="table-responsive">
              <table class="table table-condensed my-small-text table-striped table-hover table-bordered table">
                <thead class="bg-light p-2">
                  <tr>
                    <th></th>
                    <th nowrap>Analyte</th>
                    <th nowrap>Decimal Places</th>
                    <th nowrap>Reporting Symbol</th>
                    <th nowrap>Reporting Unit</th>
                    <th>Reporting Time</th>
                    <th>L.O.D.</th>
                    <th>Significant Figures</th>
                    <th>Method</th>
                    <th>Equipment</th>
                    <th>Operator</th>
                    <th nowrap>Remark Capture Manual</th>
                    <th nowrap>Non Detectable</th>
                    <th nowrap>Accredited</th>
                    <th nowrap>Show on Report</th>
                    <th nowrap>Is Manual</th>
                    <th>Active?</th>
                    
                  </tr>
                </thead>
                <tbody id="analytes-holder">
                  <?php if(count($analysis_type->analysis_elements) > 0): ?>
                  <?php $__currentLoopData = $analysis_type->analysis_elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analyte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr data-element="<?php echo e($analyte->id); ?>">
                    <td valign="center" nowrap style="font-size: 17px">
                      <span style="cursor: pointer">
                        <i class="mdi mdi-arrow-up-drop-circle move-analyte-up move-analyte" data-action="move-up"></i>
                      </span>
                      <span style="cursor: pointer">
                        <i class="mdi mdi-arrow-down-drop-circle move-analyte-down move-analyte" data-action="move-down"></i> </span>
                        <span class="btn btn-sm btn-default text-primary" data-record="<?php echo e(json_encode($analyte)); ?>" data-target="#edit-analysis-element" data-toggle="modal"><i data-toggle="tooltip" title="Edit" class="mdi mdi-pencil"></i></span>
                    </td>
                    <td nowrap><?php echo e($analyte->analyte->name); ?> - <?php echo e($analyte->analyte->code); ?></td>
                    <td><?php echo e($analyte->decimal_places); ?></td>
                    <td><?php echo e($analyte->reporting_symbol); ?></td>
                    <td><?php echo e($analyte->reporting_unit); ?></td>
                    <td><?php echo e($analyte->reporting_time > 0 ? $analyte->reporting_time : $analysis_type->reporting_time); ?></td>
                    <td><?php echo e($analyte->lod == null ? '' : ( $analyte->significant_figures == null ? $analyte->lod : sigFig($analyte->lod, $analyte->significant_figures))); ?></td>
                    <td><?php echo e($analyte->significant_figures); ?></td>
                    <td nowrap><?php echo e($analyte->method()->name ?? '-'); ?></td>
                    <td nowrap><?php echo e($analyte->equipment->name  ?? '-'); ?></td>
                    <td nowrap><?php echo e($analyte->operator->name  ?? '-'); ?></td>
                    <td class="text-small">
                      <?php echo $analyte->remark_is_manual == '1' ? '<i class="mdi mdi-check-bold text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?>

                    </td>
                    <td class="text-small"><?php echo $analyte->non_detectable == '1' ? '<i class="mdi mdi-check-bold text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                    <td class="text-small"><?php echo $analyte->non_accredited == '1' ? '<i class="mdi mdi-check-bold text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                    <td class="text-small"><?php echo $analyte->show_on_report == '1' ? '<i class="mdi mdi-check-bold text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                    <td class="text-small"><?php echo $analyte->is_manual == '1' ? '<i class="mdi mdi-check-bold text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                    <td class="text-small"><?php echo $analyte->active == '1' ? '<i class="mdi mdi-check-bold text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                    
                  </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  <?php endif; ?>
                </tbody>
              </table>
              <?php if(count($analysis_type->analysis_elements) == 0): ?>
              <div class="alert alert-info">
                <i class="mdi mdi-alert"></i> No Analytes added yet.
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="edit-analysis-element" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/analysis-element/0" method="post">
        <?php echo csrf_field(); ?>
        <div class="modal-body">

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-content-save"></i> Save</button>
          <span  class="btn btn-default" data-dismiss="modal">Close</span>
        </div>
      </form>
    </div>
  </div>
</div>
<div id="add-analyte-guide" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <form class="modal-content" method="POST" action="<?php echo e(route('add-analyte-guide')); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="analysis_type_id" value="<?php echo e($analysis_type_id); ?>" />
      <input type="hidden" name="guide_id" value="0" />
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Analyte Guide</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Analyte</label>
          <select class="form-control" name="analyte_id" required placeholder="Select Analyte...">
            <option></option>
            <?php $__currentLoopData = $analysis_type->analysis_elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($a->analyte->id); ?>" data-step="<?php echo e($a->analyte->decimal_places); ?>"><?php echo e($a->analyte->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        <?php
        $standards = getStandards();
        $standard_values = getStandardValues();
        ?>
        <div class="form-group">
          <label class="control-label">Standard</label>
          <select class="form-control" name="standard" required>
            <?php $__currentLoopData = $standards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $standard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($standard->status == 1): ?>
            <option value="<?php echo e($standard->id); ?>"><?php echo e($standard->code); ?></option>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
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
          <select name="standard_value" class="form-control" id="Standard-Value" onclick="checkvalue(this)" placeholder="Employee...">
            <?php $__currentLoopData = $standard_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($value->code); ?>"><?php echo e($value->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group" id="is-value" style="display: none;">
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
<div id="add-analyte" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <form class="modal-content" method="POST" action="/analysis-elements" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="analysis_type_id" value="<?php echo e($analysis_type_id); ?>" />
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Analysis Element</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Analyte <span class="text-danger">*</span></label>
          <select class="form-control" name="analyte_id" required>
            <option value="">Select Analyte</option>
            <?php $__currentLoopData = $analytes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($a->id); ?>"><?php echo e($a->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Significant Figures</label>
          <input type="number" min="0" step="1" class="form-control" name="significant_figures" value="3" placeholder="Significant Figures..." />
        </div>
        <div class="form-group">
          <label class="control-label">Decimal Places</label>
          <input type="number" min="0" step="2" class="form-control" name="decimal_places" value="2" placeholder="Decimal Places..." />
        </div>
        <div class="form-group">
          <label class="control-label">Limit of Detection</label>
          <input type="number" step="0.0000001" class="form-control" name="lod" value="" placeholder="Limit of Detection..." />
        </div>
        <div class="form-group">
          <label class="control-label">Limit of Quantification</label>
          <input type="number" step="0.0000001" class="form-control" name="hod" placeholder="Limit of Quantification..." />
        </div>
        <div class="form-group">
          <label class="control-label">Reporting Symbol</label>
          <input type="text" class="form-control" name="reporting_symbol" placeholder="Reporting Symbol..." />
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
        <div class="form-group">
          <label class="control-label">Method <span class="text-danger">*</span></label>
          <select class="form-control" name="method" required>
            <option value="">Select Method...</option>
            <?php $__currentLoopData = getMethods(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($g['id']); ?>"><?php echo e($g['name']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Equipment</label>
          <select class="form-control" name="equipment_id" placeholder="Select Equipment...">
            <option></option>
            <?php $__currentLoopData = getEquipment(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($g['id']); ?>" data-operators="<?php echo e(json_encode($g->operator_names())); ?>"><?php echo e($g['name']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Operator</label>
          <select class="form-control"  name="operator_id" placeholder="Select Operator...">
            <option value="">Select Analyst...</option>
            <?php $__currentLoopData = $usersAnalysts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analyst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($analyst->id); ?>" ><?php echo e($analyst->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label for="" class="control-label">Reporting Time</label>
          <input type="number" name="report_time" value="<?php echo e($analysis_type->reporting_time); ?>" class="form-control">
        </div>
        <div class="form-group">
          <label for="" class="control-label"><input type="checkbox" name="remark_is_manual" value="1" id=""> Remark Capture is Manual</label>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="non_detectable" value="1" /> Not Detectable</label>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="non_accredited" value="1" /> Accredited</label>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="show_on_report" value="1" checked /> Show on Report</label>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="is_manual" value="1" checked /> Is Manual</label>
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
   var analysisElementBody = (data)=>{
    var body= $(`
      <div class="alert alert-primary p-2 d-flex">
       <i class="mdi mdi-pencil-outline" style="font-size:30px"></i> Edit Analysis Element
      </div>
      
        <div class="form-group">
          <label class="control-label">Analyte <span class="text-danger">*</span> </label>
          <select class="form-control" name="analyte_id" id="analyte_id" required>
            <option value="">Select Analyte</option>
            <?php $__currentLoopData = $analytes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($a->id); ?>"><?php echo e($a->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <input type="hidden" name="analysis_type_id" value="<?php echo e($analysis_type_id); ?>" />
        <input type="hidden" name="analysis_element_id" value="${data.id }" />
        <div class="form-group">
          <label class="control-label">Significant Figures</label>
          <input type="number" min="0" step="1" class="form-control" name="significant_figures" value="${ data.significant_figures }" placeholder="Significant Figures..." />
        </div>
        <div class="form-group">
          <label class="control-label">Decimal Places</label>
          <input type="number" min="0" step="1" class="form-control" name="decimal_places" value="${ data.decimal_places }" placeholder="Decimal Places..." />
        </div>
        <div class="form-group">
          <label class="control-label">Limit of Detection</label>
          <input type="number" step="0.0000001" class="form-control" name="lod" value="${ data.lod }" placeholder="Limit of Detection..." />
        </div>
        <div class="form-group">
          <label class="control-label">Limit of Quantification</label>
          <input type="number" step="0.0000001" class="form-control" name="hod" value="${ data.hod }}" placeholder="Limit of Quantification..." />
        </div>
        <div class="form-group">
          <label class="control-label">Reporting Symbol</label>
          <input type="text" class="form-control" name="reporting_symbol" value="${ data.reporting_symbol }" placeholder="Reporting Symbol..." />
        </div>
        <div class="form-group">
          <label class="control-label">Reporting Unit</label>
          <select class="form-control" name="reporting_unit" id="reporting_unit">
            <option value="">Select Reporting Unit...</option>
            <?php $__currentLoopData = getReportingUnits(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($g['name']); ?>"> <?php echo e($g['name']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Method <span class="text-danger">*</span></label>
          <select class="form-control" name="method" id="method_id" required>
            <option value="">Select Method...</option>
            <?php $__currentLoopData = getMethods(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($g['id']); ?>"><?php echo e($g['name']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Equipment</label>
          <select class="form-control" name="equipment_id" id="equipment_id_field" placeholder="Select Equipment...">
            <option></option>
            <?php $__currentLoopData = getEquipment(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($g['id']); ?>"><?php echo e($g['name']); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        <div class="form-group">
          <label class="control-label">Operator</label>
          <select class="form-control"  name="operator_id" id="operator_id_field" placeholder="Select Operator...">
            <option value="">Select Analyst...</option>
            <?php $__currentLoopData = $usersAnalysts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analyst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($analyst->id); ?>"><?php echo e($analyst->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label for="" class="control-label">Reporting Time</label>
          <input type="number" name="report_time" value="${data.reporting_time}" class="form-control">
        </div>
        <div class="form-group">
          <label for="" class="control-label"><input type="checkbox" name="remark_is_manual" ${data.remark_is_manual == 1 ? 'checked' : ''} value="1" id=""> Remark Capture is Manual</label>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="non_detectable" value="1" ${ data.non_detectable == 1 ? 'checked' : '' } /> Not Detectable</label>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="non_accredited" value="1" ${ data.non_accredited == 1 ? 'checked' : '' } /> Accredited</label>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="show_on_report" value="1" ${ data.show_on_report == 1 ? 'checked' : '' } /> Show on Report</label>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="active" value="1" ${ data.active == 1 ? 'checked' : '' } /> Active</label>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="is_manual" value="1" ${ data.is_manual == 1 ? 'checked' : '' } /> Is Manual</label>
        </div>
    `).clone();
    $(body).find('#analyte_id').val(data.analyte_id);
    $(body).find('#reporting_unit').val(data.reporting_unit);
    $(body).find('#method_id').val(data.method);
    $(body).find('#equipment_id_field').val(data.equipment_id);
    $(body).find('#operator_id_field').val(data.operator_id);
    
    $(body).find('#analyte_id').select2();
    $(body).find('#reporting_unit').select2();
    $(body).find('#method_id').select2();
    $(body).find('#equipment_id_field').select2();
    $(body).find('#operator_id_field').select2();
    return body;
   }
   $('#edit-analysis-element').on('show.bs.modal',(e)=>{
      var record = $(e.relatedTarget).data('record');
      var body =  analysisElementBody(record);
      $('#edit-analysis-element').find('.modal-body').empty();
      $('#edit-analysis-element').find('.modal-body').append(body);

   })

    var configureThemArrow = function() {
      $('#analytes-holder').find('tr').find('.move-analyte-up').addClass('text-success').removeClass('text-muted');
      $('#analytes-holder').find('tr:first-child').find('.move-analyte-up').addClass('text-muted').removeClass('text-success');

      $('#analytes-holder').find('tr').find('.move-analyte-down').addClass('text-success').removeClass('text-muted');
      $('#analytes-holder').find('tr:last-child').find('.move-analyte-down').addClass('text-muted').removeClass('text-success');
    }

    configureThemArrow();

    console.log('test');
    $('#analytes-holder').on('click', '.move-analyte:not(.text-muted)', function() {
      console.log('test2');
      var action = $(this).data('action');
      var pTR = $(this).parents('tr');
      var i = pTR.index();
      console.log(pTR.data('element'));
      $.ajax({
        url: "/move-analysis-analyte/" + action + "/<?php echo e($analysis_type->id); ?>/" + pTR.data('element'),
        dataType: 'json',
        beforeSend: function() {
          $('#analytes-holder').find('tr').find('.move-analyte-up').addClass('text-muted');
          $('#analytes-holder').find('tr').find('.move-analyte-down').addClass('text-muted');
        },
        success: function(js) {
          var siblingIndex = action == 'move-up' ? (i - 1) : (i + 1);
          siblingIndex = siblingIndex < 0 ? 0 : siblingIndex;

          var sibling = $('#analytes-holder').find('tr').get(siblingIndex);

          if (action == 'move-up') {
            $(sibling).before(pTR);
          } else {
            $(pTR).before(sibling);
          }

          configureThemArrow();
        }
      })



    });
    var getAnalyteMethods = (analyte_id, callback) => {
      $.ajax({
        url: `/get/Analyte/${analyte_id}/Methods`,
        method: 'GET',
        success: (data) => {
          callback(data)
        },
        error: (data) => {
          console.log(data);
        }

      })

    }
    $('#add-analyte').on('show.bs.modal', () => {
      $('#add-analyte').find('[name="analyte_id"]').on('change', (e) => {
        $analyte = $('#add-analyte').find('[name="analyte_id"]').val();
        getAnalyteMethods($analyte, (data) => {
          $('#add-analyte').find('[name="method"]').empty();
          $.each(data, (i, e) => {
            var option = `<option value="${e}">${i}</option>`;
            $('#add-analyte').find('[name="method"]').append(option)
          })
          $('#add-analyte').find('[name="method"]').select2();
        })
      })
    })
    $('#add-analyte-guide').on('change', '[name="analyte_id"]', function() {
      var divider = 10 ** parseInt($(this).children('option:selected').data('step'));
      var step = (1 / divider);


      $('#add-analyte-guide').find('[name="value"]').attr("step", step);
    });

    window.setTimeout(function() {
      $('[name="equipment_id"]').trigger('change');
    }, 100);

    window.setTimeout(function() {
      // var selected = $('[name="operator_id"]').each(function(e) {
      //   var sel = $(this).data('selected');
      //   $(this).val(sel).trigger('change');
      // });
    }, 160);
  });

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
      text2.style.display = "none";
      $(low).removeAttr('required', false);
      $(high).removeAttr('required', false);
    }

  }

  function externaled(item) {

    var index = item.getAttribute('data-id');
    var is_standard = 'is-standard-value-' + index;
    var standard_value = 'standard-values-' + index;
    var is_value = '#Is-Value-' + index;

    var standard = '#Standard-Value-' + index;

    var checkbox = document.getElementById(is_standard);
    var text = document.getElementById(standard_value);
    if (checkbox.checked == true) {
      text.style.display = "block";
      $('#Standard-Value').attr('required', true);

      inhoused(item);
    } else {
      text.style.display = "none";
      $(standard).removeAttr('required', false);
      $(is_value).removeAttr('required', false);
    }

  }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/lab/analysis-types/show.blade.php ENDPATH**/ ?>