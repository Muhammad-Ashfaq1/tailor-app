<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CustomerLoginRequest;
use App\Http\Resources\Api\CustomerResource;
use App\Models\Customer;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Org-scoped, stateless Sanctum auth for the /api/v1/* customer surface.
 */
final readonly class AuthController extends Controller
{
    /**
     * Authenticate a customer by org slug + email + password and issue a token.
     */
    public function login(CustomerLoginRequest $request): JsonResponse
    {
        // Resolve the tenant WITHOUT org scope — slug carried in the request body.
        $organization = Organization::query()
            ->where('slug', $request->string('organization'))
            ->first();

        if ($organization === null || ! $organization->isApproved()) {
            $this->failInvalidCredentials();
        }

        // Initialise tenancy so the Customer lookup auto-scopes to this org.
        tenancy()->initialize($organization);

        $customer = Customer::query()
            ->where('email', $request->string('email'))
            ->first();

        if ($customer === null
            || ! $customer->is_active
            || ! Hash::check((string) $request->string('password'), $customer->password)
        ) {
            $this->failInvalidCredentials();
        }

        $token = $customer->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'organization' => [
                'id' => $organization->id,
                'slug' => $organization->slug,
                'name' => $organization->name,
            ],
        ]);
    }

    /**
     * Return the authenticated customer and its organization.
     */
    public function me(Request $request): JsonResponse
    {
        $customer = $request->user();
        $organization = $customer->organization;

        return response()->json([
            'customer' => new CustomerResource($customer),
            'organization' => [
                'id' => $organization->id,
                'slug' => $organization->slug,
                'name' => $organization->name,
            ],
        ]);
    }

    /**
     * Revoke the access token used for the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.'], Response::HTTP_OK);
    }

    /**
     * @return never
     *
     * @throws ValidationException
     */
    private function failInvalidCredentials(): void
    {
        throw ValidationException::withMessages([
            'email' => ['Invalid credentials'],
        ]);
    }
}
