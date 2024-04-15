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
    @page {
        /* margin-top: 20px; */
        margin-bottom: 10px;
        margin-top: 100px;
        margin-left: 20;
        margin-right: 20;

        @bottom-center {
            content: element(footer);
        }

        @top-center {
            content: element(header);
        }

    }

    * {
        font: 'Arial Narrow', Arial, sans-serif;
        font-stretch: condensed;
    }

    .header {
        position: fixed;
        top: -80px;
        left: 0;
        right: 0;
        height: 50px;
        z-index: 1000;


    }

    .footer {
        position: fixed;
        bottom: 10px;
        left: 0;
        right: 0;
        z-index: 1;
    }

    .footer_signatures {
        position: fixed;
        bottom: 180px;
        left: 0;
        right: 0;
        z-index: 1000;
    }

    .dotted-line {
        border: none;
        margin: 0% 35%;
        background-color: rgb(254, 254, 254);
        border-bottom: 3px dotted rgb(0, 0, 0);
    }


    .parameter {
        border: solid 1 rgba(0, 0, 0, 0.35) !important;
        /* border: solid 1 black !important; */

        padding: 0 !important;

    }

    .textBold {
        font-weight: 800 !important;
    }

    .footer_addr {
        font-size: 8px !important;
        font-weight: bolder !important;
    }

    .stamp-section {
        position: fixed;
        bottom: 145px;
        right: -4px!important;
    }
</style>

