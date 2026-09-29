<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_auth_views_render_successfully(): void
    {
        $product = Product::factory()->create([
            'name' => 'Przykładowy Produkt',
            'price' => 99.99,
            'stock' => 10,
        ]);

        // Katalog produktów
        $response = $this->get(route('products.index'));
        $response->assertOk();
        $response->assertSee('Przykładowy Produkt');

        // Szczegóły produktu
        $response = $this->get(route('products.show', $product));
        $response->assertOk();
        $response->assertSee('Przykładowy Produkt');

        // Koszyk
        $response = $this->get(route('cart.index'));
        $response->assertOk();
        $response->assertSee('Twój koszyk');

        // Logowanie i Rejestracja
        $this->get(route('login'))->assertOk()->assertSee('Zaloguj się');
        $this->get(route('register'))->assertOk()->assertSee('Załóż nowe konto');
    }

    public function test_customer_account_and_checkout_views_render(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        // Koszyk z produktem przed wejściem do kasy
        $this->actingAs($customer);
        app(CartService::class)->add($product, 2);

        // Kasa
        $this->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('Finalizacja zamówienia')
            ->assertSee($customer->name);

        // Zamówienie
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'full_name' => 'Jan Kowalski',
            'total' => 150.00,
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'quantity' => 2,
        ]);

        // Lista zamówień klienta
        $this->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee('Moje zamówienia')
            ->assertSee('#' . $order->id);

        // Szczegóły zamówienia klienta
        $this->get(route('account.orders.show', $order))
            ->assertOk()
            ->assertSee('Zamówienie #' . $order->id)
            ->assertSee('Jan Kowalski');
    }

    public function test_admin_views_render(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        // Utworzenie zamówień dla każdego możliwego statusu OrderStatus
        foreach (\App\Enums\OrderStatus::cases() as $status) {
            Order::factory()->create([
                'user_id' => $admin->id,
                'status' => $status,
            ]);
        }

        $order = Order::query()->first();

        $this->actingAs($admin);

        // Pulpit
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pulpit administratora');

        // Produkty (lista, tworzenie, edycja)
        $this->get(route('admin.products.index'))->assertOk()->assertSee('Zarządzanie produktami');
        $this->get(route('admin.products.create'))->assertOk()->assertSee('Dodaj nowy produkt');
        $this->get(route('admin.products.edit', $product))->assertOk()->assertSee('Edycja produktu');

        // Użytkownicy (lista, tworzenie, edycja, podgląd)
        $this->get(route('admin.users.index'))->assertOk()->assertSee('Użytkownicy');
        $this->get(route('admin.users.create'))->assertOk()->assertSee('Utwórz konto użytkownika');
        $this->get(route('admin.users.show', $admin))->assertOk()->assertSee($admin->name);
        $this->get(route('admin.users.edit', $admin))->assertOk()->assertSee('Edycja konta użytkownika');

        // Zamówienia (lista, podgląd)
        $this->get(route('admin.orders.index'))->assertOk()->assertSee('Zamówienia');
        $this->get(route('admin.orders.show', $order))->assertOk()->assertSee('Zamówienie #' . $order->id);
    }
}
