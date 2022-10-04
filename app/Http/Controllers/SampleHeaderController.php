<?php

namespace App\Http\Controllers;

use App\SampleHeader;
use Illuminate\Http\Request;

class SampleHeaderController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
	}
	 /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SampleHeader  $sampleHeader
     * @return \Illuminate\Http\Response
     */
    public function show(SampleHeader $sampleHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SampleHeader  $sampleHeader
     * @return \Illuminate\Http\Response
     */
    public function edit(SampleHeader $sampleHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SampleHeader  $sampleHeader
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SampleHeader $sampleHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SampleHeader  $sampleHeader
     * @return \Illuminate\Http\Response
     */
    public function destroy(SampleHeader $sampleHeader)
    {
        //
    }
}
