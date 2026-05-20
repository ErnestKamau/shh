<?php

namespace App\Events\Registry;

use App\Models\Registry\RegistryRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegistryRequestAssigned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public RegistryRequest $request)
    {
    }
}
