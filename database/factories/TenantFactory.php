<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'family_member' => $this->faker->numberBetween(1, 6),
            'location' => $this->faker->streetName(),
            'description' => $this->faker->sentence(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'state' => $this->faker->state(),
            'zip_code' => $this->faker->postcode(),
            'address' => $this->faker->streetAddress(),
            'property' => 1,
            'unit' => 1,
            'lease_start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'lease_end_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'parent_id' => 1,
            'is_active' => 1,
        ];
    }

    /**
     * Indicate that the tenant is in Ethiopia.
     */
    public function ethiopia()
    {
        return $this->state(function (array $attributes) {
            return [
                'country' => 'Ethiopia',
                'state' => $this->faker->randomElement(['Addis Ababa', 'Oromia', 'Amhara', 'Tigray', 'SNNPR']),
                'city' => $this->faker->randomElement(['Addis Ababa', 'Adama', 'Bahir Dar', 'Mekelle', 'Hawassa']),
                'zip_code' => $this->faker->numberBetween(1000, 9999),
            ];
        });
    }

    /**
     * Indicate that the tenant has a short-term lease.
     */
    public function shortTerm()
    {
        return $this->state(function (array $attributes) {
            return [
                'lease_start_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
                'lease_end_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            ];
        });
    }

    /**
     * Indicate that the tenant has a long-term lease.
     */
    public function longTerm()
    {
        return $this->state(function (array $attributes) {
            return [
                'lease_start_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
                'lease_end_date' => $this->faker->dateTimeBetween('now', '+5 years'),
            ];
        });
    }
} 