@php
    $skillcss = '';
    foreach ($proficiencies ?? [] as $s_p) {
        $skillcss .= '.sp' . $s_p->id . '{ background-color : ' . $s_p->color . ' !important; color:#fff; }';
    }
@endphp
@if($skillcss)
<style>{{ $skillcss }}</style>
@endif
