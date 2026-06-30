<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveProjectRequest;
use App\Models\Project;
use App\Repositories\Interface\ProjectRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thin controller: receives a typed request, delegates to the repository,
 * returns Blade / JSON. The org scope + policies handle isolation & authz.
 */
final readonly class ProjectController extends Controller
{
    public function __construct(
        private ProjectRepositoryInterface $projects,
    ) {}

    /** Page chrome — the DataTable hydrates itself from listing(). */
    public function index(): View
    {
        return view('tenant.projects.index', [
            'statuses' => $this->projects->statusOptions(),
        ]);
    }

    /** DataTable JSON (same permission as index). */
    public function listing(Request $request): JsonResponse
    {
        return response()->json($this->projects->datatable($request));
    }

    /** Single record JSON for the edit modal. */
    public function show(Project $project): JsonResponse
    {
        return response()->json([
            'id' => $project->id,
            'name' => $project->name,
            'slug' => $project->slug,
            'status' => $project->status->value,
            'description' => $project->description,
        ]);
    }

    /** ONE save endpoint: create (no id) or update (with id). */
    public function save(SaveProjectRequest $request): JsonResponse
    {
        $project = $this->projects->save(
            $request->payload(),
            $request->filled('id') ? (int) $request->input('id') : null,
        );

        return response()->json([
            'message' => 'Project saved.',
            'project' => ['id' => $project->id, 'name' => $project->name],
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $this->projects->delete($project);

        return response()->json(['message' => 'Project deleted.']);
    }

    /** Dropdown JSON — short-circuits on missing permission. */
    public function statusOptions(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('projects.view'), 403);

        return response()->json($this->projects->statusOptions());
    }
}
