<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Repositories\Interface\LeadRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Central, NON-org-scoped lead capture. The Request-a-Demo modal posts here
 * via axios (JSON); a plain form fallback gets a redirect with a flash.
 */
final readonly class LeadController extends Controller
{
    public function __construct(
        private LeadRepositoryInterface $leads,
    ) {}

    public function store(StoreLeadRequest $request): JsonResponse|RedirectResponse
    {
        $this->leads->create($request->payload());

        $message = 'Thanks — we will be in touch.';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
