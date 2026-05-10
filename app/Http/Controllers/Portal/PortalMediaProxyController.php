<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PortalMediaProxyController extends Controller
{
    public function show(Request $request): Response
    {
        $path = $this->normalizePath((string) $request->query('path', ''));

        if ($path === '' || str_contains($path, '..')) {
            abort(404);
        }

        if (!str_starts_with($path, 'portal/')) {
            abort(403);
        }

        if (Storage::disk('public')->exists($path)) {
            $fullPath = Storage::disk('public')->path($path);
            return response()->file($fullPath);
        }

        $baseUrl = rtrim((string) config('services.portal_relay.auth_api_base_url'), '/');
        if ($baseUrl === '') {
            abort(404);
        }

        $sharedKey = (string) config('services.portal_relay.shared_key');

        $relayUrl = $baseUrl . '/api/v1/auth/media';

        $response = Http::timeout(30)
            ->withHeaders($sharedKey !== '' ? ['X-Relay-Key' => $sharedKey] : [])
            ->get($relayUrl, ['path' => $path]);

        if ($response->failed()) {
            $legacyStorageUrl = $baseUrl . '/storage/' . $path;

            $legacyWithRelay = Http::timeout(30)
                ->withHeaders($sharedKey !== '' ? ['X-Relay-Key' => $sharedKey] : [])
                ->get($legacyStorageUrl);

            if ($legacyWithRelay->successful()) {
                $response = $legacyWithRelay;
            } else {
                $legacyPublic = Http::timeout(30)->get($legacyStorageUrl);
                if ($legacyPublic->successful()) {
                    $response = $legacyPublic;
                }
            }
        }

        if ($response->failed()) {
            Log::warning('Portal media proxy remote fetch failed', [
                'status' => $response->status(),
                'path' => $path,
                'base_url' => $baseUrl,
            ]);
            abort($response->status() >= 400 ? $response->status() : 404);
        }

        $contentType = (string) ($response->header('Content-Type') ?: 'application/octet-stream');
        $disposition = $response->header('Content-Disposition');

        $headers = [
            'Content-Type' => $contentType,
            'Cache-Control' => 'private, max-age=600',
        ];

        if ($disposition) {
            $headers['Content-Disposition'] = $disposition;
        }

        return response($response->body(), 200, $headers);
    }

    private function normalizePath(string $path): string
    {
        $normalized = ltrim(trim($path), '/');

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        return ltrim($normalized, '/');
    }
}
