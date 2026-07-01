<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CustomerCreditType;
use App\Enums\CustomerType;
use App\Models\Customer;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $creditType = fake()->randomElement(CustomerCreditType::cases());

        return [
            'organization_id' => null,
            'name' => fake()->name(),
            'phone' => fake()->numerify('05########'),
            'address' => fake()->optional()->address(),
            'type' => fake()->randomElement(CustomerType::cases()),
            'credit_type' => $creditType,
            'credit_value' => $creditType === CustomerCreditType::None
                ? 0
                : ($creditType === CustomerCreditType::Percentage ? fake()->numberBetween(2, 10) : fake()->numberBetween(10, 100)),
            'notes' => fake()->optional()->sentence(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }
}
