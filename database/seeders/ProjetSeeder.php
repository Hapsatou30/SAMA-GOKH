<?php

namespace Database\Seeders;

use App\Models\Projet;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProjetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Projet::factory(2)->create();
    }
}
