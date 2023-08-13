<!DOCTYPE html>
<html lang="en">


<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Document</title>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" /> -->

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg==" crossorigin="anonymous"></script>
</head>

<style>
    @page  {
        margin : 10px;
        /* margin-top: 230px; */
    }

    
    footer {
        position: fixed;
        bottom: 140;
        left: 0;
        right: 0;

        z-index: 1000;
    }

    .parameter {
        border: solid 1 rgba(0, 0, 0, 0.35) !important;
        padding: 0 !important;
        padding-left: 2px;
        font-size: 10px !important;
        
    }

    

    .sample-holder {
        position: relative;

    }
</style>
<footer>
        <table class="container" style="margin-top: 1px !important;">
            <tr>
                <td style="font-size: 8px !important;">
                    <img src="<?php echo e($batch_result['approve_user_sig']); ?>" height="80px" width="100px" alt="">   <br>
                    <b><?php echo e(strtoupper($batch_result['approve_user_name'])); ?> </b> <br>
                     <b><u><?php echo e(strtoupper($batch_result['approve_user_position'])); ?></u></b>
                </td>
                <td style="font-size: 8px !important; text-align:right; margin-right:30px !important">
                    <img src="<?php echo e($batch_result['verify_user_sig']); ?>" height="80px" width="100px" alt="">   <br>
                    <b><?php echo e(strtoupper($batch_result['verify_user_name'])); ?></b> <br>
                     <b> <u><?php echo e(strtoupper($batch_result['verify_user_position'])); ?></u></b> 
                    
                </td>
            </tr>
        </table>
        <br>
        <div class="clear:both">
            <table style="width:100%">
                <tr>

                    <td style="font-size: 10px !important" colspan="3">
                        REVISION [01] ISSUE DATE: <?php echo e(date('Y-m-d')); ?> | Authorized by: <?php echo e(strtoupper($batch_result['approve_user_name'])); ?> | Approved by: <?php echo e(strtoupper($batch_result['verify_user_name'])); ?> <br>
                        
                    </td>
                    <td colspan="3" style="font-size: 9px;text-align:right">
                        <img src="data:image/png;base64, <?php echo $qrcode; ?>"> <br>
                        <span style="clear: both;"><?php echo e($batch_result['sample_type_name']); ?></span>  
                    </td>

                </tr>

            </table>
        </div>
    </footer>

<body>
    <main  style="margin-bottom:150px !important">
        
            
            <table class="table table-condensed table-sm table-bordered" style="font-size: 8px;">
                <thead>
                    <tr style="border: solid 1px black !important;">
                        <th style="font-size: 10px !important;border: solid 0 transparent !important" colspan="6">
                            <table style="width: 100%;border:0px; padding-bottom:2px !important; border-bottom: solid 2px #0000ff !important">
                                <tr>
                                    <td style="border:solid 0 transparent !important;" colspan="3">
                                        <img src="<?php echo e($path); ?>" style="height:60px;" alt="logo"> <br> <br> <br>
                                        <span style="color:green !important;font-size:14px !important" ><b>SYNGENTA EAST AFRICA PLANT PATHOLOGY LAB</b></span>
                                        
                                    </td>
                                   
                                    
                                    <td style="border: solid 0 transparent !important;text-align:right;font-size:10px !important; " colspan="3">
                                        <?php echo e($company->name); ?> <br>
                                        <?php echo e($company->address); ?> <br>
                                        <?php echo e($company->location); ?> <br>
                                        <?php echo e($company->street); ?> <br>
                                        Email: <?php echo e($company->email); ?> <br>
                                        Website: <?php echo e($company->website); ?> <br>
                                        Tel: <?php echo e($company->telephone); ?> Cell: <?php echo e($company->cell_phone); ?> <br>
                                    </td>
                                </tr>

                            </table>

                        </th>
                    </tr>

                    <tr>
                        <th style="border: solid 0 transparent !important;border-bottom: solid 1 rgba(0, 0, 0,0.35) !important" colspan="6">
                            <table style="width: 100%; border:0px;padding:0 !important">
                                <tr>
                                    <td colspan="4" style=" border: 0 transparent !important; font-size:11px !important;"><b>Request Week <span style="color:red !important"><?php echo e($batch->year); ?><?php echo e($batch->week); ?></span></b></td>
                                </tr>
                                <tr>
                                    <td style="border: 0 transparent !important; font-size:11px !important;"colspan="3">
                                        <b>Working Instructions: <span><?php echo e($batch->description); ?></span> </b> <br>
                                        <b>Sampler Address: <span><?php echo e($batch->importer_address); ?></span></b> <br>
                                        <b>Sample Type: <span><?php echo e($batch_result['sample_type_name']); ?></span></b> <br>
                                        <b>Customer: </b> <span><?php echo e($customer->name); ?></span><br>
                                        <b>Customer Address: <span><?php echo e($customer->postal_address); ?></span> </b>
                                    </td>
                                   



                                    <td style="border: 0 transparent !important; font-size:11px !important; text-align:right;padding:0px" colspan="3">
                                        <b>Date of Shipment: <span><?php echo e($batch->date_collected); ?></span></b><br>
                                        <b>Date of Arrival: <span><?php echo e($batch->receipt_date); ?></span></b><br>
                                        <b>Date of Analysis: <span><?php echo e($batch->processing_date); ?></span></b> <br>
                                        <b>Date of Report Approval: <span><?php echo e($batch->approval_date ?? '-'); ?></span></b> <br>
                                        <b>Batch Code: <span><?php echo e($batch->batch_code); ?></span></b>
                                    </td>
                                    
                                </tr>
                            </table>
                        </th>
                    </tr>
                    <tr>
                       
                        <th class="parameter " >Sample No</th>
                        <th class="parameter " >No of Samples</th>
                        <th class="parameter " >Description</th>
                        <th class="parameter " >Test Required</th>
                        <th class="parameter " >Results</th>
                        <th class="parameter " >Remark</th>
                    </tr>
                </thead>
                <tbody style="font-size: 9px !important;">
                   <?php $__currentLoopData = $batch_view; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $view): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                   
                   <tr>
                       <td style="width: 10% !important;"><?php echo e($view->sample_no); ?></td>
                       <td style="width: 10% !important;"><?php echo e($view->no_of_samples); ?></td>
                       <td style="width: 15% !important;"><?php echo e($view->analysis_type_name); ?>, <?php echo e($view->sample_point_name); ?></td>
                       <td style="width: 15% !important;"><?php echo e($view->standard_tests); ?></td>
                       <td style="width: 30% !important;">
                            <?php $__currentLoopData = $view->results_arr; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $re = explode('_',$r);?>
                               
                                    <span style="border: 3px;margin-bottom:5px;font-size:10px !important;" class="badge badge-pill <?php echo e($re[1] ?? 'bg-white'); ?>"><?php echo e($re[0] ?? ''); ?></span>
                                    <?php if($loop->iteration % 3 == 0): ?>
                                        <br>
                                    <?php endif; ?>
                                    
                                
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                          
                       </td>
                       <td style="width: 10% !important;"><?php echo e(strip_tags($view->header_body)); ?></td>
                   </tr>
                   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
    </main>
    
    <script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $size = 10;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width) / 2;
        $y = $pdf->get_height() - 75;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>
    
</body>

</html><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/reports/print/print_lab_water_report.blade.php ENDPATH**/ ?>