<body>
    <div class="main-lab" style="position:fixed;bottom:22%;left:1%;font-size:8px">
        <b>{{ strtoupper($main_lab) }}</b><br>
        <b>{{ $batch->approval_date != '' && $batch->prelim_report_status != 2 ? convertDateFormatReports($batch->approval_date, 'dateShortMonth') : '-' }}</b>
    </div>
    <header class="header">
        <table style="width: 100%;border:0px;">
            <tr>
                <td style="font-size: 10px !important;border:solid 0 transparent !important; width:30% !important">
                    <img src="{{ $path }}" style="height:70px;" alt="logo">

                </td>

                <td style="border: solid 0 transparent !important;text-align:right;font-size:11px !important;">
                    {{ $customer->name }} <br>
                    {{ $customer->postal_address }} <br>
                    {{-- {{ $customer->physical_address }} --}}
                </td>
            </tr>

        </table>
    </header>

    @if (isset($is_stamp->id))
        <div class="stamp-section">
            <img src="{{ $stamp }}" style="height:160px; z-index:1000;position: relative;" alt="">
        </div>
    @endif

    <?php
    $printed_title = [];
    $printed_sig = [];
    $printed_pos = [];
    
    ?>
    @foreach (range(1,3) as $sample)
        <div class="my-footer" style="position: sticky !important;top:600px !important;right: 0">
            My footer {{$sample}}
        </div>
    
        
        
        <main style="margin-bottom:280px">
          Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum accusantium laborum cumque eaque eos minus rerum 
          perferendis pariatur, nisi facere harum aperiam sunt? Nobis quo facere repudiandae, adipisci libero reiciendis?Lorem ipsum dolor, sit amet consectetur adipisicing elit. Facilis temporibus ipsum ut quas asperiores officia molestias maiores est aut rem distinctio saepe quia nobis similique, totam ea praesentium quis quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Error harum porro quo amet maiores dicta dignissimos earum officia tempora quod. Quo consequuntur sed excepturi ipsa, quos atque. Nobis, temporibus iusto? Lorem ipsum, dolor sit amet consectetur adipisicing elit. Architecto, dolore excepturi. Molestias sequi inventore nostrum! Eligendi beatae et enim tenetur eveniet ex cupiditate mollitia. Voluptate dicta maiores culpa dignissimos cumque?
          Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum accusantium laborum cumque eaque eos minus rerum perferendis pariatur, nisi facere harum aperiam sunt? Nobis quo facere repudiandae, adipisci libero reiciendis?Lorem ipsum dolor, sit amet consectetur adipisicing elit. Facilis temporibus ipsum ut quas asperiores officia molestias maiores est aut rem distinctio saepe quia nobis similique, totam ea praesentium quis quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Error harum porro quo amet maiores dicta dignissimos earum officia tempora quod. Quo consequuntur sed excepturi ipsa, quos atque. Nobis, temporibus iusto? Lorem ipsum, dolor sit amet consectetur adipisicing elit. Architecto, dolore excepturi. Molestias sequi inventore nostrum! Eligendi beatae et enim tenetur eveniet ex cupiditate mollitia. Voluptate dicta maiores culpa dignissimos cumque?
          Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum accusantium laborum cumque eaque eos minus rerum perferendis pariatur, nisi facere harum aperiam sunt? Nobis quo facere repudiandae, adipisci libero reiciendis?Lorem ipsum dolor, sit amet consectetur adipisicing elit. Facilis temporibus ipsum ut quas asperiores officia molestias maiores est aut rem distinctio saepe quia nobis similique, totam ea praesentium quis quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Error harum porro quo amet maiores dicta dignissimos earum officia tempora quod. Quo consequuntur sed excepturi ipsa, quos atque. Nobis, temporibus iusto? Lorem ipsum, dolor sit amet consectetur adipisicing elit. Architecto, dolore excepturi. Molestias sequi inventore nostrum! Eligendi beatae et enim tenetur eveniet ex cupiditate mollitia. Voluptate dicta maiores culpa dignissimos cumque?
          Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum accusantium laborum cumque eaque eos minus rerum perferendis pariatur, nisi facere harum aperiam sunt? Nobis quo facere repudiandae, adipisci libero reiciendis?Lorem ipsum dolor, sit amet consectetur adipisicing elit. Facilis temporibus ipsum ut quas asperiores officia molestias maiores est aut rem distinctio saepe quia nobis similique, totam ea praesentium quis quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Error harum porro quo amet maiores dicta dignissimos earum officia tempora quod. Quo consequuntur sed excepturi ipsa, quos atque. Nobis, temporibus iusto? Lorem ipsum, dolor sit amet consectetur adipisicing elit. Architecto, dolore excepturi. Molestias sequi inventore nostrum! Eligendi beatae et enim tenetur eveniet ex cupiditate mollitia. Voluptate dicta maiores culpa dignissimos cumque?
          Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum accusantium laborum cumque eaque eos minus rerum perferendis pariatur, nisi facere harum aperiam sunt? Nobis quo facere repudiandae, adipisci libero reiciendis?Lorem ipsum dolor, sit amet consectetur adipisicing elit. Facilis temporibus ipsum ut quas asperiores officia molestias maiores est aut rem distinctio saepe quia nobis similique, totam ea praesentium quis quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Error harum porro quo amet maiores dicta dignissimos earum officia tempora quod. Quo consequuntur sed excepturi ipsa, quos atque. Nobis, temporibus iusto? Lorem ipsum, dolor sit amet consectetur adipisicing elit. Architecto, dolore excepturi. Molestias sequi inventore nostrum! Eligendi beatae et enim tenetur eveniet ex cupiditate mollitia. Voluptate dicta maiores culpa dignissimos cumque?
          Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum accusantium laborum cumque eaque eos minus rerum perferendis pariatur, nisi facere harum aperiam sunt? Nobis quo facere repudiandae, adipisci libero reiciendis?Lorem ipsum dolor, sit amet consectetur adipisicing elit. Facilis temporibus ipsum ut quas asperiores officia molestias maiores est aut rem distinctio saepe quia nobis similique, totam ea praesentium quis quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Error harum porro quo amet maiores dicta dignissimos earum officia tempora quod. Quo consequuntur sed excepturi ipsa, quos atque. Nobis, temporibus iusto? Lorem ipsum, dolor sit amet consectetur adipisicing elit. Architecto, dolore excepturi. Molestias sequi inventore nostrum! Eligendi beatae et enim tenetur eveniet ex cupiditate mollitia. Voluptate dicta maiores culpa dignissimos cumque?
          Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum accusantium laborum cumque eaque eos minus rerum perferendis pariatur, nisi facere harum aperiam sunt? Nobis quo facere repudiandae, adipisci libero reiciendis?Lorem ipsum dolor, sit amet consectetur adipisicing elit. Facilis temporibus ipsum ut quas asperiores officia molestias maiores est aut rem distinctio saepe quia nobis similique, totam ea praesentium quis quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Error harum porro quo amet maiores dicta dignissimos earum officia tempora quod. Quo consequuntur sed excepturi ipsa, quos atque. Nobis, temporibus iusto? Lorem ipsum, dolor sit amet consectetur adipisicing elit. Architecto, dolore excepturi. Molestias sequi inventore nostrum! Eligendi beatae et enim tenetur eveniet ex cupiditate mollitia. Voluptate dicta maiores culpa dignissimos cumque?
          Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsum accusantium laborum cumque eaque eos minus rerum perferendis pariatur, nisi facere harum aperiam sunt? Nobis quo facere repudiandae, adipisci libero reiciendis?Lorem ipsum dolor, sit amet consectetur adipisicing elit. Facilis temporibus ipsum ut quas asperiores officia molestias maiores est aut rem distinctio saepe quia nobis similique, totam ea praesentium quis quod. Lorem ipsum dolor sit amet consectetur adipisicing elit. Error harum porro quo amet maiores dicta dignissimos earum officia tempora quod. Quo consequuntur sed excepturi ipsa, quos atque. Nobis, temporibus iusto? Lorem ipsum, dolor sit amet consectetur adipisicing elit. Architecto, dolore excepturi. Molestias sequi inventore nostrum! Eligendi beatae et enim tenetur eveniet ex cupiditate mollitia. Voluptate dicta maiores culpa dignissimos cumque?
        </main>
        @if ($loop->iteration < 3)
            <div style="page-break-after: always;">
            </div>
        @endif
    @endforeach
    <script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $size = 9;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width) / 1;
        $y = $pdf->get_height() - 10;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>

</body>

</html>
