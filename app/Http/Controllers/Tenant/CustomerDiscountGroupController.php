<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCustomerDiscountGroupRequest;
use App\Repositories\Interface\CustomerDiscountGroupRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Customer Discount Group management (e.g. VIP, Gold, Regular).
 * Delegates listing shape and persistence to the repository.
 */
final readonly class CustomerDiscountGroupController extends Controller
{
    public function __construct(
        private CustomerDiscountGroupRepositoryInterface $groups,
    ) {}

    public function index(): View
    {
        return view('tenant.customer-discount-groups.index');
    }

    public function listing(Request $request): JsonResponse
    {
        return response()->json($this->groups->datatable($request));
    }

    public function show(int $id): JsonResponse
    {
        $group = $this->groups->find($id);
        abort_if($group === null, 404);

        return response()->json([
            'id' => $group->id,
            'name' => $group->name,
            'discount_percentage' => $group->discount_percentage,
            'description' => $group->description,
            'is_active' => $group->is_active,
        ]);
    }

    public function save(SaveCustomerDiscountGroupRequest $request): JsonResponse
    {
        $id = $request->filled('id') ? (int) $request->input('id') : null;

        $group = $this->groups->save($request->payload(), $id);

        return response()->json([
            'message' => __('customer_discount_groups.alerts.saved'),
            'group' => ['id' => $group->id],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->groups->delete($id);

        return response()->json([
            'message' => __('customer_discount_groups.alerts.deleted'),
        ]);
    }
}
