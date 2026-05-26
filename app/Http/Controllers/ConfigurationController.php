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

  public function preferences(): View
  {
    return view('layouts.configuration.preferences');
  }

  public function updatePreferences(\Illuminate\Http\Request $request): RedirectResponse
  {
    $request->validate([
      'sys_theme_primary_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
      'sys_theme_secondary_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
      'sys_theme_accent_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
      'sys_sidebar_bg_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
      'sys_sidebar_link_bg' => 'required|string',
    ]);

    $type = \App\Models\System\SystemConfigurationsType::firstOrCreate(
      ['configuration_type' => 'Global System Theme Settings'],
      [
        'description' => 'Manage colors and aesthetics globally across all modules, including primary highlight colors and sidebar styles.',
        'status' => true,
      ]
    );

    $keys = [
      'sys_theme_primary_color',
      'sys_theme_secondary_color',
      'sys_theme_accent_color',
      'sys_sidebar_bg_color',
      'sys_sidebar_link_bg',
    ];

    foreach ($keys as $key) {
      if ($request->has($key)) {
        \App\Models\System\SystemConfiguration::updateOrCreate(
          ['key' => $key],
          [
            'configuration_type_id' => $type->id,
            'value' => $request->input($key),
            'status' => true,
          ]
        );
      }
    }

    \Illuminate\Support\Facades\Cache::forget('global_theme_variables');

    return redirect()
      ->back()
      ->with('success', 'System Preferences & Theming updated successfully!');
  }
}
