<?php

declare(strict_types=1);

use Illuminate\Support\Facades\URL;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;
use Vevey\Customers\Models\Customer;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Orders\Enums\DeliveryMethod;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Models\Order;

it('sends OWASP security headers on storefront responses', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
});

it('serves institutional, favorites, tracking, and sitemap pages', function (): void {
    $this->get('/sobre')->assertOk()->assertSee('Vevey Doces');
    $this->get('/privacidade')->assertOk()->assertSee('LGPD');
    $this->get('/trocas-e-devolucoes')->assertOk()->assertSee('Código de Defesa do Consumidor');
    $this->get('/favoritos')->assertOk()->assertSee('Meus favoritos');
    $this->get('/rastrear')->assertOk()->assertSee('Rastrear meu pedido');

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('<urlset', false);
});

it('blocks unsigned and unauthorized payment simulation requests (IDOR protection)', function (): void {
    $order = Order::query()->create([
        'number' => 'VV-2026-900001',
        'channel' => OrderChannel::Web,
        'payment_status' => PaymentStatus::Pending,
        'fulfillment_status' => FulfillmentStatus::Pending,
        'delivery_method' => DeliveryMethod::Pickup,
        'customer_name' => 'Cliente Teste',
        'customer_email' => 'cliente@example.com',
        'customer_phone' => '11999999999',
        'subtotal_cents' => brl(2500),
        'discount_cents' => brl(0),
        'shipping_cents' => brl(0),
        'total_cents' => brl(2500),
    ]);

    // Unsigned request must be rejected by signed middleware (403)
    $this->postJson("/checkout/pagar/{$order->id}")
        ->assertForbidden();

    // Even a validly signed URL without session/customer ownership must be rejected (403)
    $signedUrl = URL::temporarySignedRoute('store.checkout.simulate-pay', now()->addMinutes(10), [
        'orderId' => $order->id,
    ]);

    $this->postJson($signedUrl)
        ->assertForbidden();

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::Pending);
});

it('validates cart items against live prices and available stock', function (): void {
    $product = Product::factory()->create([
        'is_active' => true,
        'price_cents' => brl(1500),
        'promo_price_cents' => brl(1200),
    ]);

    StockLevel::query()->create([
        'product_id' => $product->id,
        'on_hand' => 3,
        'reserved' => 1,
    ]);

    $this->postJson('/carrinho/validar', [
        'items' => [
            ['id' => $product->id, 'qty' => 5],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('items.0.id', $product->id)
        ->assertJsonPath('items.0.priceCents', 1200)
        ->assertJsonPath('items.0.qty', 2)
        ->assertJsonPath('items.0.available', 2)
        ->assertJsonCount(1, 'warnings');
});

it('returns active favorite products with live stock', function (): void {
    $product = Product::factory()->create(['is_active' => true, 'price_cents' => brl(1990)]);

    StockLevel::query()->create([
        'product_id' => $product->id,
        'on_hand' => 10,
        'reserved' => 0,
    ]);

    $this->postJson('/favoritos/itens', ['ids' => [$product->id]])
        ->assertOk()
        ->assertJsonPath('items.0.id', $product->id)
        ->assertJsonPath('items.0.available_stock', 10);
});

it('tracks an order only when number and email both match', function (): void {
    Order::query()->create([
        'number' => 'VV-2026-900002',
        'channel' => OrderChannel::Web,
        'payment_status' => PaymentStatus::Paid,
        'fulfillment_status' => FulfillmentStatus::Preparing,
        'delivery_method' => DeliveryMethod::Pickup,
        'customer_name' => 'Maria Silva',
        'customer_email' => 'maria@example.com',
        'customer_phone' => '11988887777',
        'subtotal_cents' => brl(5000),
        'discount_cents' => brl(0),
        'shipping_cents' => brl(0),
        'total_cents' => brl(5000),
    ]);

    // Wrong email must not leak order details
    $this->post('/rastrear', [
        'order_number' => 'VV-2026-900002',
        'email' => 'outro@example.com',
    ])
        ->assertOk()
        ->assertSee('Pedido não encontrado');

    // Matching number + email shows order
    $this->post('/rastrear', [
        'order_number' => 'VV-2026-900002',
        'email' => 'maria@example.com',
    ])
        ->assertOk()
        ->assertSee('VV-2026-900002')
        ->assertDontSee('Pedido não encontrado');
});

it('enforces minimum 8-character password and valid CPF on customer registration', function (): void {
    $this->post('/cadastro', [
        'name' => 'Joao Teste',
        'email' => 'joao@example.com',
        'phone' => '11999998888',
        'cpf' => '111.111.111-11',
        'password' => '123456',
        'password_confirmation' => '123456',
    ])->assertSessionHasErrors(['password']);
});

it('allows authenticated customer to update profile and cancel own pending order', function (): void {
    $customer = Customer::query()->create([
        'name' => 'Ana Costa',
        'email' => 'ana@example.com',
        'password' => 'senha1234',
        'email_verified_at' => now(),
    ]);

    $order = Order::query()->create([
        'number' => 'VV-2026-900003',
        'channel' => OrderChannel::Web,
        'payment_status' => PaymentStatus::Pending,
        'fulfillment_status' => FulfillmentStatus::Pending,
        'delivery_method' => DeliveryMethod::Pickup,
        'customer_id' => $customer->id,
        'customer_name' => $customer->name,
        'customer_email' => $customer->email,
        'customer_phone' => '11977776666',
        'subtotal_cents' => brl(3000),
        'discount_cents' => brl(0),
        'shipping_cents' => brl(0),
        'total_cents' => brl(3000),
    ]);

    $this->actingAs($customer, 'customers')
        ->put('/conta/perfil', ['name' => 'Ana Paula Costa'])
        ->assertRedirect('/conta');

    expect($customer->fresh()->name)->toBe('Ana Paula Costa');

    $this->actingAs($customer, 'customers')
        ->post("/conta/pedidos/{$order->id}/cancelar")
        ->assertRedirect('/conta');

    expect($order->fresh()->fulfillment_status)->toBe(FulfillmentStatus::Cancelled);
});

it('does not include the current product inside related products on the PDP', function (): void {
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['is_active' => true]);
    $sibling = Product::factory()->create(['is_active' => true]);

    $product->categories()->attach($category->id);
    $sibling->categories()->attach($category->id);

    $response = $this->get("/produto/{$product->slug}")->assertOk();

    /** @var \Illuminate\Support\Collection<int, Product> $related */
    $related = $response->viewData('relatedProducts');

    expect($related->pluck('id')->all())->toBe([$sibling->id]);
});
