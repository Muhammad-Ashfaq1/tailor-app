<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Tenancy\OrganizationContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCustomerDiscountGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->boolean('_is_update')
            ? (bool) $this->user()?->can('customer_discount_groups.update')
            : (bool) $this->user()?->can('customer_discount_groups.create');
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
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('customer_discount_groups', 'name')
                    ->where('organization_id', $orgId)
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],
            'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],

            // Server-set columns never accepted from the client.
            'organization_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
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
            'name' => __('customer_discount_groups.fields.name'),
            'discount_percentage' => __('customer_discount_groups.fields.discount_percentage'),
            'description' => __('customer_discount_groups.fields.description'),
            'is_active' => __('customer_discount_groups.fields.status'),
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
            'name', 'discount_percentage', 'description', 'is_active',
        ]) + [
            'is_active' => $this->boolean('is_active'),
            'discount_percentage' => (float) ($this->input('discount_percentage') ?? 0),
        ];
    }
}
