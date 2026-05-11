<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteController extends Controller
{
    public function __construct(
        private readonly QuoteService $quoteService
    ) {
    }

    public function store(Request $request): JsonResource
    {
        $validated = $request->validate([
            'products' => ['required', 'array', 'min:1', 'max:5'],
            'products.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'products.*.quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
        ]);

        $quote = $this->quoteService->createFromPayload($validated, $request->user('sanctum'));

        return (new QuoteResource($quote))->additional([
            'meta' => [
                'message' => 'Quote generated.',
            ],
        ]);
    }

    public function show(string $hash): QuoteResource
    {
        $quote = Quote::query()
            ->with(['items.product.category', 'user'])
            ->where('hash_id', $hash)
            ->firstOrFail();

        return new QuoteResource($quote);
    }
}
