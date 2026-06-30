<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->boolean('_is_update')
            ? (bool) $this->user()?->can('tasks.update')
            : (bool) $this->user()?->can('tasks.create');
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
        $orgId = OrganizationContext::id();

        return [
            'id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(TaskStatus::class)],

            // FK validation is org-scoped so you cannot attach to another tenant's rows.
            'project_id' => [
                'required', 'integer',
                Rule::exists('projects', 'id')->where('organization_id', $orgId),
            ],
            'assigned_to' => [
                'nullable', 'integer',
                Rule::exists('users', 'id')->where('organization_id', $orgId),
            ],
            'due_date' => ['nullable', 'date'],

            'organization_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    public function payload(): array
    {
        return $this->safe()->only(['title', 'status', 'project_id', 'assigned_to', 'due_date']);
    }
}
