<?php

namespace Tests\Feature;

use App\Models\GroceryItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase10Test extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can view products page.
     */
    public function test_admin_can_view_products_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->get('/admin/products');

        $response->assertStatus(200);
        $response->assertViewIs('admin.products');
    }

    /**
     * Test admin can view orders page.
     */
    public function test_admin_can_view_orders_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders');
    }

    /**
     * Test admin can view analytics page.
     */
    public function test_admin_can_view_analytics_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->get('/admin/analytics');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics');
    }

    /**
     * Test customer can view products page (public).
     */
    public function test_customer_can_view_products_page()
    {
        $response = $this->get('/customer/products');

        $response->assertStatus(200);
        $response->assertViewIs('customer.products');
    }

    /**
     * Test customer can view orders page.
     */
    public function test_customer_can_view_orders_page()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->get('/customer/orders');

        $response->assertStatus(200);
        $response->assertViewIs('customer.orders');
    }

    /**
     * Test customer can view checkout page.
     */
    public function test_customer_can_view_checkout_page()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->get('/customer/checkout');

        $response->assertStatus(200);
        $response->assertViewIs('customer.checkout');
    }

    /**
     * Test customer can view order confirmation page.
     */
    public function test_customer_can_view_order_confirmation_page()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->get('/customer/order-confirmation');

        $response->assertStatus(200);
        $response->assertViewIs('customer.order-confirmation');
    }

    /**
     * Test login page loads.
     */
    public function test_login_page_loads()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test register page loads.
     */
    public function test_register_page_loads()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * Test home redirects to admin products for admin.
     */
    public function test_home_redirects_to_admin_for_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/');

        $response->assertRedirect('/admin/products');
    }

    /**
     * Test home redirects to customer products for customer.
     */
    public function test_home_redirects_to_customer_for_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);

        $response = $this->get('/');

        $response->assertRedirect('/customer/products');
    }
}
