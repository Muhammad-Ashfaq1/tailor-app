<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How a customer earns loyalty credits on each order:
 *  - None       : no credit reward.
 *  - Percentage : credit_value is a % of the order total.
 *  - Fixed      : credit_value is a flat amount per order.
 */
enum CustomerCreditType: string
{
    case None = 'none';
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::None => 'No credit',
            self::Percentage => 'Percentage',
            self::Fixed => 'Fixed amount',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::None => 'secondary',
            self::Percentage => 'info',
            self::Fixed => 'success',
        };
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
