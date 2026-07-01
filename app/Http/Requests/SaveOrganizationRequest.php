<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\OrganizationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // /admin/* is already gated by super_admin middleware + Gate::before.
        return (bool) $this->user()?->isSuperAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(OrganizationStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('organizations.name'),
            'status' => __('organizations.status_label'),
        ];
    }
}
