<?php

namespace App\Http\Controllers;

use App\Lab;
use Illuminate\Http\Request;

class InventoryManagementController extends Controller
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
}
