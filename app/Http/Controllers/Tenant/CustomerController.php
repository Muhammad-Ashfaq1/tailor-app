<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Enums\CustomerCreditType;
use App\Enums\CustomerType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCustomerRequest;
use App\Repositories\Interface\CustomerRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shop customer management (walk-in / regular) — the CRUD entry point every
 * measurement, order and wallet record hangs off. Thin: delegates persistence
 * and listing shape to the repository.
 */
final readonly class CustomerController extends Controller
{
    public function __construct(
        private CustomerRepositoryInterface $customers,
    ) {}

    public function index(): View
    {
        return view('tenant.customers.index', [
            'types' => CustomerType::options(),
            'creditTypes' => CustomerCreditType::options(),
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        return response()->json($this->customers->datatable($request));
    }

    public function show(int $id): JsonResponse
    {
        $customer = $this->customers->find($id);
        abort_if($customer === null, 404);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'type' => $customer->type->value,
            'credit_type' => $customer->credit_type->value,
            'credit_value' => $customer->credit_value,
            'notes' => $customer->notes,
            'email' => $customer->email,
            'is_active' => $customer->is_active,
        ]);
    }

    public function save(SaveCustomerRequest $request): JsonResponse
    {
        $id = $request->filled('id') ? (int) $request->input('id') : null;

        $customer = $this->customers->save($request->payload(), $id);

        return response()->json([
            'message' => 'Customer saved.',
            'customer' => ['id' => $customer->id],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        // Route middleware already enforces customers.delete.
        $this->customers->delete($id);

        return response()->json(['message' => 'Customer deleted.']);
    }
}
