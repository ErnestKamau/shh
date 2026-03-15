@php
/** @var \Illuminate\Support\Collection|\App\BatchAttachment[] $attachments */
$attachments = $attachments ?? collect();
$serologySummaries = $serologySummaries ?? [];
@endphp

<div class="results-section">
    @if(empty($serologySummaries))
        <p style="font-size: 10px; margin: 0;">
            No result attachments have been linked to this batch.
        </p>
    @else
        <div style="font-size: 10px; line-height: 1.4;">
            @foreach($serologySummaries as $summary)
                @php
                    $sampleCodes = !empty($summary['samples']) ? implode(', ', $summary['samples']) : 'N/A';
                    $parameters  = !empty($summary['parameters']) ? implode(', ', $summary['parameters']) : 'N/A';

                    $rawTitle = $summary['title'] ?? 'Attachment';
                    $docTitle = $rawTitle;

                    $pageFrom = $summary['page_from'] ?? null;
                    $pageTo   = $summary['page_to'] ?? null;
                @endphp

                <div style="margin: 0 0 4px; padding: 4px 6px; border-left: 2px solid #4682B4;">
                    <span style="font-weight: bold;">Sample code(s):</span>
                    <span>{{ $sampleCodes }}</span>
                    &nbsp;&nbsp;
                    <span style="font-weight: bold;">Test(s) / Parameter(s):</span>
                    <span>{{ $parameters }}</span>
                    <br>
                    <span style="font-weight: bold;">Attachment:</span>
                    <span>{{ $docTitle }}</span>
                    @if($pageFrom)
                        @if($pageTo && $pageTo != $pageFrom)
                            <span>&mdash; from pages {{ $pageFrom }}&ndash;{{ $pageTo }}</span>
                        @else
                            <span>&mdash; from page {{ $pageFrom }}</span>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>