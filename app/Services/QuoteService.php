<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteLog;
use App\Models\User;
use Illuminate\Support\Arr;

class QuoteService
{
    private const HASH_ALPHABET = '123456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public function createFromPayload(array $payload, ?User $user = null): Quote
    {
        $quote = Quote::create([
            'hash_id' => $this->generateHashId(),
            'user_id' => $user?->id,
            'customer_name' => Arr::get($payload, 'customer_name'),
            'customer_phone' => Arr::get($payload, 'customer_phone'),
            'customer_email' => Arr::get($payload, 'customer_email'),
            'utm_source' => Arr::get($payload, 'utm_source'),
            'utm_medium' => Arr::get($payload, 'utm_medium'),
            'utm_campaign' => Arr::get($payload, 'utm_campaign'),
        ]);

        foreach ($payload['products'] as $item) {
            $product = Product::query()
                ->where('is_active', true)
                ->findOrFail($item['product_id']);

            $quote->items()->create([
                'product_id' => $product->id,
                'quantity' => Arr::get($item, 'quantity', 1),
                'product_name_snapshot' => $product->name_en,
                'sku_snapshot' => $product->sku,
                'unit_price_snapshot' => $product->base_price,
                'technical_specs_snapshot' => $product->technical_specs,
            ]);
        }

        QuoteLog::create([
            'quote_id' => $quote->id,
            'user_id' => $user?->id,
            'actor_type' => $user ? 'buyer' : 'public',
            'field' => 'status',
            'old_value' => null,
            'new_value' => Quote::STATUS_PENDING,
        ]);

        return $quote->load(['items.product.category', 'user']);
    }

    public function generateHashId(): string
    {
        do {
            $hash = $this->randomHash();
        } while (Quote::query()->where('hash_id', $hash)->exists());

        return $hash;
    }

    private function randomHash(): string
    {
        $hash = '';
        $max = strlen(self::HASH_ALPHABET) - 1;

        for ($i = 0; $i < 6; $i++) {
            $hash .= self::HASH_ALPHABET[random_int(0, $max)];
        }

        return $hash;
    }
}
