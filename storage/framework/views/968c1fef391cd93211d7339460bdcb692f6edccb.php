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

    .btn-default:hover {
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }

    .table-responsive {
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
    <?php
    $items = array(
        array(
            'link' => '/prp',
            'name' => 'PRP',
            'icon' => null,
        ),
        array(
            'link' => '/prp/day-batch/index',
            'name' => 'Day ' . $day_batch->day_code,
            'icon' => null,
        ),
        array(
            'link' => '/prp/day-batch/show/' . $day_batch->id . '/type/prd',
            'name' => 'PRD Files',
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
    <h5 class="p-2 mt-2">
        <i class="mdi mdi-file-account-outline"></i>Week <?php echo e($harvest_week->week_no); ?> | <?php echo e($day_batch->day_code); ?> | Prd Files

        <span class="btn btn-default text-primary btn-sm float-right" data-toggle="modal" data-target="#pull_record"><i class="mdi mdi-sync"></i> Sync Pull PRD Files</span>

        <a href="/prp/show/PrdRawData/Harvesters" class="btn btn-default btn-sm float-right mr-3 text-warning"><i class="mdi mdi-account-multiple"></i> View Harvesters</a>
        <!-- <a href="" id="errorRoute" class="btn btn-default bg-light btn-sm float-right mr-3 text-danger hidden"><i class="mdi mdi-alert-decagram"></i> View Errors</a> -->



    </h5>
    <div class="table-responsive mt-4 p-3">
        <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Filename</th>
                    <th>DayCode</th>
                    <th>Synced By</th>
                    <th>No of records</th>

                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $prds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($prd->file_name); ?></td>
                    <td><?php echo e(getPrpNewBatchDayByID($prd->day_batch_id)->day_code); ?></td>
                    <?php $sync_user = getUserById($prd->sync_user_id) ?>
                    <td><?php echo e($sync_user->first_name); ?> <?php echo e($sync_user->last_name); ?></td>
                    <td><?php echo e($prd->record_count == 0 ? '-'  : $prd->record_count); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" data-backdrop="static" data-daybatch="<?php echo e(json_encode($day_batch->id)); ?>" data-keyboard="false" id="pull_record" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php echo e(route('savePrdFilesToServe')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-primary p-2 d-flex">
                        <i class="mdi mdi-cloud-upload-outline mdi-24px"></i>
                        <span class="p-2">Multi-select and upload the prd files into the system</span>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Choose Files <small class="text-danger">*</small></label>
                        <input type="file" name="file[]" multiple="multiple" id="multiplefiles" class="form-control">
                    </div>
                    <input type="text" class="hidden" name="duplicate_files" id="duplicate_files">
                    <input type="hidden" name="day_batch" value="<?php echo e($day_batch->id); ?>">
                    <input type="hidden" name="harvest_week_id" value="<?php echo e($day_batch->harvest_week_id); ?>">
                    <div class="before-validate mt-2">
    
                    </div>
                    <div class="mt-3 bg-light p-3">
                        <b><u>Selected Filenames:</u></b>
                        <span class="float-right text-bold">Duplicates <span class="badge badge-pill btn-rounded badge-warning p-2 duplicate-count">0</span></span>
                    </div>
    
                    <div class="files-given pr-3 pl-3 row" style="clear: both;">
    
    
                    </div>
                </div>
                <div class="modal-footer" id="close_pull_records">
                    <button type="submit" id="submit-pull-records" disabled class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="add-prd-path" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('storePrdFilePath')); ?>" method="post">
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
    $(() => {
        var beforeBody = () => {
            var body = $(`
                <center class="pls-img">
                    <img src="/images/pls.gif" style="height: 250px !important;" alt="">
                </center>
                <input  type="checkbox" name="" disabled id="data-valid"> <span>Validating Uploaded Files</span>
            `).clone();
            return body;
        }
        var uploadedFilenamesBody = (data, color = '') => {
            var body = $(`
            <div class="col-md-6 p-2 ${color}" data-file="${data}">
                <i class="mdi mdi-menu-right-outline"></i> ${data}
                
            </div>
            `).clone();
            return body;
        }
        var confirmUploadBodySuccess = () => {
            var body = $(`
            <div class="alert alert-primary p-2 d-flex">
                <i class="mdi mdi-alert-decagram mdi-24px"></i>
                <span class="p-2">Confirm the following filenames above are the PRD files that you wantu upload into the system.</span>
            </div>
            `).clone();
        }

        var allDuplicatesBody = () => {
            var body = $(`
            <div class="alert alert-warning all-duplicate-body p-2 mt-3 d-flex">
                <i class="mdi mdi-alert-decagram mdi-24px"></i>
                <span class="p-2">All the files selected are Duplicates ie they have already been uploaded</span>.
            </div>
            `).clone();
            return body;
        }



        var newFileList = []
        let getDuplicateRecords = (names_data, callback) => {
            $.ajax({
                url: '/prp/get/Duplicate-Files/By-Filenames/Ajax',
                type: 'get',
                dataType: 'json',
                data: {
                    _token: null,
                    names: names_data
                },
                success: (data) => {
                    callback(data);
                },
                error: (data) => {
                    console.log(data)
                }

            });
        }
        $('#pull_record').find('input[type="file"').on('change', (event) => {
            newFileList = Array.from(event.target.files) || [];
            $names = []
            $('#pull_record').find('.files-given').empty()

            $before_body = beforeBody();
            $('#pull_record').find('.before-validate').empty();
            $('#pull_record').find('.before-validate').append($before_body);



            for (var i = 0; i < newFileList.length; i++) {
                $names.push(newFileList[i].name)
            }
            getDuplicateRecords($names, (duplicateFiles) => {
                $('#pull_record').find('#duplicate_files').val(duplicateFiles);
                $('#pull_record').find('.duplicate-count').empty();
                $('#pull_record').find('.duplicate-count').append(duplicateFiles.length)
                $count = 0;
                $.each(duplicateFiles, (i, dupName) => {
                    $dupIndex = $names.indexOf(dupName);
                    if ($dupIndex >= 0) {
                        newFileList.splice($dupIndex - $count, 1);
                        ++$count;
                    }

                });
                $('#pull_record').find('.pls-img').remove()
                $('#pull_record').find('#data-valid').prop('checked', true);
                $.each($names, (i, $name) => {
                    if (duplicateFiles.indexOf($name) >= 0) {
                        $body = uploadedFilenamesBody($name, 'text-warning')
                        $('#pull_record').find('.files-given').append($body);
                    } else {
                        $body = uploadedFilenamesBody($name)
                        $('#pull_record').find('.files-given').append($body);
                    }

                })

                if (newFileList.length == 0) {
                    $dupBody = allDuplicatesBody();
                    $('#pull_record').find('.modal-body').append($dupBody)
                    $('#pull_record').find('#submit-pull-records').prop('disabled', true)
                } else {
                    $('#pull_record').find('.dup-body').remove();
                    $('#pull_record').find('#submit-pull-records').prop('disabled', false)
                    $('#pull_record').find('.all-duplicate-body').remove();
                };


            })

        });
    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/Data/prd/index.blade.php ENDPATH**/ ?>