<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(50, $request->integer('per_page', 12)));

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($request->filled('category'), function ($query) use ($request): void {
                $query->whereHas('category', fn ($category) => $category->where('slug', $request->string('category')));
            })
            ->when($request->filled('material'), function ($query) use ($request): void {
                $query->where('technical_specs->material', $request->string('material'));
            })
            ->when($request->filled('gender'), function ($query) use ($request): void {
                $query->where('technical_specs->gender', $request->string('gender'));
            })
            ->when($request->filled('pose'), function ($query) use ($request): void {
                $query->where('technical_specs->pose', $request->string('pose'));
            })
            ->orderBy('name_en')
            ->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function sync(Request $request): AnonymousResourceCollection
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn (string $id): int => (int) trim($id))
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->take(50)
            ->values();

        $products = Product::query()
            ->with('category')
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Product $product): int => $ids->search($product->id))
            ->values();

        return ProductResource::collection($products);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $product = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $crossSell = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $data = (new ProductResource($product))->resolve($request);
        $data['cross_sell'] = ProductResource::collection($crossSell)->resolve($request);

        return response()->json(['data' => $data]);
    }
}
