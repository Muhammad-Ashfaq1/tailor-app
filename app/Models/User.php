<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Permissions\PermissionTeamScope;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property int|null $organization_id  Null => central / super-admin user.
 * @property string|null $role          Fast-path mirror of the primary spatie role.
 * @property bool $is_active
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasRoles;     // spatie (teams enabled, keyed on organization_id)
    use Notifiable;
    use SoftDeletes;

    /* ----------------------------------------------------------------- */
    /* Roles                                                              */
    /* ----------------------------------------------------------------- */

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_TENANT_ADMIN = 'tenant_admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_MEMBER = 'member';
    public const ROLE_MEMBER_LEAD = 'member_lead';
    public const ROLE_CUSTOMER = 'customer';

    /** Roles that may not be renamed or deleted in the roles UI. */
    public const PROTECTED_ROLES = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_TENANT_ADMIN,
    ];

    /** Roles that grant access to the /member/* operational panel. */
    public const MEMBER_TIER_ROLES = [
        self::ROLE_MEMBER,
        self::ROLE_MEMBER_LEAD,
    ];

    /** Roles assignable inside a tenant (excludes super_admin / customer). */
    public const TENANT_ROLES = [
        self::ROLE_TENANT_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_MEMBER_LEAD,
        self::ROLE_MEMBER,
    ];

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /* ----------------------------------------------------------------- */
    /* Relations                                                          */
    /* ----------------------------------------------------------------- */

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /* ----------------------------------------------------------------- */
    /* Role helpers                                                       */
    /* ----------------------------------------------------------------- */

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN || $this->organization_id === null;
    }

    public function isTenantAdmin(): bool
    {
        return $this->role === self::ROLE_TENANT_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isMemberTier(): bool
    {
        return in_array($this->role, self::MEMBER_TIER_ROLES, true);
    }

    /**
     * Assign the user's PRIMARY role: keep the fast-path users.role string and
     * the spatie pivot in sync, scoped to the user's organization team.
     */
    public function assignPrimaryRole(string $role): void
    {
        $teamId = $this->organization_id ?? 0;

        PermissionTeamScope::for($teamId, function () use ($role): void {
            $this->syncRoles([$role]);
        });

        $this->forceFill(['role' => $role])->save();
    }

    /* ----------------------------------------------------------------- */
    /* Routing                                                            */
    /* ----------------------------------------------------------------- */

    /** Where this user lands after login, by role tier. */
    public function defaultDashboardRouteName(): string
    {
        return match (true) {
            $this->isSuperAdmin() => 'admin.dashboard',
            $this->isMemberTier() => 'member.dashboard',
            $this->organization_id !== null => 'tenant.dashboard',
            default => 'login',
        };
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
