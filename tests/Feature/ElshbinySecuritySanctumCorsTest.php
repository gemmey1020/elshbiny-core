<?php

namespace Tests\Feature;

use App\Http\Middleware\OmegaPassGate;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ElshbinySecuritySanctumCorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sanctum_auth_registers_logs_in_and_returns_owned_quotes(): void
    {
        $register = $this->postJson('/api/v1/auth/register', [
            'name' => 'B2B Buyer',
            'email' => 'buyer@example.com',
            'password' => 'secure-password',
        ]);

        $register
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'buyer@example.com')
            ->assertJsonPath('data.token_type', 'Bearer');

        $this->assertNotEmpty($register->json('data.access_token'));

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'buyer@example.com',
            'password' => 'secure-password',
        ]);

        $login->assertOk();
        $token = $login->json('data.access_token');

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'buyer@example.com');

        $category = Category::create([
            'name_en' => 'Mannequins',
            'name_ar' => 'مانيكانات',
            'slug' => 'mannequins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => 'Secure Quote Mannequin',
            'name_ar' => 'مانيكان عرض سعر آمن',
            'slug' => 'secure-quote-mannequin',
            'sku' => 'ESH-MAN-SEC-001',
            'base_price' => 1800,
        ]);

        $quote = $this->withToken($token)->postJson('/api/v1/quote/generate', [
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $quote->assertCreated();

        $this->withToken($token)
            ->getJson('/api/v1/auth/my-quotes')
            ->assertOk()
            ->assertJsonPath('data.0.hash_id', $quote->json('data.hash_id'));
    }

    public function test_sanctum_logout_revokes_current_bearer_token(): void
    {
        $register = $this->postJson('/api/v1/auth/register', [
            'name' => 'Logout Buyer',
            'email' => 'logout@example.com',
            'password' => 'secure-password',
        ]);

        $token = $register->json('data.access_token');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('meta.message', 'Logged out.');

        $this->assertSame(0, PersonalAccessToken::query()->count());
    }

    public function test_cors_uses_frontend_origin_with_credentials(): void
    {
        config([
            'cors.allowed_origins' => ['https://store.elshbiny.test'],
            'cors.supports_credentials' => true,
        ]);

        $this->withHeaders([
            'Origin' => 'https://store.elshbiny.test',
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/v1/categories')
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'https://store.elshbiny.test')
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function test_bearer_login_accepts_frontend_origin_without_csrf_cookie(): void
    {
        config([
            'cors.allowed_origins' => ['http://127.0.0.1:3017'],
            'cors.supports_credentials' => true,
        ]);

        $user = \App\Models\User::factory()->create([
            'email' => 'origin-buyer@example.com',
            'password' => Hash::make('secure-password'),
        ]);

        $this->withHeader('Origin', 'http://127.0.0.1:3017')
            ->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => 'secure-password',
            ])
            ->assertOk()
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonPath('data.token_type', 'Bearer');
    }

    public function test_omega_pass_gate_requires_matching_secret_when_configured(): void
    {
        config([
            'omega.pass_gate.enabled' => true,
            'omega.pass_gate.secret' => 'silver-door',
        ]);

        $middleware = new OmegaPassGate();

        $this->expectException(NotFoundHttpException::class);

        $middleware->handle(Request::create('/omega', 'GET'), fn (): Response => new Response('ok'));
    }

    public function test_omega_pass_gate_allows_matching_secret(): void
    {
        config([
            'omega.pass_gate.enabled' => true,
            'omega.pass_gate.secret' => 'silver-door',
        ]);

        $request = Request::create('/omega', 'GET', ['omega_pass' => 'silver-door']);
        $response = (new OmegaPassGate())->handle($request, fn (): Response => new Response('ok'));

        $this->assertSame('ok', $response->getContent());
    }

    public function test_admin_path_is_absent_and_panel_config_uses_neutral_color(): void
    {
        $this->get('/admin')->assertNotFound();

        $provider = File::get(app_path('Providers/Filament/OmegaPanelProvider.php'));

        $this->assertStringContainsString('Color::Neutral', $provider);
    }

    public function test_application_config_has_no_warm_panel_color_references(): void
    {
        $matches = [];

        foreach (['app', 'bootstrap', 'config', 'routes'] as $directory) {
            foreach (File::allFiles(base_path($directory)) as $file) {
                $contents = File::get($file->getPathname());

                if (preg_match('/Color::(Amber|Orange)/i', $contents) === 1) {
                    $matches[] = $file->getRelativePathname();
                }
            }
        }

        $this->assertSame([], $matches);
    }
}
