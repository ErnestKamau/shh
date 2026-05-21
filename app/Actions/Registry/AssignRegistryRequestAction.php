<?php

namespace App\Actions\Registry;

use App\DTOs\Registry\AssignRegistryRequestDTO;
use App\Models\Registry\RegistryRequest;
use App\Services\Registry\RegistryAssignmentService;

class AssignRegistryRequestAction
{
    public function __construct(
        protected RegistryAssignmentService $assignmentService,
    ) {
    }

    public function execute(AssignRegistryRequestDTO $dto): RegistryRequest
    {
        return $this->assignmentService->assign($dto);
    }
}
