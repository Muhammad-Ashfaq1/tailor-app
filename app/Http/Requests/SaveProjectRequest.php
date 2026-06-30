<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permission-name check (works without loading the model; super admins
        // pass via Gate::before). The route middleware also enforces these.
        return $this->boolean('_is_update')
            ? (bool) $this->user()?->can('projects.update')
            : (bool) $this->user()?->can('projects.create');
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
        return [
            'id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'description' => ['nullable', 'string', 'max:5000'],

            // Server-set columns may never arrive from the client.
            'organization_id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'updated_by' => ['prohibited'],
        ];
    }

    /** The persistable subset (id is handled separately by the controller). */
    public function payload(): array
    {
        return $this->safe()->only(['name', 'status', 'description']);
    }
}
