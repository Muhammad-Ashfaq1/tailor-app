<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Actions\Settings\UpdateOrganizationSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveNotificationsRequest;
use App\Http\Requests\SaveSettingsRequest;
use App\Models\Organization;
use App\Support\Settings\SettingsOptions;
use App\Support\Settings\SettingsSchema;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Organization settings. Route-based tabs (the tab list lives in the Blade
 * view); each tab is one form saved over AJAX. Scalar sections are driven by
 * SettingsSchema; Notifications (a matrix) has its own request. Persistence
 * goes through UpdateOrganizationSettings.
 */
final readonly class SettingController extends Controller
{
    public function __construct(
        private UpdateOrganizationSettings $updateSettings,
    ) {}

    public function index(string $page = 'profile'): View
    {
        if (! SettingsSchema::isValidPage($page)) {
            throw new NotFoundHttpException;
        }

        $organization = $this->currentOrganization();

        return view('tenant.settings.index', [
            'page' => $page,
            'organization' => $organization,
            'settings' => $organization->mergedSettings(),
            'options' => [
                'locales' => SettingsOptions::locales(),
                'timezones' => SettingsOptions::timezones(),
                'dateFormats' => SettingsOptions::dateFormats(),
                'timeFormats' => SettingsOptions::timeFormats(),
                'firstDaysOfWeek' => SettingsOptions::firstDaysOfWeek(),
                'currencyPositions' => SettingsOptions::currencyPositions(),
                'measurementUnits' => SettingsOptions::measurementUnits(),
                'deliveryTypes' => SettingsOptions::deliveryTypes(),
            ],
            'notificationEvents' => SettingsSchema::NOTIFICATION_EVENTS,
        ]);
    }

    /** Scalar, schema-driven section save (profile / regional / operations / loyalty / invoice). */
    public function save(SaveSettingsRequest $request, string $section): JsonResponse
    {
        $organization = $this->currentOrganization();
        $validated = $request->validated();

        $dotUpdates = [];
        $name = null;

        foreach (SettingsSchema::section($section) as $field => $def) {
            $value = $this->castValue($validated[$field] ?? null, $def['cast'] ?? null);

            // The special '@name' path updates the organization's name column.
            if ($def['path'] === '@name') {
                $name = (string) $value;

                continue;
            }

            $dotUpdates[$def['path']] = $value;
        }

        $this->updateSettings->handle($organization, $dotUpdates, $name);

        return response()->json(['message' => 'Settings saved.']);
    }

    public function saveNotifications(SaveNotificationsRequest $request): JsonResponse
    {
        $organization = $this->currentOrganization();
        $events = (array) $request->input('events', []);

        $dotUpdates = [];
        foreach (array_keys(SettingsSchema::NOTIFICATION_EVENTS) as $event) {
            $dotUpdates["notifications.events.{$event}.email"] = (bool) ($events[$event]['email'] ?? false);
            $dotUpdates["notifications.events.{$event}.in_app"] = (bool) ($events[$event]['in_app'] ?? false);
        }

        $this->updateSettings->handle($organization, $dotUpdates);

        return response()->json(['message' => 'Notification preferences saved.']);
    }

    private function castValue(mixed $value, ?string $cast): mixed
    {
        return match ($cast) {
            'bool' => (bool) $value,
            'int' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
    }

    private function currentOrganization(): Organization
    {
        $id = OrganizationContext::id();
        abort_if($id === null, 403);

        return Organization::findOrFail($id);
    }
}
