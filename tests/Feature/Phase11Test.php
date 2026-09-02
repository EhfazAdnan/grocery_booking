<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase11Test extends TestCase
{
    use RefreshDatabase;

    public function test_customer_products_page_includes_ajax_cart_and_filters(): void
    {
        $response = $this->get('/customer/products');

        $response->assertStatus(200);
        $response->assertSee('showToast');
        $response->assertSee('updateCartCount');
        $response->assertSee('loadProducts');
    }

    public function test_checkout_page_includes_ajax_order_submission(): void
    {
        $customer = \App\Models\User::factory()->customer()->create();
        $this->actingAs($customer);

        $response = $this->get('/customer/checkout');

        $response->assertStatus(200);
        $response->assertSee('handleCheckout');
        $response->assertSee('placeOrder');
        $response->assertSee('showToast');
    }

    public function test_admin_orders_page_includes_ajax_status_updates(): void
    {
        $admin = \App\Models\User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertSee('updateOrderStatus');
        $response->assertSee('showToast');
    }
}
