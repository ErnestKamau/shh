<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Imports\LabBooking;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\SampleDetailStaging;
use App\SampleHeader;
use App\SampleHeaderStaging;
use App\SampleType;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\HeadingRowImport;

class CRMLabBooking extends Controller
{
    public $customer;
    public $statusArr = [
        "0" => 'Created',
        "1" => 'Received And Processed',
        "2" => 'Submitted Not Processed',
        "3" =>  'Cancelled'
    ];
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function setCustomer(){
        $this->customer = CRMCustomer::find(auth()->user()->client_id);
    }


    public function index(){
        $this->setCustomer();
        $sample_types = SampleType::where('active',1)->where('is_external',1)->get();
        $bookings = SampleHeaderStaging::with(['sample','creator','sampletype'])->whereNull('deleted_at')->latest()->get();
        // return response()->json($bookings[0]->sample->count());
        $customer = $this->customer;
        $bookingstatus = $this->statusArr;
        return view('layouts.crm.dashboard.labbook',compact('sample_types','bookings','customer','bookingstatus'));
    }
    public function show($id){
        $this->setCustomer();
        $booking = SampleHeaderStaging::with(['creator','sample'])->find($id);
        $sample_types = SampleType::where('active',1)->where('is_external',1)->get();
        $customer = $this->customer;
        $status_arr = $this->statusArr;
        // return response()->json($booking->sample);
        return view('layouts.crm.dashboard.labbook_show',compact('sample_types','booking','customer','status_arr'));
    }
    public function createLabBooking(Request $request)
    {
        $this->setCustomer();

        // Debug: Uncomment the line below to debug file upload issues
        // \Log::info('Lab Booking Request Data:', $request->all());

        $file = $request->file('booking_upload');
        $sample_type = $request->input('sample_type_id');
        $book_date = $request->input('book_date');
        $staging_header_id = $request->input('staging_header_id');

        $lab_bookings = [];
        $fname = null;
        $customer_id = auth()->user()->client_id;
        $is_edit = $staging_header_id && $staging_header_id > 0;

        // Only process file if uploaded
        if ($file && $file->isValid()) {
            // Validate required headers
            $headings = (new HeadingRowImport)->toArray($file)[0][0] ?? [];
            $required_headers = [
                'sample_type', 'site_sampled_from', 'sample_description', 'date_sampled',
                'time_sampled', 'test_required', 'comments', 'temperature', 'ph', 'ppm'
            ];
            $diff_headings = array_diff($required_headers, $headings);

            if (!empty($diff_headings)) {
                $fieldsList = implode(', ', array_map(function ($field) {
                    return ucwords(str_replace('_', ' ', $field));
                }, $diff_headings));
                $errorMessage = "The following field(s) were not found in the uploaded Excel file: {$fieldsList}.";
                
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $errorMessage
                    ]);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            }

