<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\PlayerNote;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = User::all();

        $players = [
            ['name' => 'Carlos Mendez', 'email' => 'carlos.mendez@team.com'],
            ['name' => 'Lucia Fernandez', 'email' => 'lucia.fernandez@team.com'],
            ['name' => 'Andres Rios', 'email' => 'andres.rios@team.com'],
            ['name' => 'Valentina Torres', 'email' => 'valentina.torres@team.com'],
            ['name' => 'Diego Castillo', 'email' => 'diego.castillo@team.com'],
            ['name' => 'Camila Vargas', 'email' => 'camila.vargas@team.com'],
            ['name' => 'Sebastian Morales', 'email' => 'sebastian.morales@team.com'],
            ['name' => 'Isabella Rojas', 'email' => 'isabella.rojas@team.com'],
            ['name' => 'Mateo Herrera', 'email' => 'mateo.herrera@team.com'],
            ['name' => 'Sofia Gutierrez', 'email' => 'sofia.gutierrez@team.com'],
        ];

        foreach ($players as $playerData) {
            $player = Player::create($playerData);

            // Crear entre 1 y 4 notas por jugador
            PlayerNote::factory()
                ->count(fake()->numberBetween(1, 4))
                ->for($player)
                ->sequence(fn () => ['user_id' => $authors->random()->id])
                ->create();
        }
    }
}
