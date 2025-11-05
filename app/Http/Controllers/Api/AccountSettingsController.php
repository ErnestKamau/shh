<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class AccountSettingsController extends Controller
{
    /**
     * Display a listing of account settings.
     */
    public function index()
    {
        $accountSettings = Cache::remember('api_account_settings_list', 1800, function() {
            $account_settings = getConfigTypeByName('Account Settings');
            if (isset($account_settings->id)) {
                return getconfigByID($account_settings->id);
            }
            return [];
        });

        return response()->json($accountSettings);
    }
}

