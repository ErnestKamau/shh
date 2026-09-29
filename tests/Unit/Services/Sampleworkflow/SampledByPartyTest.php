<?php

namespace Tests\Unit\Services\Sampleworkflow;

use App\Services\Sampleworkflow\SampledByParty;
use PHPUnit\Framework\TestCase;

class SampledByPartyTest extends TestCase
{
    public function test_normalize_accepts_client_and_company_keys(): void
    {
        $this->assertSame(SampledByParty::CLIENT, SampledByParty::normalize('client'));
        $this->assertSame(SampledByParty::CLIENT, SampledByParty::normalize('Customer'));
        $this->assertSame(SampledByParty::COMPANY, SampledByParty::normalize('company'));
        $this->assertSame(SampledByParty::COMPANY, SampledByParty::normalize('lab'));
        $this->assertNull(SampledByParty::normalize('Thomas Mueller'));
        $this->assertNull(SampledByParty::normalize(null));
    }

    public function test_company_personnel_flag_maps_party_keys(): void
    {
        $this->assertSame(0, SampledByParty::companyPersonnelFlag(SampledByParty::CLIENT));
        $this->assertSame(1, SampledByParty::companyPersonnelFlag(SampledByParty::COMPANY));
        $this->assertNull(SampledByParty::companyPersonnelFlag('Someone Else'));
    }

    public function test_display_label_uses_company_name_when_provided(): void
    {
        $this->assertSame('Client', SampledByParty::displayLabel(SampledByParty::CLIENT));
        $this->assertSame('AMSPEC', SampledByParty::displayLabel(SampledByParty::COMPANY, 'AMSPEC'));
        $this->assertSame('Thomas Mueller', SampledByParty::displayLabel('Thomas Mueller'));
    }

    public function test_select_options_use_stable_values(): void
    {
        $options = SampledByParty::selectOptions('AMSPEC Dubai');

        $this->assertSame([
            ['value' => 'client', 'label' => 'Client'],
            ['value' => 'company', 'label' => 'AMSPEC Dubai'],
        ], $options);
    }
}
