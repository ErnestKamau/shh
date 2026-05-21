<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;

class SolutionPreparationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): \Illuminate\View\View
    {
        return view('layouts.lab.buffer.preparation.livewire-index');
    }

    public function create(): \Illuminate\View\View
    {
        return view('layouts.lab.buffer.preparation.livewire-create');
    }

    public function show(string $id): \Illuminate\View\View
    {
        return view('layouts.lab.buffer.preparation.livewire-show', compact('id'));
    }
}
