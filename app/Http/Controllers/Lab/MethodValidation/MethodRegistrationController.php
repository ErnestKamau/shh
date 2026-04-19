<?php

namespace App\Http\Controllers\Lab\MethodValidation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MethodRegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('layouts.lab.method-validation.method-registration');
    }

    public function show($id)
    {
        $method = \App\AnalysisMethod::with(['company', 'sampleHeader'])->findOrFail($id);
        
        // Ensure this method is in sent_for_validation status
        if ($method->validation_status !== 'sent_for_validation') {
            return redirect()->route('method-validation.registration')
                ->with('error', 'Method not found or not in validation status.');
        }
        
        return view('layouts.lab.method-validation.method-registration-show', compact('method'));
    }
}

