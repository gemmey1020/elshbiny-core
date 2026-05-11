<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'base_price' => $this->visiblePrice(),
            'price_visibility' => $this->price_visibility,
            'technical_specs' => $this->technical_specs,
            'is_active' => $this->is_active,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'media' => [
                'tiny_float_url' => $this->mediaUrl('tiny-float'),
                'catalog_url' => $this->mediaUrl('catalog'),
                'zoom_url' => $this->mediaUrl('zoom'),
            ],
        ];
    }

    private function visiblePrice(): ?string
    {
        $canSeePrice = match ($this->price_visibility) {
            'public' => true,
            'authenticated' => auth('sanctum')->check(),
            'hidden' => false,
            default => false,
        };

        return $canSeePrice ? $this->base_price : null;
    }

    private function mediaUrl(string $conversion): ?string
    {
        if (! method_exists($this->resource, 'getFirstMediaUrl')) {
            return null;
        }

        $url = $this->resource->getFirstMediaUrl(Product::MEDIA_COLLECTION, $conversion);

        return $url !== '' ? $url : null;
    }
}
