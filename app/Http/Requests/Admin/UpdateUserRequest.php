<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'role' => [
                'required',
                Rule::enum(UserRole::class),
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($this->route('user')->is($this->user()) && $value !== UserRole::Admin->value) {
                        $fail('You cannot revoke your own administrator role.');
                    }
                },
            ],
        ];
    }
}
