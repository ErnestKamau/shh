<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\RatingHeader;
use App\Models\RatingDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RatingModuleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_rating_header()
    {
        $ratingHeader = RatingHeader::create([
            'name' => 'Ecoli Rating',
            'description' => 'Rating system for Ecoli contamination levels'
        ]);

        $this->assertDatabaseHas('rating_headers', [
            'name' => 'Ecoli Rating',
            'description' => 'Rating system for Ecoli contamination levels'
        ]);
    }

    /** @test */
    public function it_can_create_rating_details_for_a_header()
    {
        $ratingHeader = RatingHeader::create([
            'name' => 'Ecoli Rating',
            'description' => 'Rating system for Ecoli contamination levels'
        ]);

        $ratingDetail = RatingDetail::create([
            'rating_header_id' => $ratingHeader->id,
            'key' => 'A+',
            'label' => 'Excellent',
            'interpretation' => 'No Ecoli contamination detected. Water is safe for consumption.'
        ]);

        $this->assertDatabaseHas('rating_details', [
            'rating_header_id' => $ratingHeader->id,
            'key' => 'A+',
            'label' => 'Excellent',
            'interpretation' => 'No Ecoli contamination detected. Water is safe for consumption.'
        ]);
    }

    /** @test */
    public function rating_header_has_many_rating_details()
    {
        $ratingHeader = RatingHeader::create([
            'name' => 'Ecoli Rating',
            'description' => 'Rating system for Ecoli contamination levels'
        ]);

        $ratingDetail1 = RatingDetail::create([
            'rating_header_id' => $ratingHeader->id,
            'key' => 'A+',
            'label' => 'Excellent',
            'interpretation' => 'No Ecoli contamination detected.'
        ]);

        $ratingDetail2 = RatingDetail::create([
            'rating_header_id' => $ratingHeader->id,
            'key' => 'B',
            'label' => 'Good',
            'interpretation' => 'Low Ecoli contamination detected.'
        ]);

        $this->assertCount(2, $ratingHeader->ratingDetails);
        $this->assertTrue($ratingHeader->ratingDetails->contains($ratingDetail1));
        $this->assertTrue($ratingHeader->ratingDetails->contains($ratingDetail2));
    }

    /** @test */
    public function rating_detail_belongs_to_rating_header()
    {
        $ratingHeader = RatingHeader::create([
            'name' => 'Ecoli Rating',
            'description' => 'Rating system for Ecoli contamination levels'
        ]);

        $ratingDetail = RatingDetail::create([
            'rating_header_id' => $ratingHeader->id,
            'key' => 'A+',
            'label' => 'Excellent',
            'interpretation' => 'No Ecoli contamination detected.'
        ]);

        $this->assertEquals($ratingHeader->id, $ratingDetail->ratingHeader->id);
        $this->assertEquals($ratingHeader->name, $ratingDetail->ratingHeader->name);
    }

    /** @test */
    public function it_can_delete_rating_header_and_cascade_delete_details()
    {
        $ratingHeader = RatingHeader::create([
            'name' => 'Ecoli Rating',
            'description' => 'Rating system for Ecoli contamination levels'
        ]);

        $ratingDetail = RatingDetail::create([
            'rating_header_id' => $ratingHeader->id,
            'key' => 'A+',
            'label' => 'Excellent',
            'interpretation' => 'No Ecoli contamination detected.'
        ]);

        $ratingHeader->delete();

        $this->assertDatabaseMissing('rating_headers', ['id' => $ratingHeader->id]);
        $this->assertDatabaseMissing('rating_details', ['id' => $ratingDetail->id]);
    }

    /** @test */
    public function it_can_handle_different_rating_systems()
    {
        // Ecoli Rating System
        $ecoliHeader = RatingHeader::create([
            'name' => 'Ecoli Rating',
            'description' => 'Rating system for Ecoli contamination levels'
        ]);

        $ecoliDetail = RatingDetail::create([
            'rating_header_id' => $ecoliHeader->id,
            'key' => 'A+',
            'label' => 'Excellent',
            'interpretation' => 'No Ecoli contamination detected.'
        ]);

        // Salmonella Rating System
        $salmonellaHeader = RatingHeader::create([
            'name' => 'Salmonella Rating',
            'description' => 'Rating system for Salmonella contamination levels'
        ]);

        $salmonellaDetail = RatingDetail::create([
            'rating_header_id' => $salmonellaHeader->id,
            'key' => 'PASS',
            'label' => 'Safe',
            'interpretation' => 'No Salmonella contamination detected.'
        ]);

        // Water Quality Rating System
        $waterHeader = RatingHeader::create([
            'name' => 'Water Quality Rating',
            'description' => 'Rating system for water quality assessment'
        ]);

        $waterDetail = RatingDetail::create([
            'rating_header_id' => $waterHeader->id,
            'key' => '1-5',
            'label' => 'Scale 1-5',
            'interpretation' => 'Water quality rated on a scale of 1-5.'
        ]);

        $this->assertCount(3, RatingHeader::all());
        $this->assertCount(1, $ecoliHeader->ratingDetails);
        $this->assertCount(1, $salmonellaHeader->ratingDetails);
        $this->assertCount(1, $waterHeader->ratingDetails);
    }
}