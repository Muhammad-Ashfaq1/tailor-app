<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Org-scoped project reads for the /api/v1/* customer surface. Tenancy is already
 * initialised by customer.org.init, so every query auto-scopes to the org.
 */
final readonly class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $projects = Project::query()
            ->latest()
            ->paginate(15);

        return ProjectResource::collection($projects);
    }

    public function show(Project $project): ProjectResource
    {
        // Route-model binding is already org-scoped (BelongsToOrganization).
        return new ProjectResource($project);
    }
}
