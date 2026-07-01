<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Settings\SettingsSchema;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SaveSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('settings.manage');
    }

    /**
     * Rules are derived from the section schema (one source of truth).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $section = (string) $this->route('section');

        if (! SettingsSchema::isValidSection($section)) {
            throw new NotFoundHttpException("Unknown settings section [{$section}].");
        }

        $rules = [];
        foreach (SettingsSchema::section($section) as $field => $def) {
            $rules[$field] = $def['rules'];
        }

        return $rules;
    }

    /**
     * Localised field names for the current section's inputs. Keys mirror the
     * schema field names; a matching settings.* line supplies the label.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $section = (string) $this->route('section');

        $attributes = [];
        foreach (array_keys(SettingsSchema::section($section)) as $field) {
            $key = "settings.{$field}";
            $label = __($key);
            // Fall back to the raw field name if no translation line exists.
            $attributes[$field] = $label === $key ? $field : $label;
        }

        return $attributes;
    }
}
