<?php

namespace App\Models;

use App\Models\Concerns\RecordsCrmActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;
    use RecordsCrmActivity;

    protected $fillable = [
        'parent_id',
        'name_en',
        'name_ar',
        'slug',
        'sort_order',
        'description_en',
        'description_ar',
        'is_active',
    ];

    protected $attributes = [
        'sort_order' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category): void {
            if (blank($category->name_en)) {
                $category->name_en = $category->name_ar;
            }

            if (blank($category->slug)) {
                $slug = Str::slug($category->name_en);
                $category->slug = $slug !== '' ? $slug : 'category-'.substr(md5($category->name_ar), 0, 8);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name_ar');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
