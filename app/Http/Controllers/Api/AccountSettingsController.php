<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Commercial\AccountPaymentTermsService;
use Illuminate\Support\Facades\Cache;

class AccountSettingsController extends Controller
{
    /**
     * Display a listing of account settings.
     */
    public function index()
    {
        $accountSettings = Cache::remember('api_account_settings_list', 1800, function () {
            $account_settings = getConfigTypeByName('Account Settings');
            if (! isset($account_settings->id)) {
                return [];
            }

            $termsService = app(AccountPaymentTermsService::class);

            return collect(getconfigByID($account_settings->id))
                ->filter(fn ($account) => (bool) data_get($account, 'status', true))
                ->map(function ($account) use ($termsService) {
                    $meta = is_array(data_get($account, 'meta')) ? data_get($account, 'meta') : [];

                    return [
                        'id' => (string) data_get($account, 'id'),
                        'key' => (string) data_get($account, 'key', ''),
                        'value' => (string) data_get($account, 'value', ''),
                        'label' => $termsService->displayLabel($account),
                        'meta' => $meta,
                        'status' => (bool) data_get($account, 'status', true),
                    ];
                })
                ->values()
                ->all();
        });

        return response()->json($accountSettings);
    }
}
