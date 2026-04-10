@extends('layouts.risk.layout.app')

@section('title2')
<title>Create Risk - JASIRI LIMS</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array('link' => route('risk.risks.index'), 'name' => 'Risk Management', 'icon' => null),
        array('link' => '#', 'name' => 'Create Risk', 'icon' => null)
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plus"></i> Create New Risk</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('risk.risks.store') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control" value="{{ $prefillData['title'] ?? '' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date Identified <span class="text-danger">*</span></label>
                                        <input type="date" name="date_identified" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="risk-description" class="form-control editor" rows="3" required>{{ $prefillData['description'] ?? '' }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Category <span class="text-danger">*</span></label>
                                        <select name="category_id" class="form-control" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ ($prefillData['category_name'] ?? '') === $category->name ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h5>Link to Related Items - Risk Sources</h5>
                            <p class="text-muted small">Link this risk to related samples, equipment, methods, or personnel</p>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Sample</label>
                                        <select name="sample_id" class="form-control">
                                            <option value="">Select Sample/Batch...</option>
                                            @foreach($samples as $sample)
                                            <option value="{{ $sample->id }}">
                                                {{ $sample->batch_code }}@if($sample->reference_number) - {{ $sample->reference_number }}@endif
                                            </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="sample_reference" value="">
                                        <small class="form-text text-muted">Search and select a sample/batch</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Equipment</label>
                                        <select name="equipment_id" class="form-control">
                                            <option value="">Select Equipment...</option>
                                            @foreach($equipment as $eq)
                                            <option value="{{ $eq->id }}">{{ $eq->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Search and select equipment</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Method</label>
                                        <select name="method_id" class="form-control">
                                            <option value="">Select Analysis Method...</option>
                                            @foreach($methods as $method)
                                            <option value="{{ $method->id }}">
                                                {{ $method->name }}@if($method->code) ({{ $method->code }})@endif
                                            </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="method_reference" value="">
                                        <small class="form-text text-muted">Search and select analysis method</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Personnel</label>
                                        <select name="personnel_id" class="form-control">
                                            <option value="">Select Personnel...</option>
                                            @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Select personnel involved</small>
                                    </div>
                                </div>
                            </div>
                                     <h5>Other Risk Sources</h5>
                            <p class="text-muted small">Specify other sources of this risk</p>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Other Sources</label>
                                        <select name="other_source_id" class="form-control">
                                            <option value="">Select Other Sources</option>
                                            @foreach($sources as $source)
                                                <option value="{{ $source->id }}" {{ ($prefillData['source_name'] ?? '') === $source->name ? 'selected' : '' }}>
                                                    {{ $source->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Select other source categories for this risk</small>
                                    </div>
                                </div>
                            </div>

                            <hr>
                   

                            
                            <h5>Assignment</h5>
                            <p class="text-muted small">Assign risk owner and department</p>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Risk Owner <span class="text-danger">*</span></label>
                                        <select name="risk_owner_id" class="form-control" required>
                                            <option value="">Select Risk Owner</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select name="department_id" class="form-control">
                                            <option value="">Select Department</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            @if(isset($prefillData['audit_finding_id']))
                                <input type="hidden" name="audit_finding_id" value="{{ $prefillData['audit_finding_id'] }}">
                            @endif
                            @if(isset($prefillData['audit_id']))
                                <input type="hidden" name="audit_id" value="{{ $prefillData['audit_id'] }}">
                            @endif
                            @if(isset($prefillData['non_conformance_id']))
                                <input type="hidden" name="non_conformance_id" value="{{ $prefillData['non_conformance_id'] }}">
                            @endif

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" onclick="if(typeof tinyMCE !== 'undefined') { tinyMCE.triggerSave(); }">
                                    <i class="fas fa-save"></i> Create Risk
                                </button>
                                <a href="{{ route('risk.risks.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script2')
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
<script>
    $(document).ready(function() {
        // Wait for TinyMCE script to load
        if (typeof tinymce !== 'undefined') {
            setTimeout(function() {
                initTinyMCE();
            }, 50);
        } else {
            var checkTinyMCE = setInterval(function() {
                if (typeof tinymce !== 'undefined') {
                    clearInterval(checkTinyMCE);
                    setTimeout(function() {
                        initTinyMCE();
                    }, 50);
                }
            }, 100);
        }

        // Update sample_reference when sample_id changes
        $('select[name="sample_id"]').on('change', function() {
            var $option = $(this).find('option:selected');
            var sampleText = $option.text();
            if ($(this).val()) {
                $('input[name="sample_reference"]').val(sampleText);
            } else {
                $('input[name="sample_reference"]').val('');
            }
        });

        // Update method_reference when method_id changes
        $('select[name="method_id"]').on('change', function() {
            var $option = $(this).find('option:selected');
            var methodText = $option.text();
            if ($(this).val()) {
                $('input[name="method_reference"]').val(methodText);
            } else {
                $('input[name="method_reference"]').val('');
            }
        });
    });

    function initTinyMCE() {
        if (typeof tinymce === 'undefined') return;
        
        $('textarea.editor').each(function() {
            var $textarea = $(this);
            var textareaId = $textarea.attr('id') || 'editor-' + Math.random().toString(36).substr(2, 9);
            if (!$textarea.attr('id')) {
                $textarea.attr('id', textareaId);
            }
            
            if (tinymce.get(textareaId)) {
                return;
            }
            
            tinymce.init({
                selector: '#' + textareaId,
                menubar: false,
                height: 300,
                plugins: 'lists link code',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link | code',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; line-height: 1.6; }',
                branding: false,
                setup: function(editor) {
                    editor.on('change keyup', function() {
                        editor.save();
                    });
                }
            });
        });
    }
</script>
@endsection

