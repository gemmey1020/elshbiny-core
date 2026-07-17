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
        $quoteUrl = $this->quoteUrl();

        return [
            'hash_id' => $this->hash_id,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'utm' => [
                'source' => $this->utm_source,
                'medium' => $this->utm_medium,
                'campaign' => $this->utm_campaign,
            ],
            'quote_url' => $quoteUrl,
            'whatsapp_payload' => [
                'message' => "Quote request: ".$quoteUrl,
            ],
            'items_count' => $this->whenLoaded('items', fn () => $this->items->sum('quantity')),
            'total_snapshot' => $this->whenLoaded('items', fn () => $this->totalSnapshot()),
            'items' => QuoteItemResource::collection($this->whenLoaded('items')),
        ];
    }

    private function quoteUrl(): string
    {
        $frontendUrl = trim(explode(',', (string) env('FRONTEND_URL', ''))[0] ?? '');
        $baseUrl = $frontendUrl !== '' ? rtrim($frontendUrl, '/') : rtrim(url('/'), '/');

        return $baseUrl.'/quote/'.$this->hash_id;
    }

    private function totalSnapshot(): ?string
    {
        $total = $this->items
            ->filter(fn ($item): bool => $item->unit_price_snapshot !== null)
            ->sum(fn ($item): float => (float) $item->unit_price_snapshot * (int) $item->quantity);

        return $total > 0 ? number_format($total, 2, '.', '') : null;
    }
}
