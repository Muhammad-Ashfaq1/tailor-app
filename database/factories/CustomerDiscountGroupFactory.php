<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CustomerDiscountGroup;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerDiscountGroup>
 */
class CustomerDiscountGroupFactory extends Factory
{
    protected $model = CustomerDiscountGroup::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => null,
            'name' => fake()->unique()->word() . ' Group',
            'discount_percentage' => fake()->randomFloat(2, 5, 50),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }
}
