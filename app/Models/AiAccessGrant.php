<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken;

class AiAccessGrant extends Model
{
    protected $fillable = ['personal_access_token_id', 'report_scope', 'datasets', 'fields'];

    protected function casts(): array
    {
        return ['datasets' => 'array', 'fields' => 'array'];
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class, 'personal_access_token_id');
    }
}
