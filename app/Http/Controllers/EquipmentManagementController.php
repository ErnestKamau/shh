<?php

namespace App\Http\Controllers;

use App\Lab;
use Illuminate\Http\Request;

class EquipmentManagementController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
	public function __construct()
  {
    $this->middleware('auth');
	}
	
  public function index()
  {
    //
  }
}
