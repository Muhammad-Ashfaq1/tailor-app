<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Permissions\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('roles.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_\- ]+$/i'],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(PermissionCatalog::all())],
        ];
    }
}
