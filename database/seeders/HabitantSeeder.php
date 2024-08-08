<?php

namespace Database\Seeders;

use App\Models\Habitant;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HabitantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Habitant::factory(5)->create();
    }
}
