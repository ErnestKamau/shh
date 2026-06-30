<?php

namespace Tests\Unit\Commercial;

use App\Models\SampleSubmissionRequest;
use App\Services\Commercial\CommercialEnquiryFieldMapper;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommercialEnquiryFieldMapperTest extends TestCase
{
    private CommercialEnquiryFieldMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new CommercialEnquiryFieldMapper;
    }

    #[Test]
    public function indexed_values_empty_strings_do_not_overwrite_uuid_columns(): void
    {
        $existingContact = (string) Str::uuid7();
        $existingZone = (string) Str::uuid7();

        $enquiry = new SampleSubmissionRequest([
            'crm_contact_id' => $existingContact,
            'zone_id' => $existingZone,
            'purpose' => 'Existing purpose',
            'statement_of_conformity' => 'yes',
        ]);

        $this->mapper->applyHeaderFieldsFromIndexedValues($enquiry, [
            'crm_contact_id' => '',
            'zone_id' => '',
            'lab_zone_location' => '',
            'remarks' => '',
            'purpose' => '',
            'statement_of_conformity' => '',
            'submitted_by_full_name' => '',
        ]);

        $this->assertSame($existingContact, $enquiry->crm_contact_id);
        $this->assertSame($existingZone, $enquiry->zone_id);
        $this->assertSame('Existing purpose', $enquiry->purpose);
        $this->assertSame('yes', $enquiry->statement_of_conformity);
    }

    #[Test]
    public function indexed_values_apply_valid_uuid_when_present(): void
    {
        $contactId = (string) Str::uuid7();
        $zoneId = (string) Str::uuid7();

        $enquiry = new SampleSubmissionRequest;

        $this->mapper->applyHeaderFieldsFromIndexedValues($enquiry, [
            'crm_contact_id' => $contactId,
            'zone_id' => $zoneId,
            'remarks' => 'Walk-in note',
        ]);

        $this->assertSame($contactId, $enquiry->crm_contact_id);
        $this->assertSame($zoneId, $enquiry->zone_id);
        $this->assertSame('Walk-in note', $enquiry->purpose);
    }

    #[Test]
    public function indexed_values_reject_invalid_uuid_strings(): void
    {
        $existingContact = (string) Str::uuid7();
        $enquiry = new SampleSubmissionRequest(['crm_contact_id' => $existingContact]);

        $this->mapper->applyHeaderFieldsFromIndexedValues($enquiry, [
            'crm_contact_id' => 'not-a-uuid',
        ]);

        $this->assertSame($existingContact, $enquiry->crm_contact_id);
    }
}
