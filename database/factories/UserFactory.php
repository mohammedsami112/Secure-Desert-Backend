<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => 'USR-'.$this->faker->unique()->randomNumber(4),
            'username' => $this->faker->userName,
            'name' => $this->faker->name,
            'photo' => $this->faker->imageUrl(640, 480, 'people'),
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
            'country_code' => $this->faker->countryCode,
            'phone_number' => $this->faker->phoneNumber,
            'points' => $this->faker->randomNumber(3),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
