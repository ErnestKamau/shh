<?php

namespace App\Http\Controllers\LivewireControllers;

use App\Http\Controllers\Controller;
use App\Standards;

/**
 * StandardsController
 * 
 * Handles the Livewire-based standards management view.
 */
class StandardsController extends Controller
{
    /**
     * Display the standards management page.
     */
    public function index()
    {
        return view('livewire.layout.lab-app', [
            'componentType' => 'standards',
            'pageTitle' => 'Standards Management'
        ]);
    }

    /**
     * Display the standard analytes management page.
     */
    public function standardAnalytes($standardId)
    {
        $standard = Standards::findOrFail($standardId);
        return view('livewire.layout.lab-app', [
            'componentType' => 'standard-analytes',
            'pageTitle' => 'Standard Analytes - ' . $standard->name,
            'standard' => $standard
        ]);
    }
}
