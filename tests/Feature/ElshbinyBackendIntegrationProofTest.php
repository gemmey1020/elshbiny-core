<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ElshbinyBackendIntegrationProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_backend_end_to_end_catalog_media_quote_and_auth_history_flow(): void
    {
        Storage::fake('public');

        $auth = $this->postJson('/api/v1/auth/register', [
            'name' => 'Integration Buyer',
            'email' => 'integration@example.com',
            'password' => 'secure-password',
        ])->assertCreated();

        $token = $auth->json('data.access_token');

        $category = Category::create([
            'name_en' => 'Mannequins',
            'name_ar' => 'مانيكانات',
            'slug' => 'mannequins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => 'Integration Mannequin',
            'name_ar' => 'مانيكان تكامل',
            'slug' => 'integration-mannequin',
            'sku' => 'ESH-INT-001',
            'base_price' => 3200,
            'price_visibility' => 'public',
            'technical_specs' => [
                'material' => 'fiberglass',
                'gender' => 'female',
                'pose' => 'standing',
                'mount_type' => 'base',
            ],
        ]);

        $imagePath = $this->makeIntegrationImage();

        $media = $product
            ->addMedia($imagePath)
            ->preservingOriginal()
            ->toMediaCollection(Product::MEDIA_COLLECTION);

        $this->assertSame(Product::MEDIA_COLLECTION, $media->collection_name);
        $this->assertTrue($product->fresh()->hasMedia(Product::MEDIA_COLLECTION));

        $this->getJson('/api/v1/products?material=fiberglass')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'integration-mannequin')
            ->assertJsonPath('data.0.media.catalog_url', fn (?string $url): bool => filled($url));

        $this->getJson('/api/v1/products/'.$product->slug)
            ->assertOk()
            ->assertJsonPath('data.slug', 'integration-mannequin')
            ->assertJsonPath('data.technical_specs.material', 'fiberglass')
            ->assertJsonPath('data.media.zoom_url', fn (?string $url): bool => filled($url));

        $quote = $this->withToken($token)->postJson('/api/v1/quote/generate', [
            'customer_name' => 'Integration Buyer',
            'customer_phone' => '+201000000002',
            'utm_source' => 'integration-test',
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ],
            ],
        ]);

        $quote
            ->assertCreated()
            ->assertJsonPath('data.items.0.product_name_snapshot', 'Integration Mannequin')
            ->assertJsonPath('data.items.0.quantity', 3);

        $hash = $quote->json('data.hash_id');

        $this->assertMatchesRegularExpression('/^[123456789ABCDEFGHJKMNPQRSTUVWXYZ]{6}$/', $hash);

        $this->getJson('/api/v1/quote/'.$hash)
            ->assertOk()
            ->assertJsonPath('data.hash_id', $hash)
            ->assertJsonPath('data.utm.source', 'integration-test');

        $this->withToken($token)
            ->getJson('/api/v1/auth/my-quotes')
            ->assertOk()
            ->assertJsonPath('data.0.hash_id', $hash)
            ->assertJsonPath('data.0.items.0.sku_snapshot', 'ESH-INT-001');

        @unlink($imagePath);
    }

    private function makeIntegrationImage(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'elshbiny-integration-').'.jpg';
        $image = imagecreatetruecolor(64, 64);

        imagefill($image, 0, 0, imagecolorallocate($image, 35, 90, 110));
        imagejpeg($image, $path, 90);
        imagedestroy($image);

        return $path;
    }
}