            // Get bookings from file
            $lab_bookings = (new LabBooking)->toArray($file);
            if (empty($lab_bookings[0])) {
                $errorMessage = 'The uploaded excel has no samples attached.';
                
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $errorMessage
                    ]);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            }

            // Prepare file storage
            $staging_count = SampleHeaderStaging::where('customer_id', $customer_id)->count() + 1;
            $staging_count_str = str_pad($staging_count, 3, '0', STR_PAD_LEFT);
            $customer_name = $this->customer->name ?? '';
            $customer_initials = strtoupper(substr(preg_replace('/\s+/', '', $customer_name), 0, 3));
            $customer_name_url = preg_replace('/[^A-Za-z0-9]/', '', $customer_name);

            $path = $file->path();
            $stored_path = Storage::putFile("LabBookings/{$customer_name_url}", new File($path));
            $fname = '/storage/LabBookings/' . $customer_name_url . '/' . urlencode(basename($stored_path));
        }

        // Create or update SampleHeaderStaging
        if ($is_edit) {
            $staging_header = SampleHeaderStaging::find($staging_header_id);
            if ($staging_header) {
                $updateData = [
                    'sample_type_id' => $sample_type,
                    'book_date' => $book_date,
                    'status' => 0,
                ];
                if ($fname) {
                    $updateData['excel_url'] = (string)$fname;
                }
                $staging_header->update($updateData);
            }
        } else {
            // For new booking, file is required
            if (!$file || !$file->isValid()) {
                $errorMessage = 'Please upload a valid booking file for new lab booking.';
                
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $errorMessage
                    ]);
                }
                
                return redirect()->back()->with('error', $errorMessage);
            }
            
            $staging_header = SampleHeaderStaging::create([
                'sample_type_id' => $sample_type,
                'book_no' => 'Q/LB/' . ($customer_initials ?? 'XXX') . '-' . ($staging_count_str ?? '001'),
                'customer_id' => $customer_id,
                'book_date' => $book_date,
                'status' => 0,
                'created_by' => auth()->user()->id,
                'excel_url' => (string)$fname,
            ]);
        }

        // If file uploaded, update details
        if ($file && $file->isValid() && !empty($lab_bookings[0]) && isset($staging_header)) {
            // If updating, clear old details
            if ($is_edit) {
                SampleDetailStaging::where('sample_header_staging_id', $staging_header_id)->delete();
            }

            $insert_arr = [];
            foreach ($lab_bookings[0] as $booking) {
                if($booking['sample_type'] != '' || $booking['sample_type'] != null ){
                    $date_sampled = $booking['date_sampled'] ?? null;
                    $formatted_date = null;

                    if ($date_sampled) {
                        $formatted_date = \DateTime::createFromFormat('d/m/Y', str_replace(['\/', '.', '-'], ['/', '/', '/'], $date_sampled));
                        $formatted_date = $formatted_date ? $formatted_date->format('Y-m-d') : null;
                    }

                    $insert_arr[] = [
                        "sample_type_name" => $booking['sample_type'] ?? null,
                        "sample_point" => $booking['site_sampled_from'] ?? null,
                        "sample_description" => $booking['sample_description'] ?? null,
                        "sampling_date" => $formatted_date,
                        "sampling_time" => $booking['time_sampled'] ?? null,
                        "test_required" => $booking['test_required'] ?? null,
                        "comments" => $booking['comments'] ?? null,
                        "temperature" => $booking['temperature'] ?? null,
                        "ph" => $booking['ph'] ?? null,
                        "ppm" => $booking['ppm'] ?? null,
                        "sample_header_staging_id" => $staging_header->id,
                    ];
                }
            }

            if (!empty($insert_arr)) {
                SampleDetailStaging::insert($insert_arr);
            }
        }

        // Handle successful operations
        if ($is_edit) {
            $message = $file && $file->isValid() ? 'Lab Booking updated successfully with new file!' : 'Lab Booking updated successfully!';
        } else {
            $message = 'Lab Booking created successfully!';
        }

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
    public function moveToEnroute(Request $request){
        SampleHeaderStaging::find($request->book_id)->update(['status'=>2]);
        $booking = SampleHeaderStaging::with(['customer'])->find($request->book_id);
        $subject = "New Lab Booking Submitted – [{$booking->book_no}]";

        $message = "
        Dear Laboratory Team,<br><br>

        This is to inform you that a new lab booking with reference number <strong>{$booking->book_no}</strong> for client <strong>{$booking->customer->name}</strong> has been successfully created and submitted to the <strong>Enroute</strong> section for further processing.<br><br>

        Please proceed with the necessary reception and scheduling procedures as per protocol.<br><br>

        Thank you,<br>
        Laboratory Booking System
        ";
        // notify_user($message,'lab@qplus.co.ke',$subject,false,['danmuv12@gmail.com'],['danmuv12@gmail.com']);
        notify_user($message,'lab@qplus.co.ke',$subject,false);

        return redirect()->back()->with('success', 'Lab Booking submitted to lab successfully!');

    }
    public function updateSample(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'sample_id' => 'required|integer|exists:sample_detail_staging,id',
            'sample_type_name' => 'required|string|max:255',
            'sample_point' => 'nullable|string|max:255',
            'sample_description' => 'nullable|string|max:255',
            'sampling_date' => 'nullable|date',
            'sampling_time' => 'nullable|string|max:20',
            'test_required' => 'nullable|string|max:255',
            'comments' => 'nullable|string|max:1000',
            'temperature' => 'nullable|numeric',
            'ph' => 'nullable|numeric',
            'ppm' => 'nullable|numeric',
        ]);

        $sample = SampleDetailStaging::findOrFail($validated['sample_id']);

        $sample->sample_type_name = $validated['sample_type_name'];
        $sample->sample_point = $validated['sample_point'] ?? null;
        $sample->sample_description = $validated['sample_description'] ?? null;
        $sample->sampling_date = $validated['sampling_date'] ?? null;
        $sample->sampling_time = $validated['sampling_time'] ?? null;
        $sample->test_required = $validated['test_required'] ?? null;
        $sample->comments = $validated['comments'] ?? null;
        $sample->temperature = $validated['temperature'] ?? null;
        $sample->ph = $validated['ph'] ?? null;
        $sample->ppm = $validated['ppm'] ?? null;

        $sample->save();

        return redirect()->back()->with('success', 'Sample details updated successfully!');
    }
    public function delete(Request $request){
        // return response()->json($request->all());
        SampleDetailStaging::where('sample_header_staging_id', $request->staging_header_id)->delete();
        SampleHeaderStaging::find($request->staging_header_id)->delete();
        return redirect()->back()->with('success', 'Lab Booking deleted successfully!');

    }

    public function returnToPortal(Request $request){
        SampleHeaderStaging::find($request->book_id)->update(['status'=>0]);
        return redirect()->back()->with('success', 'Lab Booking moved successfully!');
    }

    public function reports(Request $request){
        $batches = [];
        $sample_types = SampleType::where('active',1)->where('is_external',1)->get();
        $units = CRMCompanyUnit::where('crm_customer_id',auth()->user()->client_id)->get();
        $customer = CRMCustomer::find(auth()->user()->client_id);

        if($request->has('filter')){
            $batches = SampleHeader::with(['customer','sample_type','crmunit','samples'])->where('status','Completed')->where('isactive',1);
            if(isset($request->start_date) && $request->start_date != ''){
                $batches->where('receipt_date','>=',$request->start_date);
            }
            if(isset($request->end_date) && $request->end_date != ''){
                $batches->where('receipt_date','<=',$request->end_date);
            }
            if(isset($request->sample_type) && $request->sample_type != ''){
                $batches->where('sample_type_id',$request->sample_type);
            }
            if(isset($request->customer) && $request->customer != ''){
                $batches->where('crm_customer_id',$request->customer);
            }
            if(isset($request->crm_unit) && $request->crm_unit != ''){
                $batches->where('crm_unit_id',$request->crm_unit);
            }
            $batches = $batches->orderBy('receipt_date','desc')->get();
        }
        return view('layouts.crm.dashboard.reports',compact('batches','sample_types','units','customer'));
    }
    public function validateBookingFile(Request $request){
        $file = $request->file('booking_upload');
        $validation_steps = [];
        
        // Step 1: Check file upload
        $validation_steps[] = [
            'step' => 'File Upload Check',
            'description' => 'Verifying file was uploaded successfully',
            'status' => $file && $file->isValid() ? 'passed' : 'failed',
            'message' => $file && $file->isValid() ? 'File uploaded successfully' : 'File upload failed or invalid file'
        ];
        
        if (!$file || !$file->isValid()) {
            return response()->json([
                'status' => 'error',
                'message' => 'File upload failed or invalid file',
                'validation_steps' => $validation_steps
            ]);
        }
        
        // Step 2: Check file headers
        $headings = (new HeadingRowImport)->toArray($file)[0][0] ?? [];
        $required_headers = [
            'sample_type', 'site_sampled_from', 'sample_description', 'date_sampled',
            'time_sampled', 'test_required', 'comments', 'temperature', 'ph', 'ppm'
        ];
        $diff_headings = array_diff($required_headers, $headings);
        
        $validation_steps[] = [
            'step' => 'Header Validation',
            'description' => 'Checking required columns exist in Excel file',
            'status' => empty($diff_headings) ? 'passed' : 'failed',
            'message' => empty($diff_headings) ? 'All required headers found' : 'Missing headers: ' . implode(', ', $diff_headings)
        ];
        
        if (!empty($diff_headings)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The following field(s) were not found in the uploaded Excel file: ' . implode(', ', $diff_headings),
                'validation_steps' => $validation_steps
            ]);
        }
        
        // Step 3: Check for sample data
        $lab_bookings = (new LabBooking)->toArray($file);
        $has_samples = !empty($lab_bookings[0]);
        
        $validation_steps[] = [
            'step' => 'Sample Data Check',
            'description' => 'Verifying Excel file contains sample data',
            'status' => $has_samples ? 'passed' : 'failed',
            'message' => $has_samples ? count($lab_bookings[0]) . ' samples found' : 'No samples found in Excel file'
        ];
        
        if (!$has_samples) {
            return response()->json([
                'status' => 'error',
                'message' => 'The uploaded excel has no samples attached.',
                'validation_steps' => $validation_steps
            ]);
        }
        
        // Step 4: Validate date and time formats
        $date_time_errors = [];
        foreach($lab_bookings[0] as $index => $booking){
            $date_sampled = $booking['date_sampled'] ?? '';
            $time_sampled = $booking['time_sampled'] ?? '';
            
            // Check date format (d/m/y) - allowing /, -, or . as separators
            if(!empty($date_sampled)){
                $date_parts = preg_split('/[\/\-\.]/', $date_sampled);
                if(count($date_parts) !== 3){
                    $date_time_errors[] = 'Row '.($index + 2).': Date sampled must be in d/m/y format using /, -, or . as separator (e.g., 15/12/2023, 15-12-2023, 15.12.2023)';
                    break;
                }
                
                $day = $date_parts[0];
                $month = $date_parts[1];
                $year = $date_parts[2];
                
                if(!is_numeric($day) || !is_numeric($month) || !is_numeric($year)){
                    $date_time_errors[] = 'Row '.($index + 2).': Date sampled must contain only numbers in d/m/y format';
                    break;
                }
                
                if($day < 1 || $day > 31 || $month < 1 || $month > 12){
                    $date_time_errors[] = 'Row '.($index + 2).': Invalid date values in date sampled field';
                    break;
                }
            }
            
            // Check time format (24hrs system)
            if(!empty($time_sampled)){
                if(!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](\s*hrs)?$/', $time_sampled)){
                    $date_time_errors[] = 'Row '.($index + 2).': Time sampled must be in 24-hour format (e.g., 14:30, 09:15)';
                    break;
                }
            }
        }
        
        $validation_steps[] = [
            'step' => 'Date & Time Format Validation',
            'description' => 'Checking date and time formats in all sample rows',
            'status' => empty($date_time_errors) ? 'passed' : 'failed',
            'message' => empty($date_time_errors) ? 'All dates and times are properly formatted' : $date_time_errors[0]
        ];
        
        if (!empty($date_time_errors)) {
            return response()->json([
                'status' => 'error',
                'message' => $date_time_errors[0],
                'validation_steps' => $validation_steps
            ]);
        }
        
        // Step 5: Final validation success
        $validation_steps[] = [
            'step' => 'Validation Complete',
            'description' => 'All validation checks passed successfully',
            'status' => 'passed',
            'message' => 'File is ready for processing'
        ];
        
        return response()->json([
            'status' => 'success',
            'message' => 'File validation passed',
            'validation_steps' => $validation_steps
        ]);
    }
}
