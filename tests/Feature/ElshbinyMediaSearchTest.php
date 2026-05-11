<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class ElshbinyMediaSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_implements_media_library_and_registers_conversions(): void
    {
        $product = new Product();

        $this->assertInstanceOf(HasMedia::class, $product);
        $this->assertContains(InteractsWithMedia::class, class_uses_recursive(Product::class));
        $this->assertTrue(Schema::hasTable('media'));
        $this->assertTrue(Schema::hasColumns('media', [
            'model_type',
            'model_id',
            'collection_name',
            'file_name',
            'generated_conversions',
        ]));

        $media = new Media([
            'collection_name' => Product::MEDIA_COLLECTION,
            'name' => 'Fixture',
            'file_name' => 'fixture.jpg',
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => 100,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
        ]);
        $media->model_type = Product::class;
        $media->model_id = 1;
        $media->setRelation('model', $product);

        $conversions = ConversionCollection::createForMedia($media);
        $conversionNames = $conversions->map(fn ($conversion) => $conversion->getName())->all();

        $this->assertSame(['tiny-float', 'catalog', 'zoom'], $conversionNames);
        $this->assertFalse($conversions->getByName('tiny-float')->shouldBeQueued());
        $this->assertFalse($conversions->getByName('catalog')->shouldBeQueued());
        $this->assertFalse($conversions->getByName('zoom')->shouldBeQueued());
    }

    public function test_product_search_payload_flattens_specs_for_meilisearch_filters(): void
    {
        $category = Category::create([
            'name_en' => 'Mannequins',
            'name_ar' => 'مانيكانات',
            'slug' => 'mannequins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => 'Searchable Mannequin',
            'name_ar' => 'مانيكان قابل للبحث',
            'slug' => 'searchable-mannequin',
            'sku' => 'ESH-SEA-001',
            'base_price' => 2400.75,
            'price_visibility' => 'public',
            'technical_specs' => [
                'material' => 'fiberglass',
                'gender' => 'female',
                'pose' => 'standing',
                'mount_type' => 'base',
                'head_type' => 'abstract',
                'base_type' => 'metal',
                'dimensions' => '180cm',
            ],
        ])->load('category');

        $payload = $product->toSearchableArray();

        $this->assertSame('elshbiny_products', $product->searchableAs());
        $this->assertContains(Searchable::class, class_uses_recursive(Product::class));
        $this->assertSame('fiberglass', $payload['spec_material']);
        $this->assertSame('female', $payload['spec_gender']);
        $this->assertSame('standing', $payload['spec_pose']);
        $this->assertSame('base', $payload['spec_mount_type']);
        $this->assertSame('abstract', $payload['spec_head_type']);
        $this->assertSame('metal', $payload['spec_base_type']);
        $this->assertSame('mannequins', $payload['category_slug']);
        $this->assertSame(2400.75, $payload['base_price']);
        $this->assertTrue($payload['is_active']);
    }

    public function test_scout_is_ready_for_meilisearch_product_filters(): void
    {
        $scoutConfig = File::get(config_path('scout.php'));
        $filterable = config('scout.meilisearch.index-settings.'.Product::class.'.filterableAttributes');

        $this->assertStringContainsString("'driver' => env('SCOUT_DRIVER', 'meilisearch')", $scoutConfig);
        $this->assertSame('http://localhost:7700', config('scout.meilisearch.host'));
        $this->assertContains('spec_material', $filterable);
        $this->assertContains('spec_gender', $filterable);
        $this->assertContains('spec_pose', $filterable);
        $this->assertContains('category_slug', $filterable);
        $this->assertContains('price_visibility', $filterable);
    }
}
