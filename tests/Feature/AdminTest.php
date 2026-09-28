<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
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
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_moderator_can_manage_products_but_not_users(): void
    {
        $moderator = User::factory()->moderator()->create();
        $product = Product::factory()->create();

        $this->actingAs($moderator)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertModelMissing($product);

        $this->actingAs($moderator)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($moderator)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_product_can_be_created_with_image(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->moderator()->create())
            ->post(route('admin.products.store'), [
                'name' => 'Wireless headphones',
                'description' => 'Great sound.',
                'price' => '199.99',
                'stock' => 7,
                // Minimal 1x1 PNG - does not require the GD extension
                'image' => UploadedFile::fake()->createWithContent('photo.png', base64_decode(
                    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
                )),
            ])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHasNoErrors();

        $product = Product::query()->firstOrFail();

        $this->assertSame('199.99', $product->price);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_product_form_is_validated(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.products.store'), ['price' => 'abc'])
            ->assertSessionHasErrors(['name', 'price', 'stock']);
    }

    public function test_admin_can_create_update_and_delete_user(): void
    {
        $admin = User::factory()->admin()->create();

        // Create
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Moderator',
            'email' => 'mod@example.com',
            'role' => 'moderator',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'mod@example.com')->firstOrFail();
        $this->assertSame(UserRole::Moderator, $user->role);
        $this->assertTrue(Hash::check('secret123', $user->password));

        // Update without changing the password
        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'Renamed',
            'email' => 'mod@example.com',
            'role' => 'customer',
        ])->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Renamed', $user->name);
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertTrue(Hash::check('secret123', $user->password));

        // Delete
        $this->actingAs($admin)->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertModelMissing($user);
    }

    public function test_user_form_is_validated(): void
    {
        $existing = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.users.store'), ['email' => $existing->email, 'role' => 'superuser'])
            ->assertSessionHasErrors(['name', 'email', 'role', 'password']);
    }

    public function test_admin_cannot_delete_or_demote_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));
        $this->assertModelExists($admin);

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'customer',
        ])->assertSessionHasErrors('role');
    }

    public function test_admin_can_change_order_status(): void
    {
        $order = Order::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.orders.update', $order), ['status' => 'shipped'])
            ->assertSessionHasNoErrors();

        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
    }
}
