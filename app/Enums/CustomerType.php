<?php

declare(strict_types=1);

namespace App\Enums;

enum CustomerType: string
{
    case WalkIn = 'walk_in';
    case Regular = 'regular';

    public function label(): string
    {
        return __("customers.types.{$this->value}");
    }

    /** Bootstrap/Vuexy badge colour for the type chip. */
    public function color(): string
    {
        return match ($this) {
            self::WalkIn => 'secondary',
            self::Regular => 'primary',
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
