@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px; margin: auto;">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="/path/to/tanzania-logo.png" alt="Tanzania Logo" style="height: 60px; margin-right: 20px;">
        <img src="/path/to/gcla-logo.png" alt="GCLA Logo" style="height: 60px;">
        <h5 style="margin-top: 10px;">THE UNITED REPUBLIC OF TANZANIA</h5>
        <h6>GOVERNMENT CHEMIST LABORATORY AUTHORITY</h6>
        <h5 style="margin-top: 20px; font-weight: bold;">SAMPLE RECEIPT NOTIFICATION</h5>
        <div style="text-align: right; font-weight: bold;">GCLA 01</div>
    </div>
    <form method="POST" action="{{ route('sample-receipt.store') }}">
        @csrf
        <ol style="font-size: 1.1em;">
            <li>
                Name of the client or submitting authority
                <input type="text" name="client_name" class="form-control mb-2" required>
            </li>
            <li>
                Description of sample(s)
                <textarea name="sample_description" class="form-control mb-2" rows="2" required></textarea>
            </li>
            <li>
                Name of the person submitting the sample or exhibit
                <input type="text" name="submitter_name" class="form-control mb-2" required>
                Designation
                <input type="text" name="submitter_designation" class="form-control mb-2" required>
                Signature
                <input type="text" name="submitter_signature" class="form-control mb-2" required>
            </li>
            <li>
                Laboratory identification number (Lab. No.)
                <input type="text" name="lab_number" class="form-control mb-2" required>
            </li>
            <li>
                Number of samples
                <input type="number" name="number_of_samples" class="form-control mb-2" required>
            </li>
            <li>
                Name of the receiving person
                <input type="text" name="receiver_name" class="form-control mb-2" required>
                Designation
                <input type="text" name="receiver_designation" class="form-control mb-2" required>
                Signature
                <input type="text" name="receiver_signature" class="form-control mb-2" required>
            </li>
            <li>
                Sample receiving date
                <input type="date" name="receiving_date" class="form-control mb-2" required>
            </li>
        </ol>
        <div style="text-align: center; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Submit Receipt Notification</button>
        </div>
    </form>
</div>
@endsection
