<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Creating and editing users in the admin panel.
 *
 * When editing, the password is optional - leave it empty to keep the current one.
 */
class UserRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user)],
            'role' => [
                'required',
                Rule::enum(UserRole::class),
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    if ($user && $user->is($this->user()) && $value !== UserRole::Admin->value) {
                        $fail('Nie możesz odebrać sobie roli administratora.');
                    }
                },
            ],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * Validated data without an empty password (so it is not overwritten on edit).
     *
     * @return array<string, mixed>
     */
    public function userData(): array
    {
        return array_filter($this->validated(), fn ($value, $key) => $key !== 'password' || filled($value), ARRAY_FILTER_USE_BOTH);
    }
}
