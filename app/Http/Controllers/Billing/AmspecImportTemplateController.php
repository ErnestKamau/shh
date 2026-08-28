<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\AmspecImportTemplateService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AmspecImportTemplateController extends Controller
{
    public function excel(Request $request, AmspecImportTemplateService $templateService): BinaryFileResponse
    {
        $context = $request->query('context', 'quotation') === 'pricelist' ? 'pricelist' : 'quotation';

        return $templateService->downloadExcel($context);
    }
}
