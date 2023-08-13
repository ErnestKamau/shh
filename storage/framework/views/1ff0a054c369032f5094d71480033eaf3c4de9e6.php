<!DOCTYPE html>
<html lang="en">


<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Document</title>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" /> -->

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"
        integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg=="
        crossorigin="anonymous"></script>
</head>

<style>
    @page  {
        margin: 20px;
    }

    @page  {
        margin-top: 20px;

        @bottom-center {
            content: element(footer);
        }

        @top-center {
            content: element(header);
        }

    }



    .header {
        position: running(header);
        /* top: 0px;
        left: 0;
        right: 0;
        height: 50;
        z-index: 1000; */


    }

    /* .header::before{
        position: running(header);
    } */
    .footer {
        position: fixed;
        bottom: 120;
        left: 0;
        right: 0;

        z-index: 1000;
    }

    .parameter {
        border: solid 1 rgba(0, 0, 0, 0.35) !important;
        /* border: solid 1 black !important; */

        padding: 0 !important;

    }

    .textBold {
        font-weight: 700 !important;
    }
</style>

<body>


    <footer class="footer">

        <table class="" style="margin-top: 1px !important; border-bottom:1px solid black;width:100%">

            <tr>
                <?php $__currentLoopData = $batch_approvers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td style="font-size: 8px !important;">
                        <b><?php echo e($approver->title); ?></b><br>
                        <img src="<?php echo e(getCoaApproverSignature($approver->getApproverDetails()->electronic_sig)); ?>"
                            style="width:80px" alt="signature"><br>
                        <span><?php echo e($approver->getApproverDetails()->name); ?> -
                            <?php echo e($approver->getApproverPositionDetails()); ?></span>
                    </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </tr>
        </table>

        <div style="margin-top:10px">
            <table style="width:100%">
                <tr>
                    <td style="width: 10%">
                        <img src="data:image/png;base64, <?php echo $qrcode; ?>" width="60" height="60">
                        <span style="font-size: 8px !important;">Scan to Verify</span>

                    </td>
                    <td>
                        
                    </td>
                    
                </tr>
            </table>
        </div>
    </footer>

    <?php $__currentLoopData = $samples; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sample): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <main style="margin-bottom: 20px;">


            <table class="table table-sm table-bordered" style="font-size: 8px;">
                <thead style="height: 60px !important;">
                    <tr style="border: solid 1px black !important;margin:0 !important">
                        <th style="font-size: 6px !important;border: solid 0 transparent !important" colspan="5">
                            <table style="width: 100%;border:0px; border-bottom: solid 1px #014421 !important">
                                <tr>
                                    <td
                                        style="font-size: 10px !important;border:solid 0 transparent !important; width:30% !important;border-bottom: 1px solid #014421 !important;">
                                        <img src="<?php echo e($path); ?>" style="height:90px;" alt="logo">

                                    </td>

                                    <td
                                        style="border: solid 0 transparent !important;text-align:right;font-size:11px !important; border-bottom: 1px solid #014421 !important;">
                                        <div class="">
                                            <span style="font-size: 8px"><?php echo e($company->name); ?></span> <br>
                                            <?php echo e($company->street); ?> <br>
                                            P.O. Box <?php echo e($company->address); ?>,<?php echo e($company->location); ?> <br>
                                            Office: <?php echo e($company->telephone); ?> Fax: <?php echo e($company->fax); ?> <br>
                                            Tel : <?php echo e($company->cell_phone); ?> <br>
                                            Email: <?php echo e($company->email); ?> <br> Web: <?php echo e($company->website); ?> <br>
                                            <span style="font-size: 8px">Member of POLUCON
                                                Group</span>
                                        </div>

                                    </td>
                                </tr>

                            </table>

                        </th>
                    </tr>
                    <tr style="margin:0 !important">
                        <th colspan="5"
                            style="border: solid 0 transparent !important;border-bottom:1px solid rgba(0, 0, 0, 0.35)">
                            <table style="width: 100%;border:0px;">
                                <tr>
                                    <td colspan="4"
                                        style=" border: 1px solid rgba(0, 0, 0, 0.35) !important; font-size:10px !important;">
                                        <b> TEST REPORT NO :
                                            R<?php echo e(substr($sample->sample_code, 1, strlen($sample->sample_code))); ?></b>
                                    </td>

                                </tr>
                                <tr>
                                    <td
                                        style="width:20%;border: solid 0 transparent !important;font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35)">
                                        SAMPLE</td>
                                    <td
                                        style="padding-left:10px !important;border: solid 0 transparent !important;font-size:8px !important;width:40%">
                                        <?php echo e($sample->sample_type_name); ?></td>
                                    <td
                                        style="width:40%;border: solid 0 transparent !important;font-size:8px !important;;text-align:right">
                                        CUSTOMER: </td>
                                    <td
                                        style="padding-left:10px !important;border: solid 0 transparent !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35);text-align:right;width:20%">
                                        <?php echo e($sample->crm_name); ?></td>

                                </tr>
                                <tr>
                                    <td
                                        style="border: solid 0 transparent !important;font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35)">
                                        DATE & PLACE
                                        <?php echo e($sample->sampled_by_company_personnel == 1 ? 'SAMPLED' : 'SUBMITTED'); ?></td>
                                    <?php if($sample->sampled_by_company_personnel == 1): ?>
                                        <td
                                            style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;">
                                            <?php echo e($sample->date_collected); ?> <?php echo e($sample->sample_point_name); ?></td>
                                    <?php else: ?>
                                        <td
                                            style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;">
                                            <?php echo e($sample->receipt_date); ?> <?php echo e($company->name); ?></td>
                                    <?php endif; ?>
                                    <td
                                    style="width:20%;border: solid 0 transparent !important;font-size:8px !important;;text-align:right">
                                    ADDRESS: </td>
                                <td
                                    style="padding-left:10px !important;border: solid 0 transparent !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35);text-align:right">
                                    P.O BOX <?php echo e($sample->postal_address); ?></td>

                                </tr>
                                <tr>
                                    <td
                                        style="border: solid 0 transparent !important;;font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35)">
                                        DATE ANALYSIS STARTED</td>
                                    <td
                                        style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;">
                                    </td>
                                    <td
                                    style="width:20%;border: solid 0 transparent !important;font-size:8px !important;text-align:right">
                                    LOCATION : </td>
                                <td
                                    style="padding-left:10px !important;border: solid 0 transparent !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35);text-align:right">
                                    <?php echo e($sample->physical_address); ?></td>

                                </tr>
                                <tr>
                                    <td
                                        style="border: solid 0 transparent !important;font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35)">
                                        SAMPLING METHOD</td>
                                    <td
                                        style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;">
                                        <?php echo e($sample->sampling_method_name); ?></td>
                                        <td style="width:20%;border: solid 0 transparent !important;font-size:8px !important;text-align:right">SAMPLE ID</td>
                                        <td style="padding-left:10px !important;border: solid 0 transparent !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35);text-align:right">
                                        <?php echo e($sample->sample_code); ?></td>
                                       
                                </tr>
                                <tr>
                                    <td
                                        style="border: solid 0 transparent !important;font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35)">
                                        <?php echo e(strtoupper($sample->main_lab_name)); ?></td>
                                    <td
                                        style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;">
                                        <?php echo e($batch->approval_date ?? '-'); ?></td>
                                        <td style="width:20%;border: solid 0 transparent !important;font-size:8px !important;text-align:right"></td>
                                        <td style="padding-left:10px !important;border: solid 0 transparent !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35);text-align:right"></td>
                                </tr>
                                <tr>
                                    <td
                                        style="border: solid 0 transparent !important;;font-size:8px !important;border-bottom:1px solid rgba(0, 0, 0, 0.35);border-left:1px solid rgba(0, 0, 0, 0.35)">
                                        MARKINGS</td>
                                    <td 
                                        style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;border-bottom:1px solid rgba(0, 0, 0, 0.35);">
                                        <?php echo e($sample->comments); ?></td>
                                        <td style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;border-bottom:1px solid rgba(0, 0, 0, 0.35);"></td>
                                        <td style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;border-bottom:1px solid rgba(0, 0, 0, 0.35);border-right:1px solid rgba(0, 0, 0, 0.35)"></td>
                                        
                                </tr>

                            </table>
                        </th>
                    </tr>

                    <tr style="">
                        <th class="parameter"
                            style="font-size: 9px !important; width:25% !important;vertical-align: top !important;padding:5px !important;">
                            TESTS</th>
                        <th class="parameter"
                            style="font-size:9px !important;width:25% !important;vertical-align: top !important;padding:5px !important">
                            TEST METHODS</th>
                        <th class="parameter"
                            style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                            RESULTS</th>
                        <th class="parameter"
                            style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                            UNITS</th>
                        <th class="parameter text-center"
                            style="font-size: 9px !important;width:15% !important;vertical-align: top !important;padding:5px !important">
                            <?php echo e($sample->main_standard_code); ?></th>

                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $sample->getSampleByAnalysisType(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analysis_type_level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="parameter"
                                style="font-size: 10px !important;font-weight:600;background-color:#fafafa;padding:1px !important;padding-left:2px !important;"
                                colspan="5"><?php echo e(strtoupper($analysis_type_level->analysis_type_name)); ?></td>
                        </tr>
                        <?php $__currentLoopData = $analysis_type_level->getCapturedResults(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $captured): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="parameter <?php echo e($captured->remark == 'FAIL' ? 'textBold' : ''); ?>"
                                    style="font-size: 9px !important;padding-left:3px !important;">
                                    <?php echo $captured->analyte_status_contracted == 1 ? '<small>*</small>' : ''; ?> <?php echo e($captured->analyte_code); ?>

                                </td>
                                <td class="parameter <?php echo e($captured->remark == 'FAIL' ? 'textBold' : ''); ?>"
                                    style="font-size: 9px !important;padding-left:3px !important;">
                                    <?php echo e($captured->method()->name); ?>

                                </td>
                                <td class="parameter <?php echo e($captured->remark == 'FAIL' ? 'textBold' : ''); ?>"
                                    style="font-size: 9px !important;padding-left:3px !important;">
                                    <?php echo e($captured->result_reporting_symbol ?? ''); ?><?php echo e($captured->result); ?>

                                </td>
                                <td class="parameter <?php echo e($captured->remark == 'FAIL' ? 'textBold' : ''); ?>"
                                    style="font-size: 9px !important;padding-left:3px !important;">
                                    <?php echo e($captured->analyte()->reporting_unit); ?>

                                </td>
                                <td class="parameter <?php echo e($captured->remark == 'FAIL' ? 'textBold' : ''); ?>"
                                    style="font-size: 9px !important;padding-left:3px !important;">
                                    <?php echo e($captured->main_value); ?>

                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td style="font-size: 9px !important;padding-left:3px !important; text-align:center ;border: 0 transparent !important"
                            colspan="5">******<small>End of Test Results</small>*******</td>
                    </tr>
                </tbody>
            </table>

            <table style="margin:0px !important;width:100%">
                <tr style="margin:0px !important">
                    <td style="font-size:8px !important;">
                        <b>Comments : </b><?php echo e($sample->header_body); ?>


                    </td>
                </tr>
                <tr>
                    <td style="font-size: 8px !important;">
                        <br>
                        <span><?php echo e($non_accredited->value); ?></span>

                        <div class="" style="">
                            <?php echo e($disclaimer->value); ?>

                            <?php if($batch->sampled_by_company_personnel == 0): ?>
                                <br>
                                <b>NB: This report relates to submitted sample(s) only. The source and markings are as
                                    provided by the customer.</b>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            </table>
            <?php if($sample->getAccredittedStatus() >= 1): ?>
                <div class="" style="display:inline-block;position:fixed;bottom:50;left:65%">
                    <img src="<?php echo e($kebs); ?>" style="width:70px;height:70px" alt="">

                    <img src="<?php echo e($kenas); ?>" style="width:70px;height:70px" alt="">


                    <img src="<?php echo e($ilac); ?>" style="width:70px;height:80px" alt="">
                </div>
            <?php else: ?>
                <div class="" style="display:inline-block;position:fixed;bottom:50;left:65%">
                    <img src="<?php echo e($kebs); ?>" style="width:70px;height:70px" alt="">

                    <img src="<?php echo e($nema); ?>" style="width:70px;height:70px" alt="">


                    <img src="<?php echo e($ispm); ?>" style="width:70px;height:70px" alt="">
                </div>
            <?php endif; ?>


            <?php if($loop->iteration < $samples->count()): ?>
                <div style="page-break-after: always;">
                </div>
            <?php endif; ?>
        </main>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $size = 9;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width) / 2;
        $y = $pdf->get_height() - 20;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
    
</script>

</body>

</html>
<?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/reports/coa_formats/standard_report_2.blade.php ENDPATH**/ ?>