<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Projet>
 */
class ProjetFactory extends Factory
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
            'nom' => $this->faker->sentence(3), // Titre de 3 mots
            'description' => $this->faker->paragraph,
            'statut' => $this->faker->randomElement(['en-attente', 'en cours', 'terminé']),
            'date_debut' => $this->faker->date,
            'date_fin' => $this->faker->date,
            'budget' => $this->faker->randomFloat(2, 1000, 100000), // Budget entre 1 000 et 100 000
            'etat' => $this->faker->randomElement(['approuvé', 'rejeté']),
        ];
    }
}
