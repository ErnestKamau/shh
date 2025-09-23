<?php

namespace App\Http\Controllers\LivewireControllers;

use App\Http\Controllers\Controller;

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
        return view('livewire.standard-manager');
    }
}
