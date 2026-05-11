<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Consultation;
use App\Models\FitOutQuestion;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ElshbinyCoreSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_domain_tables_and_columns_exist(): void
    {
        $this->assertTrue(Schema::hasTable('categories'));
        $this->assertTrue(Schema::hasColumns('categories', [
            'name_en',
            'name_ar',
            'slug',
            'description_en',
            'description_ar',
            'is_active',
        ]));

        $this->assertTrue(Schema::hasTable('products'));
        $this->assertTrue(Schema::hasColumns('products', [
            'category_id',
            'name_en',
            'name_ar',
            'slug',
            'sku',
            'base_price',
            'price_visibility',
            'technical_specs',
            'is_active',
            'deleted_at',
        ]));

        $this->assertTrue(Schema::hasTable('quotes'));
        $this->assertTrue(Schema::hasColumns('quotes', [
            'hash_id',
            'user_id',
            'status',
            'customer_name',
            'customer_phone',
            'customer_email',
            'utm_source',
            'utm_medium',
            'utm_campaign',
        ]));

        $this->assertTrue(Schema::hasTable('quote_items'));
        $this->assertTrue(Schema::hasColumns('quote_items', [
            'quote_id',
            'product_id',
            'quantity',
            'product_name_snapshot',
            'sku_snapshot',
            'unit_price_snapshot',
            'technical_specs_snapshot',
        ]));

        $this->assertTrue(Schema::hasTable('fit_out_questions'));
        $this->assertTrue(Schema::hasTable('consultations'));
    }

    public function test_core_models_casts_relationships_and_soft_deletes_work(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name_en' => 'Mannequins',
            'name_ar' => 'مانيكانات',
            'slug' => 'mannequins',
            'description_en' => 'Retail display mannequins.',
            'description_ar' => 'مانيكانات عرض للمحلات.',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => 'Obsidian Female Mannequin',
            'name_ar' => 'مانيكان حريمي أوبسيديان',
            'slug' => 'obsidian-female-mannequin',
            'sku' => 'ESH-MAN-001',
            'base_price' => 1250.50,
            'price_visibility' => 'authenticated',
            'technical_specs' => [
                'material' => 'fiberglass',
                'gender' => 'female',
                'pose' => 'standing',
                'mount_type' => 'floor',
            ],
        ]);

        $quote = Quote::create([
            'hash_id' => 'A9X2BC',
            'user_id' => $user->id,
            'status' => 'pending',
            'customer_name' => 'Demo Buyer',
            'customer_phone' => '+201000000000',
            'utm_source' => 'local-test',
        ]);

        $quoteItem = QuoteItem::create([
            'quote_id' => $quote->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'product_name_snapshot' => $product->name_en,
            'sku_snapshot' => $product->sku,
            'unit_price_snapshot' => $product->base_price,
            'technical_specs_snapshot' => $product->technical_specs,
        ]);

        $question = FitOutQuestion::create([
            'step' => 1,
            'question_en' => 'What is your retail specialty?',
            'question_ar' => 'ما هو تخصص المتجر؟',
            'options' => [
                ['label' => 'Fashion', 'value' => 'fashion'],
            ],
        ]);

        $consultation = Consultation::create([
            'name' => 'Demo Lead',
            'phone' => '+201111111111',
            'email' => 'lead@example.com',
            'company' => 'Demo Retail',
            'message' => 'Need fit-out support.',
        ]);

        $this->assertTrue($category->products->contains($product));
        $this->assertTrue($product->category->is($category));
        $this->assertTrue($user->quotes->contains($quote));
        $this->assertTrue($quote->items->contains($quoteItem));
        $this->assertTrue($quoteItem->product->is($product));

        $this->assertSame('fiberglass', $product->technical_specs['material']);
        $this->assertSame('fashion', $question->options[0]['value']);
        $this->assertSame('new', $consultation->status);
        $this->assertSame(2, $quoteItem->quantity);

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertTrue(Product::withTrashed()->findOrFail($product->id)->trashed());
    }
}
