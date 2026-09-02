<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Tymon\JWTAuth\JWTGuard;

class LocalizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    // Task 54: Laravel Localization.
    public function test_default_locale_is_english(): void
    {
        $response = $this->get('/customer/products');

        $response->assertStatus(200);
        $this->assertEquals('en', app()->getLocale());
    }

    public function test_locale_switcher_sets_bangla(): void
    {
        $response = $this->get('/locale/bn');

        $response->assertRedirect();
        $this->assertEquals('bn', session('locale'));
    }

    public function test_locale_switcher_sets_spanish(): void
    {
        $response = $this->get('/locale/es');

        $response->assertRedirect();
        $this->assertEquals('es', session('locale'));
    }

    public function test_locale_switcher_rejects_invalid_locale(): void
    {
        $response = $this->get('/locale/invalid');

        $response->assertRedirect();
        // Locale should not be set for invalid values
        $this->assertNull(session('locale'));
    }

    public function test_translation_files_exist_for_all_locales(): void
    {
        foreach (['en', 'bn', 'es'] as $locale) {
            $path = lang_path("{$locale}.json");
            $this->assertFileExists($path);

            $translations = json_decode(file_get_contents($path), true);
            $this->assertIsArray($translations);
            $this->assertArrayHasKey('Login', $translations);
            $this->assertArrayHasKey('Product Management', $translations);
            $this->assertArrayHasKey('Shopping Cart', $translations);
        }
    }

    public function test_bangla_translations_are_valid(): void
    {
        $translations = json_decode(file_get_contents(lang_path('bn.json')), true);

        $this->assertEquals('লগইন', $translations['Login']);
        $this->assertEquals('ব্যবহারকারী', $translations['User']);
    }

    public function test_layout_includes_language_switcher(): void
    {
        $response = $this->get('/customer/products');

        $response->assertStatus(200);
        $response->assertSee('/locale/');
    }

    /**
     * Task 56: Security Audit.
     */
    public function test_all_admin_routes_require_admin_role(): void
    {
        $customer = User::factory()->customer()->create();
        $token = $this->jwtGuard()->fromUser($customer);

        $adminRoutes = [
            ['GET', '/api/admin/grocery-items'],
            ['POST', '/api/admin/grocery-items'],
            ['GET', '/api/admin/orders'],
            ['GET', '/api/admin/analytics/revenue'],
            ['GET', '/api/admin/analytics/top-products'],
            ['GET', '/api/admin/analytics/order-count'],
        ];

        foreach ($adminRoutes as [$method, $route]) {
            $response = $this->json($method, $route, [], [
                'Authorization' => 'Bearer '.$token,
            ]);

            $this->assertEquals(403, $response->status(), "Route {$route} should deny customer access");
        }
    }

    public function test_passwords_are_not_logged(): void
    {
        Log::shouldReceive('info')
            ->times(3)
            ->andReturnUsing(function ($message, $context = []) {
                $json = json_encode([$message, $context]);
                $this->assertStringNotContainsString('Password123', $json);

                return null;
            });

        // Register a user (should log without password)
        $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        // Login (should log without password)
        $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'Password123',
        ]);

        // Logout
        $user = User::where('email', 'test@example.com')->first();
        $token = $this->jwtGuard()->fromUser($user);
        $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);
    }

    public function test_password_hashing_uses_bcrypt(): void
    {
        $user = User::factory()->create([
            'password' => 'Password123',
        ]);

        // Verify password is hashed (not plain text)
        $this->assertNotEquals('Password123', $user->password);

        // Verify bcrypt format (starts with $2y$)
        $this->assertStringStartsWith('$2y$', $user->password);

        // Verify password can be verified
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Password123', $user->password));
    }

    public function test_mass_assignment_is_protected(): void
    {
        // Attempt to register with role field (should be ignored)
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Malicious User',
            'email' => 'malicious@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'admin', // Attempt to escalate privileges
        ]);

        $response->assertStatus(201);

        // Verify the user was created as customer, not admin
        $this->assertDatabaseHas('users', [
            'email' => 'malicious@example.com',
            'role' => 'customer',
        ]);
    }
}
