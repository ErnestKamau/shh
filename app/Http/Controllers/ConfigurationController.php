<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ConfigurationController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }
  
  public function index(): View
  {
    return view('layouts.configuration.index');
  }

  public function moduleVisibility(): View|RedirectResponse
  {
    if (!auth()->check() || !auth()->user()->can('system.module-switching.view')) {
      return redirect()
        ->route('system-settings')
        ->with('error', 'You have no permission to perform the designated task!');
    }

    return view('layouts.configuration.module-visibility');
  }

  public function translations(): View
  {
    return view('layouts.configuration.translations');
  }

  public function bulkImport(): View
  {
    return view('layouts.configuration.bulk-import');
  }
}
