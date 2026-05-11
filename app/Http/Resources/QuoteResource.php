<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'hash_id' => $this->hash_id,
            'status' => $this->status,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'utm' => [
                'source' => $this->utm_source,
                'medium' => $this->utm_medium,
                'campaign' => $this->utm_campaign,
            ],
            'quote_url' => url('/quote/'.$this->hash_id),
            'whatsapp_payload' => [
                'message' => "Quote request: ".url('/quote/'.$this->hash_id),
            ],
            'items' => QuoteItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
