<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePortalAccessRequest;
use App\Models\PortalAccessRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;

class PortalAccessRequestController extends Controller
{
    public function store(StorePortalAccessRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $accessRequest = PortalAccessRequest::query()->create([
            'full_name_or_organisation_enc' => Crypt::encryptString((string) $validated['full_name_or_organisation']),
            'address_enc' => Crypt::encryptString((string) $validated['address']),
            'zone' => (string) $validated['zone'],
            'tin_number_enc' => Crypt::encryptString((string) $validated['tin_number']),
            'email_enc' => Crypt::encryptString((string) $validated['email']),
            'phone_number_enc' => Crypt::encryptString((string) $validated['phone_number']),
            'postal_code' => (string) $validated['postal_code'],
            'request_ip_enc' => $request->ip() ? Crypt::encryptString((string) $request->ip()) : null,
            'user_agent_enc' => $request->userAgent() ? Crypt::encryptString((string) $request->userAgent()) : null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Access request submitted successfully.',
            'data' => [
                'id' => $accessRequest->id,
                'status' => $accessRequest->status,
                'created_at' => $accessRequest->created_at?->toISOString(),
            ],
        ], 201);
    }
}
