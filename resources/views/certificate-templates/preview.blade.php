@extends('layouts.lab.layout.app')

@section('title2')
  <title>Preview - {{ $certificateTemplate->name }}</title>
@endsection

@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab Management',
          'icon' => null
        ),
        array(
          'link' => route('certificate-templates.index'),
          'name' => 'Certificate Templates',
          'icon' => null
        ),
        array(
          'link' => route('certificate-templates.show', $certificateTemplate),
          'name' => $certificateTemplate->name,
          'icon' => null
        ),
        array(
          'link' => '#',
          'name' => 'Preview',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center p-4">
      <div>
        <h2>
          <i class="mdi mdi-eye-outline"></i> Template Preview
        </h2>
        <p class="text-muted mb-0">{{ $certificateTemplate->name }} - v{{ $certificateTemplate->version }}</p>
      </div>
      <div class="btn-group" role="group">
        <a href="{{ route('certificate-templates.show', $certificateTemplate) }}" class="btn btn-secondary">
          <i class="mdi mdi-arrow-left"></i> Back to Template
        </a>
        @can('build', $certificateTemplate)
          <a href="{{ route('certificate-templates.builder', $certificateTemplate) }}" class="btn btn-primary">
            <i class="mdi mdi-view-dashboard"></i> Edit in Builder
          </a>
        @endcan
      </div>
    </div>

    <div class="row">
      <!-- Preview Controls -->
      <div class="col-md-3">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="mdi mdi-cog"></i> Preview Options
            </h5>
          </div>
          <div class="card-body">
            <?php 
              $pageSettings = $certificateTemplate->getPageSettings();
            ?>
            
            <div class="form-group">
              <label class="form-label">Page Settings</label>
              <ul class="list-unstyled small">
                <li><strong>Size:</strong> {{ $pageSettings['page_size'] }}</li>
                <li><strong>Orientation:</strong> {{ ucfirst($pageSettings['orientation']) }}</li>
                <li><strong>Margins:</strong> {{ $pageSettings['margins']['top'] }}/{{ $pageSettings['margins']['right'] }}/{{ $pageSettings['margins']['bottom'] }}/{{ $pageSettings['margins']['left'] }}</li>
              </ul>
            </div>

            <div class="form-group">
              <label class="form-label">Zoom Level</label>
              <select class="form-control form-control-sm" id="zoom-control">
                <option value="0.5">50%</option>
                <option value="0.75">75%</option>
                <option value="1" selected>100%</option>
                <option value="1.25">125%</option>
                <option value="1.5">150%</option>
              </select>
            </div>

            <div class="form-group">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="show-sample-data" checked>
                <label class="form-check-label" for="show-sample-data">
                  Show Sample Data
                </label>
              </div>
            </div>

            <div class="form-group">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="show-placeholders">
                <label class="form-check-label" for="show-placeholders">
                  Show Placeholders
                </label>
              </div>
            </div>

            <hr>

            <div class="form-group">
              <button type="button" class="btn btn-sm btn-outline-primary btn-block" onclick="printPreview()">
                <i class="mdi mdi-printer"></i> Print Preview
              </button>
            </div>
          </div>
        </div>

        <!-- Template Info -->
        <div class="card mt-3">
          <div class="card-header">
            <h5 class="mb-0">
              <i class="mdi mdi-information"></i> Template Info
            </h5>
          </div>
          <div class="card-body">
            <ul class="list-unstyled small">
              <li><strong>Sections:</strong> {{ $certificateTemplate->sections->count() }}</li>
              <li><strong>Elements:</strong> {{ $certificateTemplate->sections->sum(function($section) { return $section->elements->count(); }) }}</li>
              <li><strong>Status:</strong> 
                @if($certificateTemplate->is_published)
                  <span class="badge badge-success">Published</span>
                @else
                  <span class="badge badge-warning">Draft</span>
                @endif
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Preview Area -->
      <div class="col-md-9">
        <div class="card">
          <div class="card-body p-0">
            <div class="preview-container" style="background: #f8f9fa; padding: 20px; min-height: 600px;">
              <div class="preview-page" id="preview-page" style="
                background: white;
                margin: 0 auto;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                transform-origin: top center;
                {{ $pageSettings['orientation'] === 'landscape' ? 'width: 297mm; height: 210mm;' : 'width: 210mm; height: 297mm;' }}
                padding: {{ $pageSettings['margins']['top'] }} {{ $pageSettings['margins']['right'] }} {{ $pageSettings['margins']['bottom'] }} {{ $pageSettings['margins']['left'] }};
                position: relative;
              ">
                
                @if($certificateTemplate->getHeaderSettings()['enabled'])
                  <div class="template-header" style="
                    position: absolute;
                    top: 0;
                    left: {{ $pageSettings['margins']['left'] }};
                    right: {{ $pageSettings['margins']['right'] }};
                    height: {{ $certificateTemplate->getHeaderSettings()['height'] }};
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                  ">
                    <div class="header-content">
                      {!! $certificateTemplate->getHeaderSettings()['content'] ?? 'Header Content' !!}
                    </div>
                  </div>
                @endif

                <div class="template-content" style="
                  margin-top: {{ $certificateTemplate->getHeaderSettings()['enabled'] ? $certificateTemplate->getHeaderSettings()['height'] : '0' }};
                  margin-bottom: {{ $certificateTemplate->getFooterSettings()['enabled'] ? $certificateTemplate->getFooterSettings()['height'] : '0' }};
                ">
                  @if($certificateTemplate->sections->count() > 0)
                    @foreach($certificateTemplate->rootSections as $section)
                      @include('certificate-templates.partials.preview-section', ['section' => $section, 'sampleData' => $sampleData])
                    @endforeach
                  @else
                    <div class="text-center py-5">
                      <i class="mdi mdi-file-outline" style="font-size: 3rem; color: #ccc;"></i>
                      <h5 class="text-muted mt-3">No content to preview</h5>
                      <p class="text-muted">Add sections and elements to see the preview.</p>
                    </div>
                  @endif
                </div>

                @if($certificateTemplate->getFooterSettings()['enabled'])
                  <div class="template-footer" style="
                    position: absolute;
                    bottom: 0;
                    left: {{ $pageSettings['margins']['left'] }};
                    right: {{ $pageSettings['margins']['right'] }};
                    height: {{ $certificateTemplate->getFooterSettings()['height'] }};
                    border-top: 1px solid #eee;
                    padding-top: 10px;
                  ">
                    <div class="footer-content">
                      {!! $certificateTemplate->getFooterSettings()['content'] ?? 'Footer Content' !!}
                      @if($certificateTemplate->getFooterSettings()['show_page_numbers'])
                        <div class="page-numbers text-center mt-2">
                          {{ str_replace(['{current}', '{total}'], ['1', '1'], $certificateTemplate->getFooterSettings()['page_number_format']) }}
                        </div>
                      @endif
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection

@section('scripts')
<script>
  // Zoom control
  document.getElementById('zoom-control').addEventListener('change', function() {
    const previewPage = document.getElementById('preview-page');
    const zoomLevel = parseFloat(this.value);
    previewPage.style.transform = `scale(${zoomLevel})`;
  });

  // Sample data toggle
  document.getElementById('show-sample-data').addEventListener('change', function() {
    const sampleElements = document.querySelectorAll('.sample-data');
    const placeholderElements = document.querySelectorAll('.placeholder-data');
    
    if (this.checked) {
      sampleElements.forEach(el => el.style.display = 'inline');
      placeholderElements.forEach(el => el.style.display = 'none');
    } else {
      sampleElements.forEach(el => el.style.display = 'none');
      placeholderElements.forEach(el => el.style.display = 'inline');
    }
  });

  // Placeholder toggle
  document.getElementById('show-placeholders').addEventListener('change', function() {
    const placeholderElements = document.querySelectorAll('.field-placeholder');
    
    if (this.checked) {
      placeholderElements.forEach(el => {
        el.style.background = '#fff3cd';
        el.style.border = '1px dashed #856404';
        el.style.padding = '2px 4px';
      });
    } else {
      placeholderElements.forEach(el => {
        el.style.background = 'transparent';
        el.style.border = 'none';
        el.style.padding = '0';
      });
    }
  });

  // Print preview
  function printPreview() {
    const printWindow = window.open('', '_blank');
    const previewContent = document.getElementById('preview-page').outerHTML;
    
    printWindow.document.write(`
      <html>
        <head>
          <title>{{ $certificateTemplate->name }} - Preview</title>
          <style>
            body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
            .preview-page { transform: none !important; }
            @media print {
              body { padding: 0; }
              .preview-page { box-shadow: none !important; }
            }
          </style>
        </head>
        <body>
          ${previewContent}
        </body>
      </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
  }
</script>
@endsection