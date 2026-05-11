<?php

namespace App\Models;

use App\Models\Concerns\RecordsCrmActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use RecordsCrmActivity;
    use Searchable;
    use SoftDeletes;

    public const MEDIA_COLLECTION = 'product-gallery';

    protected $fillable = [
        'category_id',
        'name_en',
        'name_ar',
        'slug',
        'sku',
        'base_price',
        'price_visibility',
        'technical_specs',
        'is_active',
    ];

    protected $attributes = [
        'price_visibility' => 'public',
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'technical_specs' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION)
            ->useDisk(config('media-library.disk_name', 'public'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('tiny-float')
            ->width(180)
            ->height(180)
            ->keepOriginalImageFormat()
            ->nonQueued();

        $this->addMediaConversion('catalog')
            ->width(720)
            ->height(900)
            ->keepOriginalImageFormat()
            ->nonQueued();

        $this->addMediaConversion('zoom')
            ->width(1600)
            ->height(2000)
            ->keepOriginalImageFormat()
            ->nonQueued();
    }

    public function searchableAs(): string
    {
        return 'elshbiny_products';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $specs = $this->technical_specs ?? [];

        return [
            'id' => (int) $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'category_id' => (int) $this->category_id,
            'category_slug' => $this->category?->slug,
            'category_name_en' => $this->category?->name_en,
            'price_visibility' => $this->price_visibility,
            'base_price' => $this->base_price !== null ? (float) $this->base_price : null,
            'is_active' => (bool) $this->is_active,
            'spec_material' => $specs['material'] ?? null,
            'spec_gender' => $specs['gender'] ?? null,
            'spec_pose' => $specs['pose'] ?? null,
            'spec_mount_type' => $specs['mount_type'] ?? null,
            'spec_head_type' => $specs['head_type'] ?? null,
            'spec_base_type' => $specs['base_type'] ?? null,
            'spec_dimensions' => $specs['dimensions'] ?? null,
            'created_at' => $this->created_at?->timestamp,
            'updated_at' => $this->updated_at?->timestamp,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active;
    }
}
