<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PersonsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $password = 'password';
        
        return [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => fake()->password(),
            'lang' => fake()->languageCode(),
            'birthdate' => fake()->dateTimeBetween('1960-01-01', '2000-12-31'),
        ];
    }
}
