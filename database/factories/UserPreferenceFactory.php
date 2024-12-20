<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'preferred_sources' => $this->faker->words(3),
            'preferred_categories' => $this->faker->words(3),
            'preferred_authors' => $this->faker->words(3),
            'user_id' => User::factory(),
        ];
    }
}
