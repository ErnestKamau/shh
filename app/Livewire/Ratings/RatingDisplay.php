<?php

namespace App\Livewire\Ratings;

use Livewire\Component;
use App\Models\RatingHeader;
use App\Models\RatingDetail;

class RatingDisplay extends Component
{
    public $ratingHeaderId;
    public $selectedKey;
    public $ratingDetails = [];
    public $ratingHeader;

    public function mount($ratingHeaderId)
    {
        $this->ratingHeaderId = $ratingHeaderId;
        $this->ratingHeader = RatingHeader::findOrFail($ratingHeaderId);
        $this->loadRatingDetails();
    }

    public function loadRatingDetails()
    {
        $this->ratingDetails = RatingDetail::where('rating_header_id', $this->ratingHeaderId)
            ->orderBy('key')
            ->get();
    }

    public function getInterpretation($key)
    {
        $detail = $this->ratingDetails->firstWhere('key', $key);
        return $detail ? $detail->interpretation : 'Rating not found';
    }

    public function getLabel($key)
    {
        $detail = $this->ratingDetails->firstWhere('key', $key);
        return $detail ? $detail->label : 'Unknown';
    }

    public function updatedSelectedKey()
    {
        // This will automatically trigger a re-render
        // showing the interpretation for the selected key
    }

    public function render()
    {
        return view('livewire.ratings.rating-display', [
            'ratingDetails' => $this->ratingDetails,
        ]);
    }
}