<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création de l'administrateur
        User::create([
            'id' => Str::uuid(),
            'name' => 'Administrateur',
            'email' => 'admin@gmail.com',
            'phone' => '+2250102030405',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        // Création d'un utilisateur normal
        User::create([
            'id' => Str::uuid(),
            'name' => 'Utilisateur Test',
            'email' => 'user@foca.com',
            'phone' => '+2250506070809',
            'role' => 'user',
            'password' => Hash::make('password123'),
        ]);
        
    }
}