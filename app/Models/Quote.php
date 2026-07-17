<?php

namespace App\Models;

use App\Models\Concerns\RecordsCrmActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use HasFactory;
    use RecordsCrmActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_WON = 'won';

    public const STATUS_LOST = 'lost';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'hash_id',
        'user_id',
        'status',
        'customer_name',
        'customer_phone',
        'customer_email',
        'utm_source',
        'utm_medium',
        'utm_campaign',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(QuoteLog::class);
    }
}
