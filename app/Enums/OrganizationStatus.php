<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Suspended = 'suspended';
    case Rejected = 'rejected';

    public function label(): string
    {
        return __("organizations.status.{$this->value}");
    }

    /** Bootstrap/Vuexy badge colour for the status chip. */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Suspended => 'secondary',
            self::Rejected => 'danger',
        };
    }

    /** Only approved organizations may sign in. */
    public function allowsLogin(): bool
    {
        return $this === self::Approved;
    }

    /** @return array<int, array{value:string,label:string}> */
    public static function options(): array
    {
        return array_map(
            static fn (self $c): array => ['value' => $c->value, 'label' => $c->label()],
            self::cases(),
        );
    }
}
