<?php $__env->startSection('title2'); ?>
<title> Lab-Quotations </title>
<style type="text/css">
    .tab-card {
        border: 1px solid #eee;
    }

    .tab-card-header {
        background: none;
    }

    /* Default mode */
    .tab-card-header>.nav-tabs {
        border: none;
        margin: 0px;
    }

    .tab-card-header>.nav-tabs>li {
        margin-right: 2px;
    }

    .tab-card-header>.nav-tabs>li>a {
        border: 0;
        border-bottom: 2px solid transparent;
        margin-right: 0;
        color: #737373;
        padding: 2px 15px;
    }

    .tab-card-header>.nav-tabs>li>a.show {
        border-bottom: 2px solid #007bff;
        color: #007bff;
    }

    .tab-card-header>.nav-tabs>li>a:hover {
        color: #007bff;
    }

    .tab-card .nav-link.active {
        background-color: #dadccd !important;
        border: 1px solid #cccebf !important;
    }

    .tab-card-header>.tab-content {
        padding-bottom: 0;
    }

    .my-small-text {
        font-size: 13px !important;
    }

    .removeThis {
        z-index: 12;
        position: absolute;
        cursor: pointer;
        top: 0px;
        right: 2px;
        padding: 1px 4px;
        font-size: 12px;
        background-color: red;
        border-radius: 50%;
        color: #fff;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.08);
    }
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
    <?php
    $items = array(
        array(
            'link' => '/lab-dashboard',
            'name' => 'Dashboard',
            'icon' => null
        ),


        array(
            'link' => '/billing-quotation',
            'name' => 'Quotations',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $stage,
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
    <h4 class="p-4">
        <i class="mdi mdi-file-cad"></i>Billing | Quotations - <?php echo e($stage); ?>

        <?php if($stage == 'Quote In Preparation'): ?>
        <span class="btn btn-outline-info btn-sm float-right" data-toggle="modal" data-target="#add-quotation"><i class="mdi mdi-plus"></i> Add</span>
        <?php endif; ?>
        <div class="dropleft float-right">

            <span style="font-size:15px;border-radius: 3em;border-color: white;background-color:white;position: 0 0;" class="float-right mr-5 p-2 btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Drafts
                <small class="float-right badge badge-pill badge-primary mt-1 ml-1"><?php echo e($drafts->count()); ?></small></span>

            <div class="dropdown-menu p-2" style="max-height: 70vh; width:300%; overflow:auto" aria-labelledby="dropdownMenuButton">
                <?php $__currentLoopData = $drafts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $draft): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="dropdown-item card mb-2" style="box-shadow: 2px 2px 2px 2px;width:100%; height:40%; font-size:15px" href="<?php echo e(route('add-qoute-details-view',['id'=>$draft->id,'stage'=>$draft->status])); ?>">
                    <div class="card-bodys">
                        Quotation <?php echo e($draft->quote_number); ?>


                        <i class="float-right mb-0 mt-3" style="font-size: 12px;">(<?php echo e($draft->created_at); ?>)</i>
                    </div>
                </a>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </div>
        </div>




    </h4>
    <?php if($stage == 'All Quotations' ): ?>
    <div class="filter">
        <div class="p-3"> <u><b>Apply Filter</b></u</div>
        <div class="card mb-5">
            <form action="">
                <div class="card-body">
                    <?php echo csrf_field(); ?>
                    <div class="row">
    
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Quote Type</label>
                                <select name="quote_type" id="quote_type" class="form-control">
                                    <option value="">Choose Quotation Type</option>
                                    <option value="General">General Quotation</option>
                                    <option value="Analysis">Analysis Quotation</option>
                                    
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 sample_type_field hidden">
                            <div class="form-group">
                                <label for="" class="control-label">Sample Type</label>
                                <select name="sample_type_id" id="sample_type_id" class="form-control">
                                    <?php $__currentLoopData = $sample_types ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($st->id); ?>"><?php echo e($st->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 analysis_type_field hidden">
                            <div class="form-group">
                                <label for="" class="control-label">Analysis Type</label>
                                <select name="analysis_type_id" id="analysis_type_id" class="form-control"></select>
                            </div>
                        </div>
                        <div class="col-md-3 analyte_field hidden">
                            <div class="form-group">
                                <label for="" class="control-label">Analyte</label>
                                <select name="analyte_id" id="analyte_id" class="form-control"></select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">End Date</label>
                                <input type="date" name="end_date" id="" class="end_date form-control">
                            </div>
                        </div>
                        <div class="col-md-12 bg-light p-2 item_description_field hidden">
                            <div class="form-group">
                                <label for="" class="control-label">Item Description</label>
                                <textarea class=" form-control" value="" name="item_description" rows="1" placeholder=""> </textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-filter-variant-plus"></i> Apply Filter</button>
                </div>
            </form>

        </div>
    </div>
    <?php endif; ?>

    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">

                <li class="nav-item">
                    <a class="nav-link" id="invoice-tab" data-toggle="tab" href="#Invoice" role="tab" aria-controls="Invoice" aria-selected="true"><i style="font-size: 20px;" class="mdi mdi-file-cad"></i> Quotations</a>
                </li>

            </ul>
        </div>
        <div class="tab-content" id="Invoice-tabs-content">

            <div class="tab-pane fade show active p-3" id="Invoice" role="tabpanel" aria-labelledby="one-tab">

                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm" style="width: 130% !important;">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th>Quote No</th>
                                <th>Quote Type</th>
                                <?php if($stage == 'All Quotations'): ?>
                                <th>Status</th>
                                <?php endif; ?>
                                <th>Quote Date</th>
                                <th>Expiry Date</th>
                                <th>Customer</th>
                                <th>Customer Contact</th>
                                <th>Prepared By</th>
                                <th>Email Customer</th>

                                <th>Pricelist</th>
                                <th>Total</th>
                                <th></th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quotation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td>
                                    <a href="<?php echo e(route('add-qoute-details-view',['id'=>$quotation->id])); ?>"><?php echo e($quotation->quote_number); ?></a>
                                </td>
                                <td><?php echo e($quotation->quotation_type); ?></td>
                                <?php if($stage == 'All Quotations'): ?>
                                <td><?php echo e($quotation->status); ?></td>
                                <?php endif; ?>
                                <td><?php echo e($quotation->quote_date); ?></td>
                                <td><?php echo e($quotation->expiring_date); ?></td>
                                <td><?php echo e($quotation->customer); ?></td>
                                <td><?php echo e($quotation->contact); ?></td>
                                <td><?php echo e($quotation->prepared_by_name); ?></td>
                                <td><?php echo e($quotation->email_to_customer == '' ? '-':$quotation->email_to_customer); ?></td>
                                <td><?php echo e($quotation->pricelist); ?></td>
                                <td>
                                    <p class="float-right mt-2"> <?php echo e(number_format($quotation->total_amount,2)); ?></p>
                                </td>

                                <!-- <a class="btn btn-outline-primary btn-sm" href=""><i class="mdi mdi-pencil"></i></a>
                                    <a class="btn btn-outline-warning btn-sm" href=""><i class="mdi mdi-content-duplicate"></i></a> -->

                                <td>
                                    <a href="<?php echo e(route('add-qoute-details-view',['id'=> $quotation->id,'stage'=>'Quote In Reception'])); ?>" class="btn btn-sm btn-outline-primary " data-toggle="tooltip" title="Edit"><i class="mdi mdi-pencil"></i></a>
                                    <a href="<?php echo e(route('add-qoute-details-view',['id'=>$quotation->id])); ?>" class="btn btn-outline-success btn-sm" data-toggle="tooltip" title="View"><i class="mdi mdi-eye"></i></a>
                                    <a href="<?php echo e(route('clone_quotation',['id'=>$quotation->id])); ?>" class="btn btn-sm btn-outline-warning"><i class="mdi mdi-content-duplicate" data-toggle="tooltip" title="Clone"></i></a>
                                </td>

                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>
<div class="modal fade" id="add-quotation" role="dialog">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo e(route('add-quotation-header')); ?>" method="POST" class="modal-content">
            <?php echo csrf_field(); ?>
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="mdi mdi-plus"></i> Quotation
                </h4>
            </div>
            <div class="modal-body">

                <div class="form-section">
                    <div class="form-group">
                        <label class="control-label">Client</label>
                        <select name="client" class="form-control" id="select-client" aria-readonly="true" aria-placeholder="Choose Client..." required>
                            <option value="" disabled selected>Choose Client...</option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($customer->id); ?>"><?php echo e($customer->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="form-group contacts">

                        <label class="control-label">Client Contact</label>
                        <select name="client_contact" id="select-client-contact" class="form-control" aria-placeholder="Select Client Contact..." required>
                            <option value="" disabled selected>Select Client Contact</option>

                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Quotation Type</label>
                        <select name="quotation_type" id="selecy-quotation-type" class="form-control" required>
                            <option value="">Choose Quotation Type</option>
                            <option value="General">General Quotation</option>
                            <option value="Analysis">Analysis Quotation</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Quotaion Date</label>
                        <input type="date" name="quotation_date" class="form-control" placeholder="Quotation Date..." required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Expiry Date</label>
                        <input type="date" name="expire_date" class="form-control" placeholder="Expiration Date..." required>
                    </div>

                </div>

            </div>

            <div class="footers pt-3 p-2 bg-light" style="height:70px">
                <button type="submit" class="btn btn-outline-primary float-right"><i class="mdi mdi-content-save"></i>Next</button>
                <button type="button" class="btn btn-outline-danger float-left" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<script>

</script>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<script>
    $(function() {
        $('#quote_type').on('change',(e)=>{
            $quote_type = $('#quote_type').val();
            if($quote_type == 'General'){
                $('.sample_type_field').addClass('hidden');
                $('.analysis_type_field').addClass('hidden');
                $('.analyte_field').addClass('hidden');
                $('.item_description_field').removeClass('hidden');
            }else if($quote_type == 'Analysis'){
                
                $('.sample_type_field').removeClass('hidden');
                $('.analysis_type_field').removeClass('hidden');
                $('.analyte_field').removeClass('hidden');
                $('.item_description_field').addClass('hidden');
            }
            
            
        });
        $('#sample_type_field').on('change',(e)=>{
            $value  = $('#sample_type_field').val()
        })
        $('#select-client').on('change', function() {
            var client = $(this).val();

            console.log();
            $.ajax({
                url: '/fetch-customer-contacts/' + client,
                beforeSend: function() {
                    $('#select-client-contact').empty();
                },
                success: function(data) {
                    console.log(data);
                    $.each(data, function(j, s) {
                        console.log(s);
                        var $option = $(`
                            <option value = "${s.id}">${s.first_name} ${s.middle_name ?? ''} ${s.last_name ?? ''}</option>
                        `);
                        $('#select-client-contact').append($option);
                    })
                },
                error: function(data) {
                    console.log(data);
                }
            })
        })
    });
</script>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/invoice/quotation-index.blade.php ENDPATH**/ ?>