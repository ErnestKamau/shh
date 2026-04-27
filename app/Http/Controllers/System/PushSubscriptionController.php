<?php

namespace App\Http\Controllers\System;

use App\PushSubscription;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint'     => 'required|string|max:500',
            'p256dh'       => 'required|string|max:255',
            'auth'         => 'required|string|max:255',
            'browser_name' => 'nullable|string|max:50',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            array_merge($validated, ['user_id' => auth()->id()])
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string|max:500',
        ]);

        PushSubscription::where('endpoint', $request->endpoint)
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json(['success' => true]);
    }
}
