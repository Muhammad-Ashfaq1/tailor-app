<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDiscountGroupRequest;
use App\Models\DiscountGroup;
use App\Repositories\Interface\DiscountGroupRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

readonly class DiscountGroupController extends Controller
{
    public function __construct(
        private readonly DiscountGroupRepositoryInterface $repo,
    ) {}

    public function index(): View
    {
        return view('tenant.discounts.groups');
    }

    public function listing(Request $request): JsonResponse
    {
        $this->authorize('discount-groups.view');

        return response()->json(
            $this->repo->datatable($request)
        );
    }

    public function show(DiscountGroup $discountGroup): JsonResponse
    {
        $this->authorize('discount-groups.view');

        return response()->json([
            'id'        => $discountGroup->id,
            'name'      => $discountGroup->name,
            'type'      => $discountGroup->type,
            'value'     => (string) $discountGroup->value,
            'min_limit' => $discountGroup->min_limit !== null ? (string) $discountGroup->min_limit : '',
            'is_active' => $discountGroup->is_active,
        ]);
    }

    public function save(SaveDiscountGroupRequest $request, ?DiscountGroup $discountGroup = null): JsonResponse
    {
        if ($discountGroup && $discountGroup->exists) {
            $this->authorize('discount-groups.update');
        } else {
            $this->authorize('discount-groups.create');
            $discountGroup = null;
        }

        $group = $this->repo->store($request->validated(), $discountGroup);

        return response()->json([
            'message' => $discountGroup === null
                ? 'Discount group created successfully.'
                : 'Discount group updated successfully.',
            'data' => $group,
        ]);
    }

    public function destroy(DiscountGroup $discountGroup): JsonResponse
    {
        $this->authorize('discount-groups.delete');

        $this->repo->delete($discountGroup);

        return response()->json([
            'message' => 'Discount group deleted successfully.',
        ]);
    }
}
