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

    .header-area {
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
            'name' => 'System Configuration',
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
    <form action="<?php echo e(route('addSystemParams')); ?>" method="post">
        <?php echo csrf_field(); ?>
        <h5 class="p-3" style="font-weight: 550;">
            <span><i class="mdi mdi-cogs mdi-24px mr-2"></i> System Parameters</span>

            <button type="submit" class="btn btn-sm btn-outline-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
            <input type="hidden" name="system_params_id" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->id : 0); ?>">
            <span class="btn btn-outline-success float-right btn-sm mr-2" data-target="#affect-week" data-toggle="modal"><i class="mdi mdi-cloud-sync"></i> Sync To Active Week</span>
        </h5>
        <div class="card mt-3">
            <div class="card-body">
                <div class="row">
                    <div class=" col-sm-6 border-right">
                        <div class="header-area text-muted mb-3 border-bottom">

                            <b>Bonus Parameters</b>
                        </div>
                        <div class="bracket-data p-2 mt-3 border-bottom">
                            <span class="text-bold text-muted"><u>Bracket (Easy)</u></span>
                            <div class="input-fields mt-2" style="display: flex;">
                                <div class="form-group mr-5">
                                    <label class="control-label">Floor <small class="text-danger">*</small></label>
                                    <input type="text" name="easy_floor" id="easy_floor" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->easy_floor : ''); ?>" required class="form-control thershold_parameter" placeholder="Speed Floor ...">
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Ceiling <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control thershold_parameter" id="easy_ceil" required name="easy_ceil" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->easy_ceiling : ''); ?>" placeholder="Speed Ceiling ...">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Easy Speed Factor: <input type="text" disabled value="1" style="width:10%"></label>
                            </div>
                        </div>
                        <div class="bracket-data p-2 mt-3 border-bottom">
                            <span class="text-bold text-muted"><u>Bracket (Medium)</u></span>
                            <div class="input-fields mt-2" style="display: flex;">
                                <div class="form-group mr-5">
                                    <label class="control-label">Floor <small class="text-danger">*</small> </label>
                                    <input type="text" name="medium_floor" id="medium_floor" required value="<?php echo e(isset($system_parameters->id) ? $system_parameters->medium_floor : ''); ?>" class="form-control thershold_parameter" placeholder="Speed Floor ...">
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Ceiling <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control thershold_parameter" required name="medium_ceiling" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->medium_ceiling : ''); ?>" id="medium_ceil" placeholder="Speed Ceiling ...">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Medium Speed Factor: <input type="text" disabled value="2" style="width:10%"></label>
                            </div>
                        </div>
                        <div class="bracket-data p-2 mt-3 ">
                            <span class="text-bold text-muted"><u>Bracket (Difficult)</u></span>
                            <div class="input-fields mt-2" style="display: flex;">
                                <div class="form-group mr-5">
                                    <label class="control-label">Floor <small class="text-danger">*</small></label>
                                    <input type="text" name="difficult_floor" id="difficult_floor" required value="<?php echo e(isset($system_parameters->id) ? $system_parameters->difficult_floor : ''); ?>" class="form-control thershold_parameter" placeholder="Speed Floor ...">
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Ceiling <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control thershold_parameter" id="difficult_ceiling" required name="difficult_ceil" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->difficult_ceiling : ''); ?>" placeholder="Speed Ceiling ...">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Difficult Speed Factor: <input type="text" disabled value="1" style="width:10%"></label>
                            </div>
                        </div>


                    </div>
                    <div class=" col-sm-6">
                        <div class="header-area text-muted mb-2 p-1">
                            <b>Bonus Calculation Parameters</b>
                        </div>
                        <div class="row mb-3 p-2">

                            <div class=" col-sm-4 wage">
                                <div class="form-group">
                                    <label for="" class="control-label">Minimum Wage Monthly <small class="text-danger">*</small></label>
                                    <input type="text" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->minimum_wage_monthly : ''); ?>" required name="prp_overall_amount" id="prp_overall_amount" placeholder="Minimum Wage Monthly..." class="form-control bonus_parameter">
                                </div>
                            </div>
                            <div class=" col-sm-4 wage">
                                <div class="form-group">
                                    <label for="" class="control-label">Weekly % Allocation <small class="text-danger">*</small></label>
                                    <input type="text" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->minimum_wage_percentage : ''); ?>" required name="prp_perc_weekly" id="prp_perc_weekly" placeholder="Weekly % Allocation..." class="form-control bonus_parameter">
                                </div>
                            </div>
                            <div class=" col-sm-4 wage">
                                <div class="form-group">
                                    <label for="" class="control-label">Weekly Bonus </label>
                                    <input type="text" readonly value="<?php echo e(isset($system_parameters->id) ? $system_parameters->prp_bonus : ''); ?>" name="prp_bonus" id="prp_bonus" placeholder="PRP Weekly Bonus ..." class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="header-area text-muted mb-2 p-1">
                            <b>Amount Allocation</b>
                        </div>
                        <div class="row mb-3 p-2">
                            <div class=" col-sm-6 wage">
                                <div class="form-group">
                                    <label for="" class="control-label">Speed % Allocation <small class="text-danger">*</small></label>
                                    <input type="text" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->speed_percentage : ''); ?>" required name="prp_perc_speed_alloc" id="prp_perc_speed_alloc" placeholder="Speed % Allocation ..." class="form-control">
                                </div>
                            </div>
                            <div class=" col-sm-6 wage">
                                <div class="form-group">
                                    <label for="" class="control-label">Quality % Allocation</label>
                                    <input type="text" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->quality_percentage : ''); ?>" readonly name="prp_perc_quality_alloc" id="prp_perc_quality_alloc" placeholder="Quality % Allocation ..." class="form-control">
                                </div>
                            </div>
                            <div class=" col-sm-6 wage">
                                <div class="form-group">
                                    <label for="" class="control-label">Speed Bonus Amount</label>
                                    <input type="text" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->speed_amount : ''); ?>" name="prp_speed_amount" readonly id="prp_speed_amount" placeholder="Speed Bonus Amount ..." class="form-control">
                                </div>
                            </div>
                            <div class=" col-sm-6 wage">
                                <div class="form-group">
                                    <label for="" class="control-label">Quality Bonus Amount</label>
                                    <input type="text" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->quality_amount : ''); ?>" readonly id="prp_quality_amount" name="prp_quality_amount" placeholder="Quality Bonus Amount ..." class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="header-area text-muted mb-2 p-1">
                            <b>Quality Remark Threshold</b>
                        </div>
                        <div class="row mb-3 p-2">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label"> Harvest Hrs <small class="text-danger">*</small></label>
                                    <input type="text" required placeholder="Harvest Hrs..." id="harvest_hours" name="harvest_hours" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->harvest_hrs : ''); ?>" class="form-control thershold_parameter">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label"> Harvest Hrs % <small class="text-danger">*</small></label>
                                    <input type="text" required name="harvest_hours_percentage" id="harvest_hours_percentage" placeholder="Harvest Hrs % .." class="form-control thershold_parameter" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->harvest_hrs_percentage : ''); ?>">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label"> Harvest Days <small class="text-danger">*</small></label>
                                    <input type="text" required id="harvest_days" placeholder="Harvest Days..." name="harvest_days" class="form-control thershold_parameter" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->harvest_days : ''); ?>">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Easy</label>
                                    <input type="text" name="easy_threshold" id="easy_threshold" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->easy_quality_remark_thershold : ''); ?>" readonly class="form-control thresholds">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Medium</label>
                                    <input type="text" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->medium_quality_remark_thershold : ''); ?>" name="medium_threshold" id="medium_threshold" readonly class="form-control thresholds">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Difficult</label>
                                    <input type="text" name="difficult_threshold" id="difficult_threshold" value="<?php echo e(isset($system_parameters->id) ? $system_parameters->difficult_quality_remark_thershold : ''); ?>" readonly class="form-control thresholds">
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

            </div>
        </div>

    </form>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="affect-week" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('affectWeekDataTrigger')); ?>" action="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-success d-flex">
                        <i class="mdi mdi-alert-decagram mdi-36px"></i>
                        <div class="p-2">
                            Confirm you want to implement the current configurations to the active harvest weeks: <br><br>
                            <div class="bg-white p-2">
                                <?php $__currentLoopData = $harvest_weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week_): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-group">
                                    <?php
                                        $status_name = '';
                                        $status_name = $week_->status == 0 ? 'Active' : $status_name; 
                                        $status_name = $week_->status == 1 ? 'Partially Closed' : $status_name;
                                    ?>
                                    <label for="" class="control-label"><input checked type="checkbox" name="week_ids[]" class="mr-2" value="<?php echo e($week_->id); ?>" id=""> Harvest Week <?php echo e($week_->week_no); ?> - Status <?php echo e($status_name); ?></label>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </div>
                            
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="mdi mdi-thumb-up"></i> Yes, Affect</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Cancel</span>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(() => {
        let calculatePrpBonus = (amount, percentage) => {
            let prp_bonus = amount * percentage / 100;
            $('#prp_bonus').val(prp_bonus)

        }
        let renderFieldsNull = () => {
            $('#prp_bonus').val('');
            $('#prp_perc_quality_alloc').val('');
            $('#prp_speed_amount').val('');
            $('#prp_quality_amount').val('');

        }
        $('.bonus_parameter').on('change', (e) => {
            let prp_overall_amount = $('#prp_overall_amount').val();
            let perc_weekly = $('#prp_perc_weekly').val();
            if (prp_overall_amount, perc_weekly != '') {
                calculatePrpBonus(prp_overall_amount, perc_weekly)
                $('#prp_perc_speed_alloc').trigger('change')
            } else {
                renderFieldsNull()
            }
            // console.log($(e.target).val())
        });
        let calculateSpeedQualityBonusAmount = (prp_bonus, speed_perc) => {
            let speed_amount = prp_bonus * speed_perc / 100;
            let quality_perc = 100 - parseInt(speed_perc);
            let quality_amount = prp_bonus * quality_perc / 100;
            $('#prp_perc_quality_alloc').val(quality_perc);
            $('#prp_speed_amount').val(speed_amount);
            $('#prp_quality_amount').val(quality_amount);

        }
        $('#prp_perc_speed_alloc').on('change', (e) => {
            let perc_speed_alloc = $(e.target).val();
            let prp_bonus = $('#prp_bonus').val();
            if (prp_bonus, perc_speed_alloc != '') {
                calculateSpeedQualityBonusAmount(prp_bonus, perc_speed_alloc);
            }

        });
        $('.thershold_parameter').on('change', () => {
            let easy_floor = $('#easy_floor').val();
            let medium_floor = $('#medium_floor').val()
            let difficult_floor = $('#difficult_floor').val();
            let harvest_hrs = $('#harvest_hours').val();
            let harvest_hrs_perc = $('#harvest_hours_percentage').val();
            let harvest_days = $('#harvest_days').val();
            if (easy_floor, medium_floor, difficult_floor, harvest_hrs, harvest_hrs_perc, harvest_days != '') {
                let easy_threshold = easy_floor * harvest_days * harvest_hrs * harvest_hrs_perc;
                let medium_threshold = medium_floor * harvest_days * harvest_hrs * harvest_hrs_perc;
                let difficult_threshold = difficult_floor * harvest_days * harvest_hrs * harvest_hrs_perc;
                $('#easy_threshold').val(easy_threshold);
                $('#medium_threshold').val(medium_threshold);
                $('#difficult_threshold').val(difficult_threshold);
                console.log('here')
            }
            console.log(`easy floor ${easy_floor}`)
            console.log(`medium floor ${medium_floor}`)
            console.log(`difficult floor ${difficult_floor}`);
            console.log(` hervest hrs ${harvest_hrs} `)
            console.log(`harvest % ${harvest_hrs_perc}`)
            console.log(` harvest days ${harvest_days}`)

        })

      


    })
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/configuration/system_params.blade.php ENDPATH**/ ?>