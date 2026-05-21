<?php

namespace App\Http\Controllers\Registry;

use App\Exports\Registry\RegistryRequestsExport;
use App\Http\Controllers\Controller;
use App\Repositories\Registry\RegistryRequestRepository;
use App\DTOs\Registry\RegistryFilterDTO;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistryReportController extends Controller
{
    public function __construct(
        protected RegistryRequestRepository $repository,
    ) {
        $this->middleware('auth');
    }

    public function export(Request $request, string $format): BinaryFileResponse
    {
        $filter = RegistryFilterDTO::fromArray($request->all());
        $rows = $this->repository->queryFiltered($filter)->get();
        $filename = 'registry-requests-' . now()->format('Y-m-d');

        return match ($format) {
            'csv', 'excel' => Excel::download(new RegistryRequestsExport($rows), $filename . '.xlsx'),
            default => Excel::download(new RegistryRequestsExport($rows), $filename . '.xlsx'),
        };
    }
}
