<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $password = 'password';
        
        $need_note = (fake()->numberBetween(0, 1) == 1) ? true : false;
        
        return [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => fake()->password(),
            'language' => fake()->languageCode(),
            'birthdate' => fake()->dateTimeBetween('1960-01-01', '2000-12-31'),
            'note' => ( $need_note ) ? json_encode([
                'lat' => fake()->latitude(),
                'long' => fake()->longitude(),
                'CardNumber' => fake()->creditCardNumber,
                'Color name' => fake()->colorName,
            ]) : null,
        ];
    }
}
