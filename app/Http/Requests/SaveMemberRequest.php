<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SaveMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->boolean('_is_update')
            ? (bool) $this->user()?->can('members.update')
            : (bool) $this->user()?->can('members.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['_is_update' => $this->filled('id')]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->filled('id') ? (int) $this->input('id') : null;

        return [
            'id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'role' => ['required', Rule::in(User::TENANT_ROLES)],
            'is_active' => ['boolean'],
            // Password required on create, optional on update.
            'password' => [$id === null ? 'required' : 'nullable', 'confirmed', Password::defaults()],

            'organization_id' => ['prohibited'],
        ];
    }

    /**
     * Localised field names for validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('members.name'),
            'email' => __('members.email'),
            'role' => __('members.role'),
            'is_active' => __('members.status'),
            'password' => __('members.password'),
        ];
    }
}
