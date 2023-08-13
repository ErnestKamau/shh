<?php $__env->startSection('title2'); ?>
<title> Lab-Invoice </title>

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
            'link' => route('quotation-index', ['stage' => 'Quote Complete']),
            'name' => 'Quote Complete',
            'icon' => null
        ),

        array(
            'link' => null,
            'name' => $header[0]->quote_number,
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
    <h4 class=" mt-2">
        <?php
        $header_id = $header[0]->id;

        ?>
        <span class=" mb-2 float-left">
            <i class="mdi mdi-file-cad"></i> Billing | Quotations <?php echo e($header[0]->quote_number); ?>

        </span>
        <div class="nav-item dropdown float-left">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" style="color:black;font-size:14px" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-compare-vertical"></i> Move To workflow
            </a>
            <div class="dropdown-menu" style="font-size: 13px;" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="<?php echo e(route('change_quotation_workflow',['id'=>$header[0]->id,'stage'=>'Quote In Preparation'])); ?>"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation In Preparation</a>
                <a class="dropdown-item" href="<?php echo e(route('change_quotation_workflow',['id'=>$header[0]->id,'stage'=>'Quote In Approval'])); ?>"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation In Approval</a>
                <a class="dropdown-item" href="<?php echo e(route('change_quotation_workflow',['id'=>$header[0]->id,'stage'=>'Quote Complete'])); ?>"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation Complete</a>

            </div>
        </div>

        <span style="font-size: 10px;" class="badge badge-pill p-2 bg-white <?php echo e($header[0]->approved_by > 0 ? 'text-success' : 'text-danger'); ?>"><i class="mdi  <?php echo e($header[0]->approved_by > 0 ? 'mdi-thumb-up' : 'mdi-alert-decagram'); ?>"></i> <?php echo e($header[0]->approved_by > 0 ? 'Approved' : 'Awaiting Approval'); ?></span>
        <span style="font-size: 10px;" class="badge badge-pill p-2 bg-white <?php echo e($header[0]->is_complete > 0 ? 'text-success' : 'text-primary'); ?>"><i class="mdi  <?php echo e($header[0]->is_complete > 0 ? 'mdi-thumb-up' : 'mdi-alert-decagram'); ?>"></i> <?php echo e($header[0]->is_complete > 0 ? 'Complete' : 'Not Complete'); ?></span>

        <?php if($header[0]->is_complete == 1): ?>
        <span class="btn btn-default btn-sm float-right" data-target="#print-quotation" data-header="<?php echo e($header[0]->id); ?>" data-toggle="modal"><i class="mdi mdi-printer"></i> Process PDF</span>
        <?php if($header[0]->is_print == 1): ?>
        <?php 	$path = '/storage'.$header[0]->upload_url;?>
        <button data-toggle="modal" data-target="#quotation-upload" class="btn btn-outline-dark btn-sm float-right mr-2"><i class="mdi mdi-share-all"></i> Send Quotation</button>
        <a href="<?php echo e($path); ?>" target="_blank" class="btn btn-outline-success btn-sm float-right mr-2"><i class="mdi mdi-eye"></i> View Quote</a>
        <?php else: ?>
        
        <a href="/billing-quotation-view-final/<?php echo e($header[0]->id); ?>" target="_blank" class="btn btn-outline-success view-quote mr-2 float-right btn-sm hidden"><i class="mdi mdi-sync-circle"></i> Refresh Page</a>
        <?php endif; ?>
        <?php endif; ?>
    </h4><br>
    <div class="card p-3 mt-5" id="quotation-document" style="clear: both;">
        <div class="card-header p-2" style="border-bottom: 1px solid #0000ff;background-color:white ">
            <div class="header p-3">

                <?php echo $company->show_on_reports == 1 ? '<img src='.$company->logo.' style="position:absolute;width:260px;" class="float-right mt-3" />':''; ?>

                <div class="company-info float-right" style="font-size: 13px;">

                    <p style="text-align: right;">

                        <?php echo e($company->name); ?> <br>
                        <?php echo e($company->address ?? '-'); ?> <br>
                        <?php echo e($company->location ?? '-'); ?> <br>
                        <?php echo e($company->street ?? '-'); ?> <br>
                        Email: <?php echo e($company->email ?? '-'); ?> <br>
                        Website: <?php echo e($company->website ?? '-'); ?> <br>
                        Tel: <?php echo e($company->telephone); ?> Cell: <?php echo e($company->cell_phone ?? '-'); ?>

                    </p>

                </div>
            </div>


        </div>
        <div class="card-body pt-0">
            <h5 class="text-center">QUOTATION</h5>
            <div class="quote_header pt-0" style="font-size: 13px;">
                <div class="customer-details float-left">
                    <p><?php echo e($header[0]->name); ?> <br>
                        <?php echo e($header[0]->physical_address); ?> <br>
                        <?php echo e($header[0]->postal_address); ?></p>
                    <p><?php echo e($header[0]->first_name); ?> <?php echo e($header[0]->middle_name); ?> <?php echo e($header[0]->last_name); ?> <br>
                        <?php echo e($header[0]->mobile); ?> <br>
                        <?php echo e($header[0]->email); ?></p>
                </div>
                <div class="quotation_detail float-right">
                    <p style="text-align: right;"><b>Quotation Number: </b><?php echo e($header[0]->quote_number); ?> <br>
                        Quote Date: <?php echo e($header[0]->quote_date); ?> <br>
                        Expiring Date: <?php echo e($header[0]->expiring_date); ?> <br>
                        Prepared By : <?php echo e($header[0]->prepared_by); ?> <br>
                        Position: <?php echo e($header[0]->position); ?> <br>
                        Phone: <?php echo e($header[0]->phone ?? '-'); ?> <br>
                        Email: <?php echo e($header[0]->prepared_by_email); ?></p>
                </div>
            </div>
            <table class="table  table-condensed my-small-text table-striped table-hover table-bordered table-md">
                <thead style="background-color: #75ee4a !important; ">
                    <th>No</th>
                    <th>Part No</th>
                    <th nowrap>Description</th>

                    <th nowrap>Quantity</th>
                    <th nowrap>Unit Price</th>
                    <th nowrap>Tax</th>
                    <th>Extended Price</th>

                </thead>
                <tbody>
                    <?php $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($loop->iteration); ?></td>
                        <td><?php echo e($detail->part_no_final); ?></td>
                        <?php if($header[0]->quotation_type == 'General'): ?>
                        <td>
                            <?php if($detail->photo_url != ''): ?>
                            <img src="<?php echo e($detail->photo_url); ?>" style="height:120px; width:auto" alt="image">
                            <?php endif; ?>
                            <p><b>Item Name :</b> <?php echo e($detail->item_name); ?></p>
                            <p><b>Description :</b> <?php echo e($detail->description); ?></p>
                        </td>
                        <?php else: ?>
                        <td>
                            <p class="mb-0"><b><?php echo e($detail->sample_type_name); ?></b></p>
                            <p class="mb-0"><b>Description:</b></p>
                            <?php $__currentLoopData = $detail->default; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $da): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span><?php echo e($da); ?>, </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $detail->sub_acc; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span><?php echo e($sb); ?>* <img src="/images/tick.png" height="8" width="8" alt="">, </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $detail->sub_analytes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span><?php echo e($sa); ?>*, </span>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $detail->acc_analytes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class=""><?php echo e($acc); ?> <img src="/images/tick.png" height="8" width="8" alt="">, </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </td>
                        <?php endif; ?>
                        <td class="text-center"><?php echo e($detail->quantity); ?></td>
                        <td style="text-align: right;"><?php echo e(number_format($detail->unit_price,2)); ?></td>
                        <td style="text-align: right;"><?php echo e(number_format($detail->tax,2)); ?></td>
                        <td style="text-align: right;"><?php echo e(number_format($detail->extended_price,2)); ?></td>

                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            <div class="row">
                <div class="col-lg-5 col-sm-6 ml-auto">
                    <table class="table table-clear table-sm">
                        <tbody>
                            <tr>
                                <td style="font-size: 13px; font-weight:600">
                                    Sub Total
                                </td>
                                <td>
                                    <b class="float-right"><?php echo e(number_format($header[0]->sub_total,2)); ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 13px; font-weight:600">
                                    Tax
                                </td>
                                <td>
                                    <b class="float-right">
                                        <?php echo e(number_format($header[0]->tax,2)); ?>

                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 13px; font-weight:600">
                                    Total (<?php echo e($currency->name ?? '-'); ?>)
                                </td>
                                <td>
                                    <b class="float-right">
                                        <?php echo e(number_format($header[0]->total_price,2)); ?>

                                    </b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="terms">
                <h4 style="font-size: 12px; font-weight:800" class="mb-2">Terms of Sale</h4>
                <p><b>Prices: </b><?php echo e($terms_array['prices']); ?> <?php echo e($currency->name ?? '-'); ?> <br>
                    <b>Service Delivery: </b><?php echo e($header[0]->service_delivery); ?> <br>
                    <b>Payments: </b><?php echo e($header[0]->payments); ?> <br>
                    <b>Quote Specification: </b><?php echo e($header[0]->quote_specification); ?> <br>
                    <b>Approved By: </b><?php echo e(getUserById($header[0]->approved_by)->name); ?></p>

                <h5 style="font-size: 12px; font-weight:800" class="mt-2">Additional Information</h5>
                <p><?php echo e($header[0]->additional_info); ?> <br>
                    <span style="font-weight: 510;"><?php echo e($header[0]->payment_info); ?></soan>
                </p>
                <div class="bank text-center" style="font-size: 12px;">
                    <p>
                        Cheques made payable to <b><?php echo e($company->name); ?></b> <br>

                        <b>Bank Details: </b>Bank Name: <b><?php echo e($bankarr['bank_name']); ?></b> Account No: <b><?php echo e($bankarr['account_no']); ?></b> Swift Code: <b><?php echo e($bankarr['swift_code']); ?></b> <br>
                        Bank Code: <b><?php echo e($bankarr['bank_code']); ?></b> Branch Code: <b><?php echo e($bankarr['branch_code']); ?></b> <br>
                        <b>Mobile Remittance: </b>Mpesa Paybill: <b><?php echo e($bankarr['paybill']); ?></b> Account Name: <b><?php echo e($bankarr['account']); ?></b> </p>
                    <span style="font-size: 30px; font-weight:600">-</span> End Of Document <span style="font-size: 30px; font-weight:600">-</span>
                </div>
            </div>
        </div>

    </div>
