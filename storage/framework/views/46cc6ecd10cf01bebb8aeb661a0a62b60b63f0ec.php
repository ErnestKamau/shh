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
        padding: 2px 20px;
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

    .select2-selection {
        min-width: 200px !important;
    }
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
    <?php
    $items = array(
        array(
            'link' => route('lab-home'),
            'name' => 'Lab',
            'icon' => null
        ),
        array(
            'link' => route('quotation-index'),
            'name' => 'Billing-Quotation',
            'icon' => null
        ),
        array(
            'link' => route('quotation-index', ['stage' => $header->status]),
            'name' => $header->status,
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $header->quote_number,
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
    <h2 class="p-4">
        <span class="float-left">
            <i class="mdi mdi-file-table"></i>Billing | Quotations
        </span>
       
        <span class="btn btn-default btn-sm float-right ml-2" style="background-color: white;" data-target="#print-quotation" data-header="<?php echo e($header->id); ?>" data-toggle="modal"><i class="mdi mdi-printer"></i> Process PDF</span>
        <?php if($header->is_print == 1): ?>
        <?php 	$path = '/storage'.$header->upload_url;?>
        <a href="<?php echo e($path); ?>" target="_blank" class="btn btn-outline-success btn-sm float-right ml-2"><i class="mdi mdi-eye"></i> View Quote</a>
        <?php endif; ?>
        <?php if($header->status == "Quote In Preparation"): ?>
        <span data-target="#save-draft" data-toggle="modal" class="btn btn-outline-warning btn-sm float-right"><i class="mdi mdi-download-outline"></i> Save As Draft</span>
        <span data-target="#delete-quotation" data-toggle="modal" class="btn btn-outline-danger mr-2 btn-sm float-right"><i class="mdi mdi-delete-empty"></i> Delete Quotation</span>
        <?php if(sizeof($details)>0): ?>
        <span class="btn btn-sm btn-outline-dark mr-2 float-right mt-1" data-target="#request-approval" data-toggle="modal"><i class="mdi mdi-share-circle"></i> Request For Approval</span>
        <!-- <a href="<?php echo e(route('change_quotation_workflow',['id'=>$header->id,'stage'=>'Quote In Approval'])); ?>" class="btn btn-outline-dark btn-sm mr-2 float-right"><i class="mdi mdi-share-circle"></i> Request For Approval</a> -->
        <?php endif; ?>
        <?php endif; ?>
        <?php if($header->status == 'Quote In Approval'): ?>
            <?php if($header->is_approved == 1): ?>

                <span class="badge  badge-pill ml-2 bg-white text-success p-2 <?php echo e($header->approved_by < 0  ? 'hidden' : ''); ?>" style="font-size: 10px;"><i class="mdi mdi-thumb-up"></i> Approved</span>
            <?php else: ?>
                <span class="badge badge-pill bg-white ml-2 text-primary p-2 " style="font-size: 10px;"><i class="mdi mdi-alert-decagram" ></i> Awaiting Approval</span>
                <?php if($header->approved_by == auth()->user()->id): ?>
                <span class="btn btn-sm btn-outline-dark mr-2 float-right" data-target="#approve-quote" data-toggle="modal"><i class="mdi mdi-share-circle"></i> Approve Quotation</span>
                <?php else: ?>
                <span class="badge badge-pill bg-white ml-2 text-danger p-2 " style="font-size: 10px;"><i class="mdi mdi-alert-decagram" ></i> Required Approver- <?php echo e(getUserById($header->approved_by)->name ?? '-'); ?></span>
                <?php endif; ?>
            <?php endif; ?>
        <!-- <a href="<?php echo e(route('change_quotation_workflow',['id'=>$header->id,'stage'=>'Quote Complete'])); ?>" class="btn btn-success btn-sm float-right"><i class="mdi mdi-share-circle"></i> Approve Quotation</a> -->
        <?php endif; ?>
        <div class="nav-item dropdown float-right" style="margin-top: 0px !important;">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" style="color:black;font-size:14px" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-compare-vertical"></i> Move To workflow
            </a>
            <div class="dropdown-menu" style="font-size: 13px;" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="<?php echo e(route('change_quotation_workflow',['id'=>$header->id,'stage'=>'Quote In Preparation'])); ?>"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation In Preparation</a>
                <?php if(in_array($header->status,['Quote In Approval','Quote Complete'])): ?>
                <a class="dropdown-item" href="<?php echo e(route('change_quotation_workflow',['id'=>$header->id,'stage'=>'Quote In Approval'])); ?>"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation In Approval</a>
                <a class="dropdown-item" href="<?php echo e(route('change_quotation_workflow',['id'=>$header->id,'stage'=>'Quote Complete'])); ?>"><i class="mdi mdi-subdirectory-arrow-right"></i> Quotation Complete</a>
                <?php endif; ?>

            </div>
        </div>
    </h2>
    <div class="card mt-5" style="clear: both;">

        <h3 class=" text-center card-header">
            <i class="mdi mdi-check-decagram mb-1" style="position: absolute;left:47.4%"></i><br> Quotation | <?php echo e($header->quote_number); ?>


        </h3>

        <div class="card-body">

            <div class="row no-gutter">

                <div class="col-xl-4 col-sm-4">
                    <form action="<?php echo e(route('add-quotation-header')); ?>" method="POST" class="bg-light ">
                        <?php echo csrf_field(); ?>
                        <div class="card-body p-2">
                            <div class="form-group">
                                <label class="control-label">Quotation Number</label>
                                <input type="text" name="quote_code" readonly value="<?php echo e($header->quote_number); ?>" id="" class="form-control">
                                <input type="hidden" name="quote_id" value="<?php echo e($header->id); ?>">
                            </div>

                            <div class="form-group">
                                <label class="control-label">Client *</label>
                                <select name="client" class="form-control" id="select-client" data-contact="<?php echo e(json_encode($header->crm_customer_contact_id)); ?>" aria-readonly="true" aria-placeholder="Choose Client..." required>
                                    <option value="" disabled selected>Choose Client...</option>
                                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($customer->id); ?>" <?php echo e($customer->id == $header->crm_customer_id ? 'selected':''); ?>><?php echo e($customer->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="form-group contacts" id="choose-client">
                                <label class="control-label">Client Contact *</label>
                                <select name="client_contact" id="select-client-contact" class="form-control" aria-placeholder="Select Client Contact..." required>
                                    <?php
                                    $client_contacts = getCrmCustomerContacts($header->crm_customer_id);
                                    ?>
                                    <?php if(sizeof($client_contacts)>0): ?>
                                    <?php $__currentLoopData = $client_contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($contact->id); ?>" <?php echo e($contact->id == $header->crm_customer_contact_id ? 'selected':''); ?>><?php echo e($contact->first_name); ?> <?php echo e($contact->middle_name); ?> <?php echo e($contact->last_name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </select>


                            </div>

                            <div class="form-group">
                                <label class="control-label">Quotation Type</label>
                                <select name="quotation_type" required id="" class="form-control">
                                    <option value="">Choose Quotation Type</option>
                                    <option value="General" <?php echo e($header->quotation_type == 'General' ? 'selected' : ''); ?>>General Quotation</option>
                                    <option value="Analysis" <?php echo e($header->quotation_type == 'Analysis' ? 'selected' : ''); ?>>Analysis Quotation</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Quotation Date *</label>
                                <input type="date" name="quotation_date" class="form-control" placeholder="Quotation Date..." value="<?php echo e($header->quote_date); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Expiry Date *</label>
                                <input type="date" name="expire_date" class="form-control" placeholder="Expiration Date..." value="<?php echo e($header->expiring_date); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Pricelist Code</label>
                                <input type="text" name="pricelist_code" readonly value="<?php echo e($pricelist->code ?? '-'); ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Prepared By</label>
                                <input type="text" name="prepared_by" readonly value="<?php echo e($header->prepared_by_name); ?>" id="" class="form-control">
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            <button style="width:70%;" class="btn btn-outline-success" type="submit">Save</button>
                        </div>
                    </form>


                </div>
                <div class="col-xl-8 col-sm-8">
                    <form action="<?php echo e(route('add_quotation_detail',['id'=>$header->id])); ?>" method="POST" enctype="multipart/form-data" class="bg-light p-1">
                        <?php echo csrf_field(); ?>
                        <?php if($header->quotation_type == 'General'): ?>
                        <button type="submit" class="btn btn-outline-success btn-sm float-left mr-2">Save <i class="mdi mdi-share-circle"></i></button>
                        <span class="btn btn-outline-info float-right btn-sm mb-2" data-toggle="modal" onclick=" addrowgeneral()"><i class="mdi mdi-plus"></i></span>
                        <div class="table-responsive">

                            <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm" style="min-width: 180%;">
                                <thead class="bg-light">
                                    <th></th>

                                    <th style="min-width: 10%;">Part No</th>
                                    <th nowrap style="min-width: 25%;">Item*</th>
                                    <th nowrap style="min-width: 30%;">Description*</th>
                                    <th nowrap style="min-width: 8%;">Photo</th>
                                    <th nowrap style="min-width: 8%;">Quantity*</th>
                                    <th nowrap style="min-width: 8%;">Unit Price</th>
                                    <th nowrap style="min-width: 11%;">Tax</th>

                                </thead>
                                <tbody id="quotation-detail-row">
                                    <?php $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td style="display: flex;border:0px ">
                                            <span style="font-size:11px; flex:1" data-toggle="modal" data-target="#edit-detail-quotation-<?php echo e($detail->id); ?>" class="btn mdi mdi-pencil " data-toggle="tooltip" title="Edit"></span>
                                            <span style="font-size:12px;flex:1 ;border-bottom:0px" data-toggle="modal" data-target="#delete-detail-<?php echo e($detail->id); ?>" class="btn mdi mdi-delete-empty text-danger" data-toggle="tooltip" title="Delete"></span>


                                        </td>

                                        <td><?php echo e($detail->part_no); ?></td>
                                        <td>
                                            <div class="form-group">

                                                <textarea class=" form-control" value="" rows="1" readonly> <?php echo e($detail->item_name); ?> </textarea>
                                            </div>
                                        </td>
                                        <td nowrap>
                                            <div class="">
                                                <textarea style="min-height: 100px;" class=" form-control" value="" rows="1" readonly> <?php echo e($detail->description); ?> </textarea>

                                            </div>
                                        </td>
                                        <td>

                                            <div class="form-group">
                                                <img src="<?php echo e($detail->photo_url ?? '/images/no-logo.png'); ?>" alt="Item Photo" style="width: auto; height:100px">


                                            </div>

                                        </td>
                                        <td>
                                            <div class="form-group" id="">
                                                <input type="text" class="form-control" value="<?php echo e($detail->quantity); ?>" readonly id="">

                                            </div>
                                        </td>

                                        <td>

                                            <div class="form-group">
                                                <input type="float" class="form-control" value="<?php echo e(number_format($detail->unit_price,2)); ?>" disabled>
                                            </div>

                                        </td>
                                        <td>

                                            <div class="form-group" id="">
                                                <input type="text" value="<?php echo e($detail->tax); ?>" readonly class="form-control" disabled>
                                            </div>



                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <button type="submit" class="btn btn-outline-success btn-sm float-left mr-2 mb-2 mt-2">Save <i class="mdi mdi-share-circle"></i></button>
                        <?php if($header->status == 'Quote In Preparation'): ?>
                        <span class="btn btn-outline-info float-right btn-sm mb-2 mt-2" id="add-row"><i class="mdi mdi-plus"></i></span>
                        <?php endif; ?>
                        <div class="table-responsive ">

                            <table class="table table-condensed table-stripped table-hover table-bordered" style="width: 130%;">
                                <thead class="bg-light">

                                    <th>No</th>
                                    <th nowrap>Sample Type <span class="text-danger">*</span></th>
                                    <th nowrap>Part No <span class="text-danger">*</span></th>
                                    <th nowrap>Description<span class="text-danger">*</span></th>
                                    <th nowrap>Quantiy<span class="text-danger">*</span></th>
                                    <th nowrap>Unit Price <span class="text-danger">*</span></th>
                                    <th nowrap>Tax%</th>



                                </thead>
                                <tbody id="create-detail">
                                    <?php $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td style="display: flex;border:0px ">

                                            <span style="font-size:11px; flex:1" data-sample="<?php echo e($detail->sample_type_name); ?>" data-detail=<?php echo e($detail->id); ?> data-toggle="modal" data-target="#detail-edit-mode" class="btn mdi mdi-pencil " data-toggle="tooltip" title="Edit"></span>

                                            <span style="font-size:12px;flex:1 ;border-bottom:0px" data-toggle="modal" data-target="#delete-detail-<?php echo e($detail->id); ?>" class="btn mdi mdi-delete-empty text-danger" data-toggle="tooltip" title="Delete"></span>


                                        </td>
                                        <td style="min-width: 200px;">

                                            <div class="form-group" id="">
                                                <input type="text" class="form-control" value="<?php echo e($detail->sample_type_name); ?>" disabled id="">

                                            </div>

                                        </td>
                                        <td style="min-width: 300px;">
                                            <p><?php echo e($detail->part_no_final); ?></p>
                                        </td>
                                        <td style="min-width: 450px;">
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

                                        <td>
                                            <div class="form-group">
                                                <input type="number" id="" class="form-control" value="<?php echo e($detail->quantity); ?>" disabled>
                                            </div>
                                        </td>
                                        <td style="min-width: 150px;text-align:right !important">

                                            <div class="form-group">
                                                <input type="float" class="form-control" value="<?php echo e(number_format($detail->unit_price,2)); ?>" disabled>
                                            </div>

                                        </td>
                                        <td style="min-width: 80px;">

                                            <div class="form-group">
                                                <input type="text" value="<?php echo e($detail->tax); ?>" readonly class="form-control" disabled>
                                            </div>


                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>

                        <h5 style="font-size: 11px; margin-top:15px"><b><u>Terms of Sale</u></b></h5>

                        <div class="terms ml-4">
                            <div class="form-group">
                                <label class="control-label">Quote Currency <span class="text-danger">*</span></label>
                                <select name="currency_id" class="form-control" Required>
                                    <option value="">Choose Currency...</option>
                                    <?php $__currentLoopData = getCurrencies(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($currency->id); ?>" <?php echo e($header->currency_id == $currency->id ? 'selected' : ''); ?>><?php echo e($currency->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Service Delivery <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="2" name="service_delivery" placeholder="Service Delivery..." required><?php echo e($header->service_delivery == ''? $terms_array['service_delivery'] : $header->service_delivery); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Payments <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="2" name="payments" placeholder="Payment Information..." required><?php echo e($header->payments == '' ? $terms_array['payments'] : $header->payments); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Quote Specifications <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="2" name="quote_specification" placeholder="Quote Spcification..." required><?php echo e($header->quote_specification == '' ?  $terms_array['quote_specification'] : $header->quote_specification); ?></textarea>
                            </div>

                        </div>
                        <small style="font-size: 11px;"><b><u>Additional Information</u></b></small>

                        <div class="additional-info ml-4">
                            <div class="form-group">
                                <label class="control-label">Additional Information <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="2" name="additional_info" placeholder="Additional Info..." required><?php echo e($header->additional_info == '' ? $terms_array['additional_info'] : $header->additional_info); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Payment Instructions <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="2" name="payment_info" placeholder="Payment Instructions..." required><?php echo e($header->payment_info == '' ? 'YOU MAY SUBMIT YOUR PAYMENT IN ACCORDANCE TO THE BELOW INSTRUCTIONS BANK OR MOBILE REMITTANCE' : $header->payment_info); ?> </textarea>
                            </div>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>



</main>


<link rel="stylesheet" href="/css/quilljs.css" />
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="print-quotation" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="mdi mdi-printer"></i> Quote <?php echo e($header->quote_number); ?> PDF Proccessing </h5>
            </div>
            <div class="modal-body text-center">
                <div class="loading">
                    
                </div>
            </div>
            <div class="modal-footer">
                <a href="/billing-add-quote-detail-index/<?php echo e($header->id); ?>" class="btn  btn-sm btn-outline-danger">Close</a>
                
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="request-approval" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('approve-workflow')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-share-circle"></i> Request For Approval
                    </h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="header_id" value="<?php echo e($header->id); ?>">
                    <input type="hidden" name="stage" value="Quote In Approval">
                    <div class="form-group">
                        <label class="control-label">To Be Approved BY: <span class="text-danger">*</span> </label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Select Approver...</option>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($user->id != $header->prepared_by_id): ?>
                            <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                            <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" class="form-control" name="notification" />
                        <label class="form-check-label">
                            Send Email Notification
                        </label>
                    </div>
                    <br>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
                        <label class="form-check-label">
                            Send Message
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="mdi mdi-content-save"></i> Approve</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="approve-quote" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('approve-workflow')); ?>" method="post">
            <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <input type="hidden" name="header_id" value="<?php echo e($header->id); ?>">
                    <input type="hidden" name="stage" value="Quote Complete">
                    <?php if($header->approved_by == Auth::user()->id): ?>
                    <div class="alert alert-success">
                       <i class="mdi mdi-alert-decagram"></i> Notify <?php echo e(getUserById($header->prepared_by_id)->name); ?> that you have approved Quote <?php echo e($header->quote_number); ?> by ?
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" class="form-control" name="notification" />
                        <label class="form-check-label">
                            Send Email Notification
                        </label>
                    </div>
                    <br>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" class="form-control" name="send_message" />
                        <label class="form-check-label">
                            Send Message
                        </label>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="mdi mdi-alert-decagram"></i> You are not allowed to approve this quotation.
                    </div>
                    <?php endif; ?>
                    
                </div>
                <div class="modal-footer">
                    <?php if($header->approved_by == Auth::user()->id): ?>
                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="mdi mdi-content-save"></i> Approve</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="delete-quotation" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('delete_quotation',['id'=>$header->id])); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h4 class="modal-title">

                        <i class="mdi mdi-delete-empty text-danger"></i> Delete Item <?php echo e($header->quote_number); ?>


                    </h4>
                </div>
                <div class="modal-body text-center">

                    <input type="hidden" name="header_id" value="<?php echo e($header->id); ?>" class="form-control">
                    Are you sure you want to delete Quotation <?php echo e($header->quote_number); ?>?
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="save-draft" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('save_draft',['id'=>$header->id])); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-download-outline text-warning"></i> Save As Draft Quotation <?php echo e($header->quote_number); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="card p-3" style="background-color: turquoise;">
                        Ensure you have saved all the details first before saving as draft.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i>Save</button>
                    <button type="button" class="btn btn-outline-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="detail-edit-mode" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo e(route('edit_quotation_detail')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-content-save"></i>Save</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success text-center">
                        kindly wait for the page to load!
                    </div>
                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-outline-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="edit-detail-analytes" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-light">
            <form action="">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-pencil text-primary"></i> Edit Quotation Detail Description

                    </h5>
                    <span class="btn btn-success float-right btn-sm" id="save-edit"><i class="mdi mdi-content-save"></i>Save</span>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table style="width: 100%;" class="table-condensed table-hover table-stripped table-sm table-bordered">
                            <thead class="bg-light">
                                <th>No <input type="checkbox" class="float-right" id="edit-analyte-all"></th>
                                <th>Analyte Name</th>
                                <th>Accredited <input type="checkbox" id="edit-accreditted" class="float-right"></th>
                                <th>Sub-Contracted <input type="checkbox" id="edit-sub" class="float-right"></th>
                            </thead>
                            <tbody id="edit-description"></tbody>
                        </table>
                    </div>

                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-outline-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="delete-detail-<?php echo e($detail->id); ?>" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('delete_quotation_detail',['id'=>$detail->id])); ?>" method="get">
                <div class="modal-header">
                    <h4 class="modal-title">

                        <i class="mdi mdi-delete-empty text-danger"></i> Delete Item <?php echo e($loop->iteration); ?>

                    </h4>
                </div>
                <div class="modal-body text-center">
                    <?php if($header->quotation_type == 'General'): ?>
                    <div class="alert alert-danger">
                        Are you sure you want to delete item <?php echo e($detail->item_name); ?>

                    </div>
                    <?php else: ?>


                    Are you sure you want to delete <?php echo e($detail->sample_type_name); ?> ?
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-detail-quotation-<?php echo e($detail->id); ?>" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo e(route('edit_quotation_detail')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-pencil"></i> Edit Quote Detail
                    </h5>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <input type="hidden" name="detail_id" value="<?php echo e($detail->id); ?>">
                        <label class="control-label">Part no</label>
                        <input type="text" id="part_number" name="part_no" value="<?php echo e($detail->part_no); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Item Name</label>
                        <textarea class="form-control" name="item" required rows="1"><?php echo e($detail->item_name); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <textarea class="form-control" id="quotation-description" name="description" required rows="2"><?php echo e($detail->description); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Quantity</label>
                        <input type="number" name="quantity" id="" class="form-control" value="<?php echo e($detail->quantity); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Unit Price</label>
                        <input type="float" name="unit_price" class="form-control" value="<?php echo e($detail->unit_price); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Tax</label>
                        <input type="text" name="tax" value="<?php echo e($detail->tax); ?>" class="form-control" required>
                    </div>
                    <?php if($header->quotation_type == 'General'): ?>
                    <div class="form-group">
                        <label class="control-label">Photo</label>
                        <div class="row">
                            <div class="col-md-2 col-lg-2 col-sm-2">
                                <img src="<?php echo e($detail->photo_url); ?>" style="height:100px;width:auto" alt="">
                            </div>
                            <div class="col-md-10 col-lg-10 col-sm-10">
                                <input type="file" name="photo" id="" class="form-control">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-success"><i class="mdi mdi-content-save"></i> Yes</button>
                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="quote-description-analytes" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-google-circles-extended text-success"></i> Quotation Description
                    </h5>
                    <span type="submit" id="save-analytes" class="btn btn-outline-success btn-sm float-right"><i class="mdi mdi-content-save"></i> Save</span>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table-condensed table-hover table-stripped table-sm table-bordered" style="width: 100%;">
                            <thead class="bg-light">
                                <th>No <input type="checkbox" id="select-analyte-all" class="float-right"></th>
                                <th>Analyte </th>
                                <th>Accreditted <input type="checkbox" id="select-accredited-all" class="float-right"></th>
                                <th>Subcontracted <input type="checkbox" id="select-sub-all" class="float-right"></th>
                            </thead>
                            <tbody id="analysis-analytes-holder">

                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    var analysis = [];
    $(function() {

        $('#print-quotation').on('show.bs.modal', function(e) {
            var header_id = $(e.relatedTarget).data('header');
            var load_text = $(`
                    <img src="/images/load.gif" width="70%" height="70%" alt="">
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

        $('#select-client').on('change', function() {
            var client = $(this).val();
            var contact = $(this).data('contact');
            console.log();
            $.ajax({
                url: '/fetch-customer-contacts/' + client,
                beforeSend: function() {
                    $('#select-client-contact').empty();
                },
                success: function(data) {
                    // console.log(data);
                    $.each(data, function(j, s) {
                        console.log(s);
                        var $option = $(`
                            <option value = "${s.id}" ${s.id === contact ? 'selected' :''}>${s.first_name} ${s.middle_name ?? ''} ${s.last_name ?? ''}</option>
                        `);
                        $('#select-client-contact').append($option);
                    })
                },
                error: function(data) {
                    console.log(data);
                }
            })
        });

        $('#add-row').on('click', function() {

            // console.log(len2);

            var roeNo = $('#create-detail').find('tr').length + 1;

            var $row = $(`
                <tr id="detail-row-${roeNo}">
                                    <td class="text-center ">${roeNo}
                                    <span class="mdi mdi-minus-circle-outline text-danger btn " id="delete-row"></span>
                                    </td> 
                                    <td>      
                                        <div class="form-group">
                                            <select name="sample_type[]" class="form-control" id ="select-sample-type">
                                                <option value ="">Choose Sample Type</option>
                                                <?php $__currentLoopData = $sample_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($type->id); ?>"><?php echo e($type->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select> 
                                        </div> 
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <select name="part_number[]" class="form-control select2 select-part" multiple required>
                                                
                                            </select>
                                            <input type="hidden" class="form-control" value="" name ="part_number_final[]" id="select-part-final">
                                        </div>
                                    </td>
                                    <td class="text-center" id="quote-description" >
                                        <span>-</span>
                                    </td>
                                    
                                    <td>
                                        <div class="form-group">
                                            <input type="number" name="quantity[]" id="" class="form-control" value="" required>
                                        </div>
                                    </td>
                                    <td>
                                        
                                        <div class="form-group">
                                            <input type="float" name="unit_price[]" id="unit-price" class="form-control" value="" required>
                                        </div>
                                        
                                    </td>
                                    <td >
                                        
                                        <div class="form-group">
                                        <input type="text" name="tax[]" value="" class="form-control" required>
                                        
                                        </div>
                                        
                                    </td>
                                    </tr>
                `).clone();
            $row.find('#delete-row').on('click', function() {
                if (confirm("Are you sure you want to delete this row?")) {
                    var row = $(this).parents('tr');
                    $(row).remove();
                }
            });


            $row.find('#select-sample-type').on('change', function(analysis) {
                $(this).addClass('choose');
                var sample_type = $(this).val();
                if (sample_type != '') {

                    $.ajax({
                        url: '/fetch-sample-type/' + sample_type,
                        beforeSend: function() {
                            $('#part-number-test').empty();
                            analysis = [];
                        },
                        success: function(data) {
                            analysis = data['analysis']

                            quotationDetailRow(data['analysis'], sample_type, roeNo, data['sample_type']);
                            $('#create-detail').find('tr#detail-row-' + roeNo).find('select.select-part').select2();
                        },
                        error: function(data) {
                            console.log(data);
                        }
                    })
                }


            });
            $row.find('select.select-part').on('change', function() {
                var value = $(this).val();
                $row.find('input#select-part-final').val(value.toString());
            })

            $('#create-detail').append($row);

        });

        var quotationDetailRow = function(data, sample_type, rowNo, sample_code) {
            var ids = [];
            $.each(data, function(i, e) {
                ids.push(e.id);

                $('#create-detail').find('tr#detail-row-' + rowNo).find('select.select-part').append(`<option value ="${e.id}">${e.name}</option>`);
            })
            $('#create-detail').find('tr#detail-row-' + rowNo).find('select.select-part').val(ids);
            $('#create-detail').find('tr#detail-row-' + rowNo).find('input#select-part-final').val(ids.toString());

            $('#create-detail').find('tr#detail-row-' + rowNo).find('select.select-part').select2();

            var text = $(`
                <span data-target="#quote-description-analytes" data-row = ${rowNo} data-samplecode = "${sample_code}"  data-toggle="modal" data-sampletype=${sample_type} class="btn btn-outline-success btn-sm mdi mdi-eye"></span>
            `);
            $('#create-detail').find('tr#detail-row-' + rowNo).find('#quote-description').empty();
            $('#create-detail').find('tr#detail-row-' + rowNo).find('#quote-description').append(text);


            console.log(data);
        }
        $('#detail-edit-mode').on('show.bs.modal', function(e) {
            var detail_data = $(e.relatedTarget).data('detail')
            var sample_name = $(e.relatedTarget).data('sample');
            $(this).find('.modal-title').empty();
            var header = $(`<span><i class="mdi mdi-pencil text-primary"></i> Edit ${sample_name} Details </span>`)
            $(this).find('.modal-title').append(header);
            if (detail_data != '') {
                $.ajax({
                    url: '/fetch-detail-data/' + detail_data,
                    beforeSend: function() {
                        $(this).find('h5.modal-title').empty();
                        $('#detail-edit-mode').find('.modal-body').empty();
                    },
                    success: function(data) {
                        // console.log(data['analysis'])
                        var row = detail_edit_body(data['detail'], data['analysis_data'])
                        $('#detail-edit-mode').find('.modal-body').append(row);
                    },
                    error: function(data) {
                        console.log(data);
                    }
                })
            }
        })
        var detail_edit_body = function(data, analysis) {
            var editRow = $(`
            <div class="form-group">
                <label class="cotrol-label">Sample Type</label>
                <span class="form-control">${data.sample_type_name}</span>
                <input type="hidden" id="sample-type-id" value="${data.sample_type}">
                <input type="hidden" name="detail_id" id="detail-id" value="${data.id}">
            </div>
            <div class="form-group">
                <label class="control-label">Part No</label>
                <select class="form-group select2" name="part_no[]" multiple id="edit-part-no">
                   
                    
                </select>
            </div>
            <div class="form-group">
                <label class="control-label">Quantity</label>
                <input type="text" name="quantity" value="${data.quantity}" class="form-control">
            </div>
            <div class="form-group">
                <label class="control-label">Unit Price</label>
                <input type="text" name="unit_price" value="${data.unit_price}" class="form-control">
            </div>
            <div class="form-group">
                <label class="control-label">Tax</label>
                <input type="text" name="tax" value="${data.tax}" class="form-control">
            </div>
            <div class="form-group">
                <label class="control-label">Description</label>
                <span data-toggle="modal" data-target="#edit-detail-analytes" class="float-right" ><i class="mdi mdi-pencil"></i></span>
                <div class="description-analytes p-2" style="border:1px solid grey;">
                </div>
            </div>
            
            `).clone();
            $(editRow).find('#edit-part-no').select2();
            $.each(analysis, function(j, s) {
                // console.log(s);
                var option_text = $(`<option value="${s.id}">${s.name}</option>`)
                $(editRow).find('#edit-part-no').append(option_text);
            })

            $(editRow).find('#edit-part-no').val(data.part_no_value)

            $.each(data.sub_analytes, function(i, e) {
                var text = $(`<span>${e}*, </span>`);
                $(editRow).find('.description-analytes').append(text);
            });
            $.each(data.acc_analytes, function(i, e) {
                var text = $(`<span>${e} <img src="/images/tick.png" height="8" width="8">, </span>`);
                $(editRow).find('.description-analytes').append(text);
            });
            $.each(data.sub_acc, function(i, e) {
                var text = $(`<span>${e}* <img src="/images/tick.png" height="8" width="8">, </span>`);
                $(editRow).find('.description-analytes').append(text);
            });
            $.each(data.default, function(i, e) {
                var text = $(`<span>${e} , </span>`);
                $(editRow).find('.description-analytes').append(text);
            });


            return editRow;
        }
        $('#edit-detail-analytes').on('show.bs.modal', function(e) {
            var sample_type = $('#detail-edit-mode').find('#sample-type-id').val();
            var analysis_s = $('#detail-edit-mode').find('#edit-part-no').val();
            var detail = $('#detail-edit-mode').find('#detail-id').val();

            $.ajax({
                url: '/fetch-sample-analytes/' + sample_type + '/' + analysis_s.toString() + '/' + detail,
                beforeSend: function() {
                    $('.description-analytes').empty();
                    $('#edit-description').empty();
                },
                success: function(data) {
                    console.log(data);
                    $.each(data, function(i, e) {
                        var analysis_text = $(`<tr>
                                <td colspan="4"><b>${i}</b></td>
                            </tr>`)
                        $('#edit-description').append(analysis_text);
                        $.each(e, function(j, s) {
                            console.log(s);
                            var rows = quoteAnalytesRow(s);
                            $('#edit-description').append(rows);
                        })
                    });
                }
            })
            $(this).find('#save-edit').on('click', function() {
                var analytes_acc = [];
                var sub_analytes = [];
                var acc_sub = [];
                var default_a = [];
                $('#edit-description').find('[name="selected_analyte[]"]').each(function(e) {
                    var tr = $(this).parents('tr');
                    if ($(this).prop('checked')) {
                        var analyte_name = tr.find('#analyte-name').val();
                        var analyte_id = tr.find('#selected-analytes').val();
                        var analyte_text = '';
                        if (tr.find('#accreditted').prop('checked') && !tr.find('#sub_contracted').prop('checked')) {
                            analyte_text = $(`<span>${analyte_name} <img src="/images/tick.png" alt="tick" height="8" width="8">, </span>`)

                            analytes_acc.push(analyte_id);
                        }
                        if (tr.find('#sub_contracted').prop('checked') && !tr.find('#accreditted').prop('checked')) {
                            analyte_text = $(`<span>${analyte_name}*, </span>`)
                            sub_analytes.push(analyte_id);
                        }
                        if (tr.find('#accreditted').prop('checked') && tr.find('#sub_contracted').prop('checked')) {
                            analyte_text = $(`<span>${analyte_name} * <img src="/images/tick.png" alt="tick" height="8" width="8">,</span> `);
                            acc_sub.push(analyte_id);
                        }
                        if (!tr.find('#accreditted').prop('checked') && !tr.find('#sub_contracted').prop('checked')) {
                            analyte_text = $(`<span>${analyte_name}, </span>`);
                            default_a.push(analyte_id);
                        }

                        $('.description-analytes').append(analyte_text);
                    }
                });
                var test = sub_analytes.toString();
                var accredited_input = $(`<input type="hidden" name="accreditted_analytes" value="${analytes_acc.toString()}">`);
                var sub_input = $(`<input type="hidden" name="sub_analytes" value="${test}">`);
                var sub_acc_input = $(`<input type="hidden" name="sub_acc" value="${acc_sub.toString()}">`);
                var default_input = $(`<input type="hidden" name="default_analytes" value="${default_a.toString()}">`);
                $('.description-analytes').append(accredited_input);
                $('.description-analytes').append(sub_input);
                $('.description-analytes').append(sub_acc_input);
                $('.description-analytes').append(default_input);

                $('#edit-detail-analytes').modal('toggle');
            })
        })
        $('#quote-description-analytes').on('show.bs.modal', function(e) {

            var sample_code = $(e.relatedTarget).data('sampletype');
            var type_name = $(e.relatedTarget).data('samplecode')
            var row_no = $(e.relatedTarget).data('row');
            var part_no_analysis = $('#create-detail').find('tr#detail-row-' + row_no).find('#select-part-final').val();
            console.log(part_no_analysis);
            if (sample_code != '') {

                $.ajax({
                    url: '/fetch-sample-analytes/' + sample_code + '/' + part_no_analysis,
                    beforeSend: function() {
                        $('#analysis-analytes-holder').empty();
                    },
                    success: function(data) {
                        $.each(data, function(i, e) {
                            var analysis_text = $(`<tr>
                                <td colspan="4"><b>${i}</b></td>
                            </tr>`)
                            $('#analysis-analytes-holder').append(analysis_text);
                            $.each(e, function(j, s) {
                                // console.log(s);
                                var rows = quoteAnalytesRow(s);
                                $('#analysis-analytes-holder').append(rows);
                            })
                        });
                    },
                    error: function(data) {
                        console.log(data);
                    }
                })
            }

            $(this).find('#save-analytes').on('click', function() {
                // console.log(row_no)
                var loop = 1;
                $('#create-detail').find('tr#detail-row-' + row_no).find('#quote-description').empty();
                $('#create-detail').find('tr#detail-row-' + row_no).find('#quote-description').removeClass('text-center');
                var header = $(`
                                <p class="mb-0"><b>${type_name}</b>
                                <span data-target="#quote-description-analytes" data-row = ${row_no} data-samplecode = "${type_name}"  data-toggle="modal" data-sampletype=${sample_code} class="btn btn-outline-success btn-sm mdi mdi-pencil float-right"></span>
                                </p>
                                <p class="mb-0"><b>Description:</b></p>
                            `);
                $('#create-detail').find('tr#detail-row-' + row_no).find('#quote-description').append(header);
                var analytes_accreditted = [];
                var analyte_sub = [];
                var sub_acc = [];
                var default_analytes = [];
                $('[name="selected_analyte[]"]').each(function(e) {
                    // console.log(this);
                    var parent_tr = $(this).parents('tr');

                    if ($(this).prop('checked')) {
                        var analyte_name = parent_tr.find('#analyte-name').val();
                        var analyte_id = parent_tr.find('#selected-analytes').val();
                        var analyte_text = '';
                        if (parent_tr.find('#accreditted').prop('checked') && !parent_tr.find('#sub_contracted').prop('checked')) {
                            analyte_text = $(`<span>${analyte_name} <img src="/images/tick.png" alt="tick" height="8" width="8">, </span>`)

                            analytes_accreditted.push(analyte_id);
                        }
                        if (parent_tr.find('#sub_contracted').prop('checked') && !parent_tr.find('#accreditted').prop('checked')) {
                            analyte_text = $(`<span>${analyte_name}*, </span>`)
                            analyte_sub.push(analyte_id);
                        }
                        if (parent_tr.find('#accreditted').prop('checked') && parent_tr.find('#sub_contracted').prop('checked')) {
                            analyte_text = $(`<span>${analyte_name} * <img src="/images/tick.png" alt="tick" height="8" width="8">,</span> `);
                            sub_acc.push(analyte_id);
                        }
                        if (!parent_tr.find('#accreditted').prop('checked') && !parent_tr.find('#sub_contracted').prop('checked')) {
                            analyte_text = $(`<span>${analyte_name}, </span>`);
                            default_analytes.push(analyte_id);
                        }


                        $('#create-detail').find('tr#detail-row-' + row_no).find('#quote-description').append(analyte_text);
                    }
                    ++loop;

                })
                var test = analyte_sub.toString();
                var accredited_input = $(`<input type="hidden" name="accreditted_analytes[]" value="${analytes_accreditted.toString()}">`);
                var sub_input = $(`<input type="hidden" name="sub_analytes[]" value="${test}">`);
                var sub_acc_input = $(`<input type="hidden" name="sub_acc[]" value="${sub_acc.toString()}">`);
                var default_a = $(`<input type="hidden" name="default_analytes[]" value="${default_analytes.toString()}">`);
                $('#create-detail').find('tr#detail-row-' + row_no).find('#quote-description').append(accredited_input);
                $('#create-detail').find('tr#detail-row-' + row_no).find('#quote-description').append(sub_input);
                $('#create-detail').find('tr#detail-row-' + row_no).find('#quote-description').append(sub_acc_input);
                $('#create-detail').find('tr#detail-row-' + row_no).find('#quote-description').append(default_a);
                row_no = '';
                $('#quote-description-analytes').modal('toggle');
            });

        });
        var quoteAnalytesRow = function(data) {
            var check_acc = 'checked';
            var check_sub = '';
            var selected = 'checked';
            if (data.non_accredited == 0) {
                check_acc = '';
            }
            if (data.acc == 1 && data.present == 1) {
                check_acc = '';
            }
            if (data.sub == 1) {
                check_sub = 'checked'
            }

            if (data.both == 1) {
                check_acc = 'checked';
                check_sub = 'checked';
            }
            if (data.default == 1 && data.present == 1) {
                check_acc = '';
                check_sub = '';
            }
            if (data.selected == 0) {
                selected = ''
            }

            var analyte_row = $(`
                <tr>
                    <td><input type="checkbox" name="selected_analyte[]" id="selected-analytes" value="${data.id}" ${selected}></td>
                    <td>
                        <input type="text" name="analyte_name[]" id="analyte-name" class="border-0" readonly="true" value="${data.analyte_name}" >
                        <input type="hidden" name="analyte_id[]" id="analyte-id" class="border-0" readonly="true" value="${data.analyte_id}" >
                    </td> 
                    <td><input type="checkbox" name="accreditted" id="accreditted" ${check_acc}></td>
                    <td><input type="checkbox" name="sub_contracted" id="sub_contracted" ${check_sub}></td>    
                </tr>
            `);
            var $row = analyte_row.clone();

            return $row;

        }
        $('#select-analyte-all').on('change',function(){
            if($('#select-analyte-all').is(':checked')){
                $.each( $('#quote-description-analytes').find('[id=selected-analytes]'),function(j,s){
                    $(s).prop('checked',true);
                });               
            }else{
                $.each( $('#quote-description-analytes').find('[id=selected-analytes]'),function(j,s){
                    $(s).prop('checked',false);
                }); 
            }
        });
        $('#select-accredited-all').on('change',function(){
            if($('#select-accredited-all').is(':checked')){
                $.each( $('#quote-description-analytes').find('[id=accreditted]'),function(j,s){
                    $(s).prop('checked',true);
                }); 
            }else{
                $.each( $('#quote-description-analytes').find('[id=accreditted]'),function(j,s){
                    $(s).prop('checked',false);
                }); 
            }
        })
        $('#select-sub-all').on('change',function(){
            if($('#select-sub-all').is(':checked')){
                $.each( $('#quote-description-analytes').find('[id=sub_contracted]'),function(j,s){
                    $(s).prop('checked',true);
                }); 
            }else{
                $.each( $('#quote-description-analytes').find('[id=sub_contracted]'),function(j,s){
                    $(s).prop('checked',false);
                }); 
            }
        });
        $('#edit-sub').on('change',function(){
            if($('#edit-sub').is(':checked')){
                $.each( $('#edit-detail-analytes').find('[id=sub_contracted]'),function(j,s){
                    $(s).prop('checked',true);
                }); 
            }else{
                $.each( $('#edit-detail-analytes').find('[id=sub_contracted]'),function(j,s){
                    $(s).prop('checked',false);
                }); 
            }
        });
        $('#edit-accreditted').on('change',function(){
            if($('#edit-accreditted').is(':checked')){
                $.each( $('#edit-detail-analytes').find('[id=accreditted]'),function(j,s){
                    $(s).prop('checked',true);
                }); 
            }else{
                $.each( $('#edit-detail-analytes').find('[id=accreditted]'),function(j,s){
                    $(s).prop('checked',false);
                }); 
            }
        })
        $('#edit-analyte-all').on('change',function(){
            if($('#edit-analyte-all').is(':checked')){
                $.each( $('#edit-detail-analytes').find('[id=selected-analytes]'),function(j,s){
                    $(s).prop('checked',true);
                });               
            }else{
                $.each( $('#edit-detail-analytes').find('[id=selected-analytes]'),function(j,s){
                    $(s).prop('checked',false);
                }); 
            }
        });
        

    });

    function getCustomer() {
        var customer = document.getElementsByName('client')[0].value;

        var text = document.getElementById(customer);
        var text2 = document.getElementsByClassName('contacts')
        for (i = 0; i < text2.length; i++) {
            text2[i].style.display = 'none';
        }

        text.style.display = "block";
    }


    function getPricelist(index) {

        var count = index - 1;
        var analyte = document.getElementsByName('analyte[]')[count].value;
        var my = JSON.parse(analyte);
        var sample_type = document.getElementsByName('sample_type[]')[count].value = my.sample_type_name;
        var tax = document.getElementsByName('tax[]')[count].value = my.tax;
        var unit_price = document.getElementsByName('unit_price[]')[count].value = my.selling_price;
        var analyte_id = document.getElementsByName('analysis_id[]')[count].value = my.analysis_id;




    }

    function getCustomer() {
        var customer = document.getElementsByName('client')[0].value;

        var text = document.getElementById(customer);
        var text2 = document.getElementsByClassName('contacts')
        for (i = 0; i < text2.length; i++) {

            $(text2[i]).addClass('hidden');
        }


        $(text).removeClass('hidden');
    }

    function deleterow(index) {
        var detail = '.detail-' + index;
        var row = $(detail).parent('tr');

        if (confirm("Are you sure you want to delete this row?")) {
            $(row).remove();
        }
    }

    function getPricelistedit(indexed) {
        var counted = indexed - 1;
        var analyte = document.getElementsByName('analyte_edit[]')[counted].value;
        console.log(analyte);
        var my = JSON.parse(analyte);
        var sample_type = document.getElementsByName('sample_type_edit[]')[counted].value = my.sample_type_name;
        var tax = document.getElementsByName('tax_edit[]')[counted].value = my.tax;
        var unit_price = document.getElementsByName('unit_price_edit[]')[counted].value = my.selling_price;
        var analyte_id = document.getElementsByName('analysis_id_edit[]')[counted].value = my.analysis_id;

    }

    function addrow() {


    }

    function addrowgeneral() {

        var len = $('#part_number').length;

        var current = len + 1;
        // console.log(len2);
        var $row = $(`
        <tr>
                               <td class="text-center detail-${current}">
                               <i class="mdi mdi-minus-circle-outline text-danger btn " onclick="deleterow(${current})"></i>
                               </td> 
                               
                               <td>
                                   <div class="form-group">
                                       <input type="text" id="part_number" name="part_no[]" value="" class="form-control">
                                   </div>
                               </td>
                               <td >

                               <div class="form-group">
                                       
                                       <textarea  class="form-control"  name="item[]" required rows="1" ></textarea>  
                                   </div>
                                  
                               </td>
                               <td >
                                    
                                    <textarea  class="form-control" id="quotation-description"  name="description[]" required rows="2" ></textarea>  
                                    
                                </td>
                                <td nowrap>

                                    <div class="form-group id="">
                                        <input type="file" name="photo[]" value="" class="form-control"  id="">
                                    
                                    </div>
                                    
                                </td>
                               
                               <td>
                                   <div class="form-group">
                                       <input type="number" name="quantity[]" id="" class="form-control" value="" required>
                                   </div>
                               </td>
                               <td>
                                   
                                   <div class="form-group">
                                    <input type="float" name="unit_price[]" class="form-control" value="" required>
                                </div>
                                   
                               </td>
                               <td >
                                  
                                  <div class="form-group">
                                  <input type="text" name="tax[]" value="" class="form-control" required>
                                 
                                  
                                </div>
                                  
                               </td>
                            </tr>
        `).clone();
        tinymce.init({
            selector: $row.find('#quotation-description')
        });
        $('tbody').append($row);
    }
</script>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/invoice/quotation-show.blade.php ENDPATH**/ ?>