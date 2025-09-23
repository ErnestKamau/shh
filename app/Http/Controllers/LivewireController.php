<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LivewireController extends Controller
{
    public function sampleTypes()
    {
        return view('livewire.sample-type-manager-layout');
    }
}