@php
    $leftLogos = $section->getSectionLogosByPosition('left');
    $middleLogos = $section->getSectionLogosByPosition('middle');
    $rightLogos = $section->getSectionLogosByPosition('right');
@endphp

@if(count($leftLogos) > 0 || count($middleLogos) > 0 || count($rightLogos) > 0)
    <div class="section-logos mb-2">
        <div class="row no-gutters">
            <div class="col-4 d-flex flex-wrap justify-content-start align-items-center">
                @foreach($leftLogos as $logo)
                    <img src="{{ Storage::url($logo['path']) }}" alt="Section logo" class="mr-2 mb-2" style="height: 44px; width: auto; object-fit: contain;">
                @endforeach
            </div>
            <div class="col-4 d-flex flex-wrap justify-content-center align-items-center">
                @foreach($middleLogos as $logo)
                    <img src="{{ Storage::url($logo['path']) }}" alt="Section logo" class="mr-2 mb-2" style="height: 44px; width: auto; object-fit: contain;">
                @endforeach
            </div>
            <div class="col-4 d-flex flex-wrap justify-content-end align-items-center">
                @foreach($rightLogos as $logo)
                    <img src="{{ Storage::url($logo['path']) }}" alt="Section logo" class="mr-2 mb-2" style="height: 44px; width: auto; object-fit: contain;">
                @endforeach
            </div>
        </div>
    </div>
@endif
