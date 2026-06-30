<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * customer.org.init — initialise tenancy from the Sanctum-authenticated customer
 * BEFORE any org-scoped query runs. The customer analogue of org.init: the token
 * holder carries organization_id, so isolation is identification-only (single DB).
 */
final readonly class InitializeTenancyFromCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = $request->user();

        if ($customer !== null && $customer->organization_id !== null && $customer->organization !== null) {
            tenancy()->initialize($customer->organization);
        }

        return $next($request);
    }
}
