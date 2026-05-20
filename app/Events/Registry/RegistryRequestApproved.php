<?php

namespace App\Events\Registry;

use App\Models\Registry\RegistryRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegistryRequestApproved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public RegistryRequest $request)
    {
    }
}
