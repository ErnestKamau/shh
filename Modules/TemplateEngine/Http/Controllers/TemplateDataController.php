<?php

namespace Modules\TemplateEngine\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\TemplateEngine\Services\DatabaseMetadataService;
use Illuminate\Http\Request;

class TemplateDataController extends Controller
{
    protected $metadata;

    public function __construct(DatabaseMetadataService $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getTables()
    {
        return response()->json($this->metadata->getTables());
    }

    public function getColumns($table)
    {
        return response()->json($this->metadata->getColumns($table));
    }
}
