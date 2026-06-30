<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Actions\Settings\UpdateOrganizationSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSettingsRequest;
use App\Support\Settings\OrganizationSettings;
use App\Support\Settings\SettingsSchema;
use App\Support\Tenancy\OrganizationContext;
use App\Models\Organization;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SettingController extends Controller
{
    public function __construct(
        private UpdateOrganizationSettings $updateSettings,
    ) {}

    public function index(string $section = 'general'): View
    {
        if (! SettingsSchema::isValidSection($section)) {
            throw new NotFoundHttpException;
        }

        $organization = $this->currentOrganization();

        return view('tenant.settings.index', [
            'section' => $section,
            'sections' => SettingsSchema::SECTIONS,
            'organization' => $organization,
            'settings' => $organization->mergedSettings(),
            'defaults' => OrganizationSettings::DEFAULT_SETTINGS,
        ]);
    }

    public function save(SaveSettingsRequest $request, string $section): RedirectResponse
    {
        $organization = $this->currentOrganization();
        $validated = $request->validated();

        $dotUpdates = [];
        $name = null;

        foreach (SettingsSchema::section($section) as $field => $def) {
            $value = $this->castValue($validated[$field] ?? null, $def['cast'] ?? null);

            if ($def['path'] === '@name') {
                $name = (string) $value;

                continue;
            }

            $dotUpdates[$def['path']] = $value;
        }

        $this->updateSettings->handle($organization, $dotUpdates, $name);

        return redirect()
            ->route('tenant.settings.index', $section)
            ->with('status', ucfirst($section).' settings saved.');
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
