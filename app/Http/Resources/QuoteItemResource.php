<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'quantity' => $this->quantity,
            'product_name_snapshot' => $this->product_name_snapshot,
            'sku_snapshot' => $this->sku_snapshot,
            'unit_price_snapshot' => $this->unit_price_snapshot,
            'technical_specs_snapshot' => $this->technical_specs_snapshot,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
