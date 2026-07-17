<?php

namespace Tests\Feature;

use App\Filament\Pages\CatalogImport;
use App\Filament\Pages\AdminGuide;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ConsultationResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\QuoteResource;
use App\Models\Category;
use App\Models\Consultation;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ElshbinyFilamentCrmTest extends TestCase
{
    use RefreshDatabase;

    public function test_filament_crm_resource_routes_load_for_super_admin_only(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $buyer = User::factory()->create(['role' => User::ROLE_BUYER]);

        $this->actingAs($buyer)
            ->get(CategoryResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(CategoryResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(ProductResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(QuoteResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(ConsultationResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(CatalogImport::getUrl())
            ->assertOk();

        $this->actingAs($admin)
            ->get(AdminGuide::getUrl())
            ->assertOk();
    }

    public function test_filament_crm_resources_define_expected_pages(): void
    {
        $this->assertSame(['index', 'create', 'edit'], array_keys(CategoryResource::getPages()));
        $this->assertSame(['index', 'create', 'edit'], array_keys(ProductResource::getPages()));
        $this->assertSame(['index', 'view'], array_keys(QuoteResource::getPages()));
        $this->assertSame(['index', 'view'], array_keys(ConsultationResource::getPages()));
    }

    public function test_omega_quotes_and_consultations_are_read_only(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $quote = Quote::create([
            'hash_id' => 'READ01',
            'status' => Quote::STATUS_PENDING,
            'customer_name' => 'Read Only Buyer',
            'customer_phone' => '+201000000000',
        ]);
        $consultation = Consultation::create([
            'name' => 'Read Only Lead',
            'phone' => '+201000000001',
            'status' => 'new',
        ]);

        $this->assertFalse(QuoteResource::canCreate());
        $this->assertFalse(QuoteResource::canEdit($quote));
        $this->assertFalse(QuoteResource::canDelete($quote));
        $this->assertFalse(QuoteResource::canDeleteAny());

        $this->assertFalse(ConsultationResource::canCreate());
        $this->assertFalse(ConsultationResource::canEdit($consultation));
        $this->assertFalse(ConsultationResource::canDelete($consultation));
        $this->assertFalse(ConsultationResource::canDeleteAny());

        $this->actingAs($admin)
            ->get(QuoteResource::getUrl('view', ['record' => $quote]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(ConsultationResource::getUrl('view', ['record' => $consultation]))
            ->assertOk();
    }

    public function test_catalog_content_deletion_is_disabled_in_omega(): void
    {
        $category = Category::create([
            'name_en' => 'Safe Category',
            'name_ar' => 'تصنيف آمن',
            'slug' => 'safe-category',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => 'Safe Product',
            'name_ar' => 'منتج آمن',
            'slug' => 'safe-product',
            'sku' => 'SAFE-001',
            'base_price' => 2100,
            'price_visibility' => 'authenticated',
        ]);

        $this->assertFalse(CategoryResource::canDelete($category));
        $this->assertFalse(CategoryResource::canDeleteAny());
        $this->assertFalse(ProductResource::canDelete($product));
        $this->assertFalse(ProductResource::canDeleteAny());
    }

    public function test_crm_models_record_activity_for_important_crud_events(): void
    {
        $category = Category::create([
            'name_en' => 'Mannequins',
            'name_ar' => 'مانيكانات',
            'slug' => 'mannequins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => 'CRM Product',
            'name_ar' => 'منتج CRM',
            'slug' => 'crm-product',
            'sku' => 'ESH-CRM-001',
            'base_price' => 2100,
            'price_visibility' => 'authenticated',
            'technical_specs' => ['material' => 'fiberglass'],
        ]);

        $quote = Quote::create([
            'hash_id' => 'CRM123',
            'status' => 'pending',
            'customer_name' => 'CRM Buyer',
        ]);

        $consultation = Consultation::create([
            'name' => 'Consultation Lead',
            'phone' => '+201000000001',
            'status' => 'new',
        ]);

        $product->update(['price_visibility' => 'hidden']);
        $quote->update(['status' => 'contacted']);
        $consultation->update(['status' => 'contacted']);
        $product->delete();

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'elshbiny_crm',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'event' => 'deleted',
        ]);

        foreach ([$category, $product, $quote, $consultation] as $subject) {
            $this->assertGreaterThan(
                0,
                DB::table('activity_log')
                    ->where('log_name', 'elshbiny_crm')
                    ->where('subject_type', $subject::class)
                    ->where('subject_id', $subject->id)
                    ->count()
            );
        }
    }

    public function test_product_resource_exposes_price_visibility_and_media_upload_fields(): void
    {
        $resource = File::get(app_path('Filament/Resources/ProductResource.php'));

        $this->assertStringContainsString("Select::make('price_visibility')", $resource);
        $this->assertStringContainsString("FileUpload::make('media_uploads')", $resource);
        $this->assertStringContainsString('->dehydrated(false)', $resource);
    }
}
