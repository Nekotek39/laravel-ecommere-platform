<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_admin_panel(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_create_product_with_decimal_price_and_images(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Wireless headphones',
                'sku' => 'HP-001',
                'price' => '199.99',
                'compare_at_price' => '249.00',
                'stock' => 7,
                'is_active' => '1',
                // Minimal 1x1 PNG - does not require the GD extension
                'images' => [UploadedFile::fake()->createWithContent('front.png', base64_decode(
                    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
                ))],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('sku', 'HP-001')->firstOrFail();

        $this->assertSame('wireless-headphones', $product->slug);
        $this->assertSame(19999, $product->price);
        $this->assertSame(24900, $product->compare_at_price);
        $this->assertCount(1, $product->images);
        Storage::disk('public')->assertExists($product->images->first()->path);
    }

    public function test_admin_can_change_order_status_and_customer_is_notified(): void
    {
        Notification::fake();

        $order = Order::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.orders.update', $order), [
                'status' => OrderStatus::Processing->value,
                'payment_status' => PaymentStatus::Paid->value,
            ])
            ->assertSessionHasNoErrors();

        $order->refresh();

        $this->assertSame(OrderStatus::Processing, $order->status);
        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->paid_at);
        Notification::assertSentTo($order->user, OrderStatusChangedNotification::class);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $order = Order::factory()->status(OrderStatus::Delivered)->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.orders.update', $order), ['status' => OrderStatus::Pending->value])
            ->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::Delivered, $order->fresh()->status);
    }

    public function test_category_cannot_be_its_own_descendant(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.categories.update', $parent), [
                'name' => $parent->name,
                'parent_id' => $child->id,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_admin_cannot_revoke_own_admin_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $admin), ['role' => 'customer'])
            ->assertSessionHasErrors('role');
    }
}
