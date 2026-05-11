<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use App\Services\QuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElshbinyApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_categories_and_products_are_exposed_through_api_v1(): void
    {
        $category = Category::create([
            'name_en' => 'Mannequins',
            'name_ar' => 'مانيكانات',
            'slug' => 'mannequins',
        ]);

        Product::create([
            'category_id' => $category->id,
            'name_en' => 'Female Display Mannequin',
            'name_ar' => 'مانيكان عرض حريمي',
            'slug' => 'female-display-mannequin',
            'sku' => 'ESH-MAN-API-001',
            'base_price' => 1000,
            'technical_specs' => [
                'material' => 'fiberglass',
                'gender' => 'female',
                'pose' => 'standing',
            ],
        ]);

        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'mannequins')
            ->assertJsonPath('data.0.products_count', 1);

        $this->getJson('/api/v1/products?material=fiberglass')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'female-display-mannequin')
            ->assertJsonPath('data.0.base_price', '1000.00')
            ->assertJsonPath('data.0.media.tiny_float_url', null);
    }

    public function test_product_detail_applies_gated_pricing_and_cross_sell(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name_en' => 'Mannequins',
            'name_ar' => 'مانيكانات',
            'slug' => 'mannequins',
        ]);

        $authenticatedProduct = Product::create([
            'category_id' => $category->id,
            'name_en' => 'Profile Price Mannequin',
            'name_ar' => 'مانيكان بسعر للحسابات',
            'slug' => 'profile-price-mannequin',
            'sku' => 'ESH-MAN-API-002',
            'base_price' => 1500,
            'price_visibility' => 'authenticated',
            'technical_specs' => ['material' => 'fiberglass'],
        ]);

        Product::create([
            'category_id' => $category->id,
            'name_en' => 'Cross Sell Mannequin',
            'name_ar' => 'مانيكان مقترح',
            'slug' => 'cross-sell-mannequin',
            'sku' => 'ESH-MAN-API-003',
            'base_price' => 900,
        ]);

        $this->getJson('/api/v1/products/'.$authenticatedProduct->slug)
            ->assertOk()
            ->assertJsonPath('data.base_price', null)
            ->assertJsonCount(1, 'data.cross_sell');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/products/'.$authenticatedProduct->slug)
            ->assertOk()
            ->assertJsonPath('data.base_price', '1500.00');
    }

    public function test_quote_generation_creates_hash_items_and_public_lookup(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name_en' => 'Mannequins',
            'name_ar' => 'مانيكانات',
            'slug' => 'mannequins',
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => 'RFQ Mannequin',
            'name_ar' => 'مانيكان عرض سعر',
            'slug' => 'rfq-mannequin',
            'sku' => 'ESH-MAN-API-004',
            'base_price' => 1250.50,
            'technical_specs' => ['material' => 'fiberglass'],
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/quote/generate', [
            'customer_name' => 'Demo Buyer',
            'customer_phone' => '+201000000000',
            'utm_source' => 'test',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.items.0.product_name_snapshot', 'RFQ Mannequin');

        $hash = $response->json('data.hash_id');

        $this->assertMatchesRegularExpression('/^[123456789ABCDEFGHJKMNPQRSTUVWXYZ]{6}$/', $hash);
        $this->assertDatabaseHas('quotes', [
            'hash_id' => $hash,
            'user_id' => $user->id,
            'utm_source' => 'test',
        ]);
        $this->assertDatabaseHas('quote_items', [
            'quantity' => 2,
            'product_name_snapshot' => 'RFQ Mannequin',
            'sku_snapshot' => 'ESH-MAN-API-004',
        ]);

        $this->getJson('/api/v1/quote/'.$hash)
            ->assertOk()
            ->assertJsonPath('data.hash_id', $hash)
            ->assertJsonPath('data.quote_url', url('/quote/'.$hash));
    }

    public function test_quote_generation_rejects_honeypot_payloads(): void
    {
        $this->postJson('/api/v1/quote/generate', [
            'omega_trap' => 'filled-by-bot',
            'products' => [],
        ])->assertForbidden();
    }

    public function test_quote_service_hash_excludes_ambiguous_characters(): void
    {
        $service = app(QuoteService::class);

        for ($i = 0; $i < 25; $i++) {
            $hash = $service->generateHashId();

            $this->assertSame(6, strlen($hash));
            $this->assertDoesNotMatchRegularExpression('/[0OIL]/', $hash);
        }

        Quote::create(['hash_id' => '123456']);

        $this->assertNotSame('123456', $service->generateHashId());
    }
}
