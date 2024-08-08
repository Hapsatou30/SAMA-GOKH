<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Habitant>
 */
class HabitantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(), // Génère un utilisateur aléatoire
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'telephone' => $this->faker->unique()->phoneNumber,
            'adresse' => $this->faker->address,
            'sexe' => $this->faker->randomElement(['Homme', 'Femme']),
            'date_naiss' => $this->faker->date,
            'photo' => $this->faker->imageUrl(),
            'profession' => $this->faker->jobTitle,
            'numero_identite' => $this->faker->unique()->uuid,
        ];
    }
}
