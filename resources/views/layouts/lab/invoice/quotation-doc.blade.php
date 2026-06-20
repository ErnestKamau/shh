@extends('layouts.lab.layout.app', ['dataTable' => true, 'select2' => true])



@section('title2')
    <title> Lab-Invoice </title>

    @if(isset($branding))
        @include('billing.quotations.amspec.partials.fonts')
        @include('billing.quotations.amspec.partials.styles')
    @endif

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

        .btn-white {
            background-color: white !important;
            box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
        }
    </style>
@endsection
@section('content2')
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
        <x-bread-crumb :items="$items"></x-bread-crumb>
        @include('layouts.lab.invoice.partials.quotation-preview-hover-styles')
        <h4 class="mt-2 quotation-preview-hover-parent">
            <?php
    $header_id = $header[0]->id;

            ?>
            <span class=" mb-2 float-left">
                <i class="mdi mdi-file-cad"></i> Billing | Quotations {{$header[0]->quote_number}}
            </span>
            <div class="nav-item dropdown float-left">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" style="color:black;font-size:14px"
                    role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-compare-vertical"></i> Move To workflow
                </a>
                <div class="dropdown-menu" style="font-size: 13px;" aria-labelledby="navbarDropdown">
                    <a class="dropdown-item"
                        href="{{route('change_quotation_workflow', ['id' => $header[0]->id, 'stage' => 'Quote In Preparation'])}}"><i
                            class="mdi mdi-subdirectory-arrow-right"></i> Quotation In Preparation</a>
                    <a class="dropdown-item"
                        href="{{route('change_quotation_workflow', ['id' => $header[0]->id, 'stage' => 'Quote Complete'])}}"><i
                            class="mdi mdi-subdirectory-arrow-right"></i> Quotation Complete</a>

                </div>
            </div>

            @if($header[0]->is_approved > 0)
            <span style="font-size: 10px;"
                class="badge badge-pill p-2 bg-white text-success"><i
                    class="mdi mdi-thumb-up"></i>
                Finalised</span>
            @endif
            <span style="font-size: 10px;"
                class="badge badge-pill p-2 bg-white {{ $header[0]->is_complete > 0 ? 'text-success' : 'text-primary'}}"><i
                    class="mdi  {{ $header[0]->is_complete > 0 ? 'mdi-thumb-up' : 'mdi-alert-decagram'}}"></i>
                {{ $header[0]->is_complete > 0 ? 'Complete' : 'Not Complete'}}</span>
            @if(($header[0]->is_batch_generate ?? 0) == 1)
                <span style="font-size: 10px;" class="badge badge-pill p-2 bg-white text-success"><i
                        class="mdi mdi-thumb-up "></i> Batch Generated </span>
            @endif

            <div class="nav-item dropdown float-right mr-2" style="margin-top: 0px !important;">
                <a class="nav-link dropdown-toggle btn btn-sm btn-outline-secondary" href="#" id="quotationDocActionsDropdown" style="color:black;font-size:14px" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-dots-vertical"></i> Actions
                </a>
                <div class="dropdown-menu dropdown-menu-right" style="font-size: 13px;" aria-labelledby="quotationDocActionsDropdown">
                    <a class="dropdown-item quotation-preview-quote-btn" href="{{ route('quotation.preview', ['id' => $header[0]->id]) }}" target="_blank" title="Preview quotation document"><i class="mdi mdi-file-eye"></i> Preview Quote</a>
                    @if($header[0]->is_complete == 1)
                    <span class="dropdown-item" style="cursor: pointer;" data-target="#print-quotation" data-header="{{$header[0]->id}}" data-toggle="modal"><i class="mdi mdi-printer"></i> Process PDF</span>
                    @if($header[0]->is_print == 1)
                    <span class="dropdown-item" style="cursor: pointer;" data-toggle="modal" data-target="#quotation-upload"><i class="mdi mdi-share-all"></i> Send Quotation</span>
                    @if($header[0]->quotation_type == 'Analysis' && ($header[0]->is_batch_generate ?? 0) == 0)
                    <span class="dropdown-item" style="cursor: pointer;" data-target="#generate-batch" data-toggle="modal"><i class="mdi mdi-cog"></i> Generate Batch</span>
                    @endif
                    @endif
                    @endif
                </div>
            </div>

        </h4><br>
        <div class="mt-5" id="quotation-document" style="clear: both;">
            @php
                $shellMode = 'embedded';
                $forPdf = false;
                $stylesLoaded = true;
            @endphp
            @include('billing.quotations.amspec.shell')
        </div>
    </main>
@endsection
@section('script2')
    <div class="modal fade" id="quotation-upload" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('upload_quotation', ['id' => $header[0]->id]) }}" method="post"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="mdi mdi-alert-decagram"></i> Confirm you want to send quote
                            {{$header[0]->quote_number}} to client.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-content-save"></i>
                            Confirm</button>
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="generate-batch" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('convert-quote-batch') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-primary p-2 d-flex">
                            <i class="mdi mdi-alert-decagram-outline" style="font-size:25px"></i>
                            <span class="pl-2">Confirm you want to generate a batch from QOUTE NUMBER
                                {{ $header[0]->quote_number }}. Note you will find the generated batch at Sample Reception
                                area at the sample workflow. Kindly add other need information from there. <br> Choose the
                                laboratory to tie the batch to below: </span>
                        </div>
                        <input type="hidden" name="quote_id" value="{{ $header[0]->id }}">
                        <div class="form-group">
                            <label for="" class="control-label">Labs <small class="text-danger">*</small></label>
                            <select name="lab_id" required id="" class="form-control">
                                <option value="">Select Lab</option>
                                @foreach ($labs as $lab)
                                    <option value="{{ $lab->id }}">{{ $lab->code . ' - ' . $lab->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-thumbs-up"></i> Yes,
                            Generate</button>
                        <sapn class="btn btn-sm btn-default" data-dismiss="modal">Close</sapn>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="print-quotation" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-printer"></i> Quote {{$header[0]->quote_number}} PDF
                        Proccessing </h5>
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

        $(function () {
            $('#print-quotation').on('show.bs.modal', function (e) {
                var header_id = $(e.relatedTarget).data('header');
                var load_text = $(`<img src="/images/loading.gif" width="70%" height="70%" alt="">
                        <div class="alert alert-primary">
                            <i class="mdi mdi-alert-octagon"></i> Kindly wait as the quotation pdf is being proccessed!
                        </div>`);
                $(this).find('.loading').empty();
                $(this).find('.loading').append(load_text);
                $.ajax({
                    url: '/billing/print_quotation/' + header_id,
                    success: function (data) {
                        console.log(data);
                    },
                    complete: function (data) {
                        $('#print-quotation').find('.loading').empty();
                        var complete_text = $(`
                        <img src="/images/suc.gif" width="50%" height="50%" alt="">
                        <div class="alert alert-success">
                            <i class="mdi mdi-alert-octagon"></i> Quote PDF generated successfully!
                        </div>
                        `);
                        $('#print-quotation').find('.loading').append(complete_text);
                    },
                    error: function (data) {
                        console.log(data);
                    }
                })
            })
        })
    </script>

@endsection