</main>
<div class="modal fade" id="quotation-upload" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('upload_quotation',['id'=>$header[0]->id])); ?>" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="mdi mdi-alert-decagram"></i> Confirm you want to send quote <?php echo e($header[0]->quote_number); ?> to client.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-content-save"></i> Confirm</button>
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function printquoatation() {
        var mybody = document.getElementById('quotation-document');
        var print_area = window.open();
        print_area.document.write(mybody.innerHTML);
        print_area.document.close();
        print_area.focus();
        print_area.print();
        print_area.close()
        // console.log(mybody);
    }
</script>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="print-quotation" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="mdi mdi-printer"></i> Quote <?php echo e($header[0]->quote_number); ?> PDF Proccessing </h5>
            </div>
            <div class="modal-body text-center">
                <div class="loading">
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    
    $(function() {
        $('#print-quotation').on('show.bs.modal', function(e) {
            var header_id = $(e.relatedTarget).data('header');
            var load_text = $(`<img src="/images/loading.gif" width="70%" height="70%" alt="">
                    <div class="alert alert-primary">
                        <i class="mdi mdi-alert-octagon"></i> Kindly wait as the quotation pdf is being proccessed!
                    </div>`);
            $(this).find('.loading').empty();
            $(this).find('.loading').append(load_text);
            $.ajax({
                url: '/billing/print_quotation/' + header_id,
                success:function(data){
                    console.log(data);
                },
                complete:function(data){
                    $('#print-quotation').find('.loading').empty();
                    var complete_text = $(`
                    <img src="/images/suc.gif" width="50%" height="50%" alt="">
                    <div class="alert alert-success">
                        <i class="mdi mdi-alert-octagon"></i> Quote PDF generated successfully!
                    </div>
                    `);
                    $('#print-quotation').find('.loading').append(complete_text);
                    
                    $('.view-quote').removeClass('hidden');
                },
                error:function(data){
                    console.log(data);
                }
            })
        })
    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/invoice/quotation-doc.blade.php ENDPATH**/ ?>