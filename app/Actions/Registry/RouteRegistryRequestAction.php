<?php

namespace App\Actions\Registry;

use App\Models\Registry\RegistryRequest;
use App\Services\Registry\RegistryRoutingService;

class RouteRegistryRequestAction
{
    public function __construct(
        protected RegistryRoutingService $routingService,
    ) {
    }

    public function execute(RegistryRequest $request): array
    {
        return [
            'approvers' => $this->routingService->resolveApproversForRequest($request)->values()->all(),
        ];
    }
}
