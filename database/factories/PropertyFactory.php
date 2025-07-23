<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['Apartments', 'Complex', 'Residences', 'Plaza', 'Towers']),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['apartment', 'commercial']),
            'location' => $this->faker->streetName(),
            'country' => $this->faker->country(),
            'state' => $this->faker->state(),
            'city' => $this->faker->city(),
            'zip_code' => $this->faker->postcode(),
            'address' => $this->faker->streetAddress(),
            'parent_id' => 1,
            'is_active' => 1,
        ];
    }

    /**
     * Indicate that the property is an apartment.
     */
    public function apartment()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'apartment',
            ];
        });
    }

    /**
     * Indicate that the property is commercial.
     */
    public function commercial()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'commercial',
            ];
        });
    }

    /**
     * Indicate that the property is in Ethiopia.
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
} 