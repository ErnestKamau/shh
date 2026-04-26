<?php

namespace App\Models\AiRepository;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;

abstract class AiRepositoryModel extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    public function getConnectionName()
    {
        return config('imara_ai.repository_connection', 'pgsql_ai');
    }
}
