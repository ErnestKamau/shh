@php
    $conformityOptions = ['YES', 'No', 'As per Contract', 'As per Email'];
    $selected = $signatures['statement_of_conformity'] ?? '';
@endphp

<div class="trf-section-title">Statement of Conformity &amp; Signatures</div>
<table class="trf-table">
    <tr>
        <td colspan="4">
            <span class="trf-field-label">Statement of Conformity Required in Reports:</span><br>
            @foreach($conformityOptions as $option)
                @php $on = strcasecmp($selected, $option) === 0; @endphp
                <span class="{{ $on ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span> {{ $option }} &nbsp;
            @endforeach
        </td>
    </tr>
    <tr>
        <td style="width:25%;"><span class="trf-field-label">Sampled By:</span><br>{{ $signatures['sampled_by'] }}</td>
        <td style="width:25%;"><span class="trf-field-label">Customer Representative Name/Sign.:</span><br>{{ $signatures['customer_rep_name'] }}</td>
        <td style="width:25%;"><span class="trf-field-label">Customer Representative Contact:</span><br>{{ $signatures['customer_rep_contact'] }}</td>
        <td style="width:25%;"><span class="trf-field-label">Remarks:</span><br>{{ $signatures['remarks'] }}</td>
    </tr>
</table>
