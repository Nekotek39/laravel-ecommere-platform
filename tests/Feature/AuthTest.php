<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_password_is_hashed(): void
    {
        $this->post(route('register'), [
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('products.index'));

        $user = User::query()->where('email', 'john@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertNotSame('secret123', $user->password);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_registration_requires_fields(): void
    {
        $this->post(route('register'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_each_role_is_redirected_to_a_different_page_after_login(): void
    {
        $cases = [
            [User::factory()->admin()->create(), route('admin.dashboard')],
            [User::factory()->moderator()->create(), route('admin.products.index')],
            [User::factory()->create(), route('products.index')],
        ];

        foreach ($cases as [$user, $expectedUrl]) {
            $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect($expectedUrl);

            $this->post(route('logout'));
        }
    }
}
