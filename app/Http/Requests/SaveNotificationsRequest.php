<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Settings\SettingsSchema;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Notifications save — a per-event channel matrix (email / in-app), so it's an
 * array payload rather than a flat scalar form. Rules are derived from the
 * known event list so adding an event is a one-line change in SettingsSchema.
 */
class SaveNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('settings.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = ['events' => ['array']];

        foreach (array_keys(SettingsSchema::NOTIFICATION_EVENTS) as $event) {
            $rules["events.{$event}.email"] = ['boolean'];
            $rules["events.{$event}.in_app"] = ['boolean'];
        }

        return $rules;
    }
}
