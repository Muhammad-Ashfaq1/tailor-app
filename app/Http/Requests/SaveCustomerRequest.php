<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CustomerCreditType;
use App\Enums\CustomerType;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SaveCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->boolean('_is_update')
            ? (bool) $this->user()?->can('customers.update')
            : (bool) $this->user()?->can('customers.create');
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
        $orgId = (int) OrganizationContext::id();

        return [
            'id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', Rule::enum(CustomerType::class)],
            'credit_type' => ['required', Rule::enum(CustomerCreditType::class)],
            'credit_value' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
            // Email is optional, but unique per organization when supplied.
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('customers', 'email')
                    ->where('organization_id', $orgId)
                    ->ignore($id),
            ],
            'is_active' => ['boolean'],
            // Password is only needed if the customer will use the app; optional always.
            'password' => ['nullable', 'confirmed', Password::defaults()],

            // Server-set columns never accepted from the client.
            'organization_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    /**
     * The clean attribute set handed to the repository.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->only([
            'name', 'phone', 'address', 'type', 'credit_type',
            'credit_value', 'notes', 'email', 'is_active', 'password',
        ]) + [
            'is_active' => $this->boolean('is_active'),
            'credit_value' => (float) ($this->input('credit_value') ?? 0),
        ];
    }
}
