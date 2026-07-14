<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDiscountGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $orgId = auth()->user()->organization_id;
        $id    = $this->route('discountGroup')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('discount_groups', 'name')
                    ->where('organization_id', $orgId)
                    ->ignore($id),
            ],
            'type'      => ['required', Rule::in(['percentage', 'fixed'])],
            'value'     => [
                'required',
                'numeric',
                'min:0',
                $this->input('type') === 'percentage' ? 'max:100' : 'lte:min_limit',
            ],
            'min_limit' => [$this->input('type') === 'fixed' ? 'required' : 'nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a group name.',
            'name.unique'   => 'A discount group with this name already exists.',
            'type.required' => 'Please select a discount type.',
            'type.in'       => 'Discount type must be Percentage or Fixed.',
            'value.required'  => 'Please enter a discount value.',
            'value.numeric'   => 'The discount value must be a number.',
            'value.min'       => 'The discount value must be at least 0.',
            'value.max'       => 'The percentage discount cannot exceed 100%.',
            'value.lte'       => 'The discount amount cannot exceed the minimum purchase limit.',
            'min_limit.required' => 'Please enter the minimum purchase limit.',
            'min_limit.numeric' => 'The minimum purchase limit must be a number.',
            'min_limit.min'     => 'The minimum purchase limit must be at least 0.',
        ];
    }
}
