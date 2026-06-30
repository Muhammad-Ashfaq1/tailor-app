<?php

declare(strict_types=1);

namespace App\Support\Impersonation;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Shared impersonation engine. Both super-admin->tenant-admin and
 * tenant-admin->member flows go through start(); the single stop() handler
 * restores the original user. The impersonator id is stashed in the session.
 */
final class Impersonator
{
    public const SESSION_KEY = 'impersonator_id';

    public function start(User $target): void
    {
        // Remember who we really are (only the first/original impersonator).
        if (! session()->has(self::SESSION_KEY)) {
            session()->put(self::SESSION_KEY, Auth::id());
        }

        Auth::login($target);
    }

    public function stop(): bool
    {
        $impersonatorId = session()->pull(self::SESSION_KEY);

        if ($impersonatorId === null) {
            return false;
        }

        $original = User::withoutGlobalScopes()->find($impersonatorId);
        if ($original === null) {
            Auth::logout();

            return false;
        }

        Auth::login($original);

        return true;
    }

    public function isImpersonating(): bool
    {
        return session()->has(self::SESSION_KEY);
    }
}
