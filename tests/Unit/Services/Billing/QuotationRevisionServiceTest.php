<?php

namespace Tests\Unit\Services\Billing;

use App\QuotationHeader;
use App\Services\Billing\QuotationRevisionService;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotationRevisionServiceTest extends TestCase
{
    public function test_find_root_walks_revision_chain(): void
    {
        $service = app(QuotationRevisionService::class);

        $root = new QuotationHeader();
        $root->id = (string) Str::uuid();
        $root->revision_number = 1;

        $child = new QuotationHeader();
        $child->id = (string) Str::uuid();
        $child->revision_of_quotation_header_id = $root->id;
        $child->revision_number = 2;

        $this->assertSame($root->id, $service->findRoot($child)->id);
        $this->assertSame($root->id, $service->findRoot($root)->id);
    }
}
