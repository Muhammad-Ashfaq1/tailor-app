<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public capture — anyone may submit. Throttling guards the route.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],

            // Status is server-set ('new'); it may never arrive from the client.
            'status' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('leads.name'),
            'email' => __('leads.email'),
            'company' => __('leads.company'),
            'message' => __('app.notes'),
        ];
    }

    /** The persistable subset; status is forced server-side by the repository. */
    public function payload(): array
    {
        return $this->safe()->only(['name', 'email', 'company', 'message']);
    }
}
