<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Models\QuoteLog;
use App\Services\QuoteService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

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

    public function cancel(Request $request, string $hash): JsonResource
    {
        $quote = Quote::query()
            ->with(['items.product.category', 'user'])
            ->where('hash_id', $hash)
            ->whereBelongsTo($request->user())
            ->firstOrFail();

        if ($quote->status !== Quote::STATUS_PENDING) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Only pending quotes can be cancelled.');
        }

        $oldStatus = $quote->status;
        $quote->forceFill(['status' => Quote::STATUS_CANCELLED])->save();

        QuoteLog::create([
            'quote_id' => $quote->id,
            'user_id' => $request->user()->id,
            'actor_type' => 'buyer',
            'field' => 'status',
            'old_value' => $oldStatus,
            'new_value' => Quote::STATUS_CANCELLED,
        ]);

        return (new QuoteResource($quote->refresh()->load(['items.product.category', 'user'])))->additional([
            'meta' => [
                'message' => 'Quote cancelled.',
            ],
        ]);
    }
}
