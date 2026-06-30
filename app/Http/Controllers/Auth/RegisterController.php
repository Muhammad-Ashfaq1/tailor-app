<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterOrganizationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final readonly class RegisterController extends Controller
{
    public function __construct(
        private RegisterOrganizationAction $register,
    ) {}

    public function show(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = $this->register->handle([
            'organization_name' => $request->string('organization_name')->toString(),
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ]);

        // Log in so they can see the "verify your email" notice + resend.
        Auth::login($user);

        return redirect()->route('verification.notice')
            ->with('status', 'Your organization has been registered and is pending approval. Please verify your email.');
    }
}
