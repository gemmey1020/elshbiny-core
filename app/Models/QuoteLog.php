<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'quote_id',
        'user_id',
        'actor_type',
        'field',
        'old_value',
        'new_value',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
