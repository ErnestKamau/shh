<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProcessResultsRouteTest extends TestCase
{
    public function test_process_results_named_route_accepts_post(): void
    {
        $route = Route::getRoutes()->getByName('process-results');

        $this->assertNotNull($route);
        $this->assertStringContainsString('SampleWorkFlowController', $route->getActionName());
        $this->assertStringContainsString('process_results', $route->getActionName());
        $this->assertContains('POST', $route->methods());
    }

    public function test_legacy_process_raw_results_route_still_exists(): void
    {
        $route = Route::getRoutes()->getByName('process-raw-results');

        $this->assertNotNull($route);
        $this->assertStringContainsString('SampleWorkFlowController', $route->getActionName());
        $this->assertStringContainsString('process_results', $route->getActionName());
    }
}
